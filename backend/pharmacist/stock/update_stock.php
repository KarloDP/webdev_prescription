<?php
/**
 * HANDLER: Update Medication Stock
 * Used by: frontend/pharmacist/stock/edit_stock.php
 */

if (!defined('DB_HANDLER_INCLUDED')) {
    define('DB_HANDLER_INCLUDED', true);
    include_once(__DIR__ . '/../../includes/db_connect.php');
    include_once(__DIR__ . '/../../includes/auth.php');
}

// Authentication check
if (!is_logged_in()) {
    return ['success' => false, 'message' => 'Not logged in.'];
}
$user = $_SESSION['user'] ?? [];
$role = strtolower($user['role'] ?? '');
if ($role !== 'pharmacist') {
    return ['success' => false, 'message' => 'Unauthorized access. Pharmacist role required.'];
}

// Get pharmacyID from session (for pharmacist, id = pharmacyID)
$pharmacyID = (int)($user['id'] ?? 0);
if ($pharmacyID <= 0) {
    return ['success' => false, 'message' => 'Could not determine pharmacy ID.'];
}

$response = ['success' => false, 'message' => ''];

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Invalid request.';
    return $response;
}

$medicationID = intval($_POST['medicationID'] ?? 0);
$stock = intval($_POST['stock'] ?? 0);

if ($medicationID <= 0 || $stock < 0) {
    $response['message'] = 'Missing required fields.';
    return $response;
}

// Verify medication exists
$check = $conn->prepare("SELECT medicationID FROM medication WHERE medicationID = ?");
$check->bind_param("i", $medicationID);
$check->execute();
$medExists = $check->get_result()->fetch_assoc();
$check->close();

if (!$medExists) {
    $response['message'] = 'Medication not found.';
    return $response;
}

// Update pharmacy_medication stock for this pharmacy
// Use INSERT ... ON DUPLICATE KEY UPDATE to handle both insert and update
$update = $conn->prepare("
    INSERT INTO pharmacy_medication (pharmacyID, medicationID, stock)
    VALUES (?, ?, ?)
    ON DUPLICATE KEY UPDATE stock = ?
");

$update->bind_param("iiii", $pharmacyID, $medicationID, $stock, $stock);

if ($update->execute()) {
    $response['success'] = true;
    $response['message'] = 'Stock updated successfully.';
} else {
    $response['message'] = 'Error updating stock.';
}

$update->close();

return $response;
