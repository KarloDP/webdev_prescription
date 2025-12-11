<?php
/**
 * HANDLER: Update Medication Stock
 * Used by: frontend/pharmacist/stock/edit_stock.php
 */

if (!defined('DB_HANDLER_INCLUDED')) {
    define('DB_HANDLER_INCLUDED', true);
    include_once(__DIR__ . '/../../includes/db_connect.php');
}

$response = ['success' => false, 'message' => ''];

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Invalid request.';
    return $response;
}

$id           = intval($_POST['medicationID'] ?? 0);
$genericName  = trim($_POST['genericName'] ?? '');
$brandName    = trim($_POST['brandName'] ?? '');
$form         = trim($_POST['form'] ?? '');
$strength     = trim($_POST['strength'] ?? '');
$manufacturer = trim($_POST['manufacturer'] ?? '');
$stock        = intval($_POST['stock'] ?? 0);

if ($id <= 0 || $genericName === '' || $stock < 0) {
    $response['message'] = 'Missing required fields.';
    return $response;
}

$update = $conn->prepare("
    UPDATE medication
    SET genericName = ?, brandName = ?, form = ?, strength = ?, manufacturer = ?, stock = ?
    WHERE medicationID = ?
");

$update->bind_param("sssssii",
    $genericName,
    $brandName,
    $form,
    $strength,
    $manufacturer,
    $stock,
    $id
);

if ($update->execute()) {
    $response['success'] = true;
    $response['message'] = 'Medication updated successfully.';
} else {
    $response['message'] = 'Error updating medication.';
}

return $response;
