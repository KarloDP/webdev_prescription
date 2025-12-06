<?php
/**
 * HANDLER: Add Medication Stock
 * Used by: frontend/pharmacist/stock/add_stock.php
 */

if (!defined('DB_HANDLER_INCLUDED')) {
    define('DB_HANDLER_INCLUDED', true);
    include_once(__DIR__ . '/../../includes/db_connect.php');
}

$response = [
    'success' => false,
    'message' => ''
];

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Invalid request.';
    return $response;
}

$genericName  = trim($_POST['genericName'] ?? '');
$brandName    = trim($_POST['brandName'] ?? '');
$form         = trim($_POST['form'] ?? '');
$strength     = trim($_POST['strength'] ?? '');
$manufacturer = trim($_POST['manufacturer'] ?? '');
$addAmount    = intval($_POST['stock'] ?? 0);

if ($genericName === '' || $addAmount <= 0) {
    $response['message'] = 'Required fields missing.';
    return $response;
}

// Check if medication already exists
$check = $conn->prepare("
    SELECT medicationID, stock 
    FROM medication 
    WHERE genericName = ? AND brandName = ?
");
$check->bind_param("ss", $genericName, $brandName);
$check->execute();
$existing = $check->get_result()->fetch_assoc();

if ($existing) {
    // Update stock
    $newStock = $existing['stock'] + $addAmount;

    $update = $conn->prepare("UPDATE medication SET stock = ? WHERE medicationID = ?");
    $update->bind_param("ii", $newStock, $existing['medicationID']);
    $update->execute();

    $response['success'] = true;
    $response['message'] = 'Stock updated successfully.';
    return $response;
}

// Create new medication entry
$insert = $conn->prepare("
    INSERT INTO medication (genericName, brandName, form, strength, manufacturer, stock)
    VALUES (?, ?, ?, ?, ?, ?)
");
$insert->bind_param("sssssi", $genericName, $brandName, $form, $strength, $manufacturer, $addAmount);

if ($insert->execute()) {
    $response['success'] = true;
    $response['message'] = 'New medication added.';
} else {
    $response['message'] = 'Failed to add medication.';
}

return $response;
