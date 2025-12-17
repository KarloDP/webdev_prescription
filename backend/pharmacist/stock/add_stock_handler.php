<?php
/**
 * HANDLER: Add Medication Stock
 * Used by: frontend/pharmacist/stock/add_stock.php
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
$strength     = intval($_POST['strength'] ?? 0); // Cast to int as per schema
$manufacturer = trim($_POST['manufacturer'] ?? '');
$addAmount    = intval($_POST['stock'] ?? 0);

if ($genericName === '' || $brandName === '' || $form === '' || $strength <= 0 || $manufacturer === '' || $addAmount <= 0) {
    $response['message'] = 'Required fields missing or invalid.';
    return $response;
}

// Check if medication already exists in medication table
$check = $conn->prepare("
    SELECT medicationID 
    FROM medication 
    WHERE genericName = ? AND brandName = ?
");
$check->bind_param("ss", $genericName, $brandName);
$check->execute();
$existing = $check->get_result()->fetch_assoc();
$check->close();

if ($existing) {
    // Medication exists - update pharmacy_medication stock
    $medicationID = (int)$existing['medicationID'];
    
    // Get current stock for this pharmacy
    $getStock = $conn->prepare("
        SELECT stock FROM pharmacy_medication 
        WHERE pharmacyID = ? AND medicationID = ?
    ");
    $getStock->bind_param("ii", $pharmacyID, $medicationID);
    $getStock->execute();
    $stockResult = $getStock->get_result();
    $currentStock = 0;
    if ($stockRow = $stockResult->fetch_assoc()) {
        $currentStock = (int)$stockRow['stock'];
    }
    $getStock->close();
    
    // Calculate new stock
    $newStock = $currentStock + $addAmount;
    
    // Insert or update pharmacy_medication
    $upsert = $conn->prepare("
        INSERT INTO pharmacy_medication (pharmacyID, medicationID, stock)
        VALUES (?, ?, ?)
        ON DUPLICATE KEY UPDATE stock = ?
    ");
    $upsert->bind_param("iiii", $pharmacyID, $medicationID, $newStock, $newStock);
    
    if ($upsert->execute()) {
        $response['success'] = true;
        $response['message'] = 'Stock updated successfully.';
    } else {
        $response['message'] = 'Failed to update stock: ' . $upsert->error;
        error_log('Update stock error: ' . $upsert->error);
    }
    $upsert->close();
    
    return $response;
}

// Medication doesn't exist - create new medication entry first
// Use transaction to ensure both medication and pharmacy_medication are created atomically
$conn->begin_transaction();

try {
    $insert = $conn->prepare("
        INSERT INTO medication (genericName, brandName, form, strength, manufacturer)
        VALUES (?, ?, ?, ?, ?)
    ");
    
    if (!$insert) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }
    
    $insert->bind_param("ssisi", $genericName, $brandName, $form, $strength, $manufacturer);
    
    if (!$insert->execute()) {
        throw new Exception('Failed to insert medication: ' . $insert->error);
    }
    
    $medicationID = $insert->insert_id;
    $insert->close();
    
    if (!$medicationID || $medicationID <= 0) {
        throw new Exception('Failed to get medication ID after insert');
    }
    
    // Now add stock entry for this pharmacy
    $insertStock = $conn->prepare("
        INSERT INTO pharmacy_medication (pharmacyID, medicationID, stock)
        VALUES (?, ?, ?)
    ");
    
    if (!$insertStock) {
        throw new Exception('Prepare failed for pharmacy_medication: ' . $conn->error);
    }
    
    $insertStock->bind_param("iii", $pharmacyID, $medicationID, $addAmount);
    
    if (!$insertStock->execute()) {
        throw new Exception('Failed to insert pharmacy_medication: ' . $insertStock->error);
    }
    
    $insertStock->close();
    
    // Commit transaction if both inserts succeeded
    $conn->commit();
    
    $response['success'] = true;
    $response['message'] = 'New medication added with stock.';
    
} catch (Exception $e) {
    // Rollback transaction on any error
    $conn->rollback();
    $response['message'] = 'Failed to add medication: ' . $e->getMessage();
    error_log('Add stock error: ' . $e->getMessage());
}

return $response;
