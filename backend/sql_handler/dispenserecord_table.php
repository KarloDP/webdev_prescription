<?php
//CODE BELOW IS CHATGPT GENERATED. NEEDS TO BE REVIEWED AND REFINED

// backend/sql_handler/dispense_record.php
// Handles get/add/delete for dispenserecord table.
include(__DIR__ . '/../includes/db_connect.php');
include(__DIR__ . '/../includes/auth.php');

header('Content-Type: application/json; charset=utf-8');

function respond($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data);
    exit;
}

$txActive = false;

try {
    // Load shared connection and auth once
    require_once __DIR__ . '/../includes/db_connect.php';
    require_once __DIR__ . '/../includes/auth.php';

    // Ensure $conn from db_connect.php
    if (!isset($conn)) { global $conn; }
    if (!($conn instanceof mysqli)) {
        throw new Exception('Database connection not initialized', 500);
    }
    if ($conn->connect_errno) {
        throw new Exception('Database connection error: ' . $conn->connect_error, 500);
    }

    // Only pharmacists can dispense
    $user = require_role(['pharmacist']); // ensures logged in and role
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method not allowed', 405);
    }
    // Parse JSON
    $input = json_decode(file_get_contents('php://input'), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON input', 400);
    }

    $prescriptionItemID = filter_var($input['prescriptionItemID'] ?? null, FILTER_VALIDATE_INT);
    $dispensedQuantity  = filter_var($input['dispensedQuantity'] ?? null, FILTER_VALIDATE_INT);
    if (!$prescriptionItemID || !$dispensedQuantity || $dispensedQuantity <= 0) {
        throw new Exception('prescriptionItemID and positive dispensedQuantity are required', 400);
    }

    // Resolve pharmacyID from session; if not present, fall back to user id
    // Adjust if your auth stores a different key (e.g., pharmacy_id)
    $pharmacyID = (int)($user['pharmacyID'] ?? $user['id'] ?? 0);
    if ($pharmacyID <= 0) {
        throw new Exception('Could not resolve pharmacyID for this pharmacist', 400);
    }

    // Start transaction
    if (!$conn->begin_transaction()) {
        throw new Exception('Failed to start transaction: ' . $conn->error, 500);
    }
    $txActive = true;

    // Lock item row and validate remaining amount
    $stmt = $conn->prepare("SELECT prescribed_amount, prescriptionID FROM prescriptionitem WHERE prescriptionItemID = ? FOR UPDATE");
    if (!$stmt) throw new Exception('Prepare failed (select item): ' . $conn->error, 500);
    $stmt->bind_param('i', $prescriptionItemID);
    $stmt->execute();
    $item = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$item) {
        throw new Exception('Prescription item not found', 404);
    }

    $currentAmount  = (int)$item['prescribed_amount'];
    $prescriptionID = (int)$item['prescriptionID'];

    if ($dispensedQuantity > $currentAmount) {
        throw new Exception('Dispensed quantity exceeds remaining amount', 400);
    }

    // Update remaining quantity on the prescription item
    $newAmount = $currentAmount - $dispensedQuantity;
    $stmt = $conn->prepare("UPDATE prescriptionitem SET prescribed_amount = ? WHERE prescriptionItemID = ?");
    if (!$stmt) throw new Exception('Prepare failed (update item): ' . $conn->error, 500);
    $stmt->bind_param('ii', $newAmount, $prescriptionItemID);
    $stmt->execute();
    $stmt->close();

    // Insert into dispenserecord (dispenseDate uses default CURRENT_TIMESTAMP)
    $stmt = $conn->prepare(
        "INSERT INTO dispenserecord (prescriptionItemID, pharmacyID, dispensedQuantity) VALUES (?, ?, ?)"
    );
    if (!$stmt) throw new Exception('Prepare failed (insert record): ' . $conn->error, 500);
    $stmt->bind_param('iii', $prescriptionItemID, $pharmacyID, $dispensedQuantity);
    $stmt->execute();
    $insertId = $stmt->insert_id;
    $stmt->close();

    // If all items are dispensed, optionally update prescription status to 'Dispensed'
    $stmt = $conn->prepare("SELECT SUM(prescribed_amount) AS total_remaining FROM prescriptionitem WHERE prescriptionID = ?");
    if (!$stmt) throw new Exception('Prepare failed (check total): ' . $conn->error, 500);
    $stmt->bind_param('i', $prescriptionID);
    $stmt->execute();
    $totalRemaining = (int)($stmt->get_result()->fetch_assoc()['total_remaining'] ?? 0);
    $stmt->close();

    if ($totalRemaining <= 0) {
        $stmt = $conn->prepare("UPDATE prescription SET status = 'Dispensed' WHERE prescriptionID = ?");
        if (!$stmt) throw new Exception('Prepare failed (update prescription status): ' . $conn->error, 500);
        $stmt->bind_param('i', $prescriptionID);
        $stmt->execute();
        $stmt->close();
    }

    $conn->commit();
    $txActive = false;

    respond(['success' => true, 'insert_id' => $insertId], 201);

} catch (Throwable $e) {
    if ($txActive && isset($conn) && ($conn instanceof mysqli)) {
        $conn->rollback();
    }
    $code = ($e->getCode() >= 400 && $e->getCode() < 600) ? $e->getCode() : 500;
    respond(['error' => true, 'details' => $e->getMessage()], $code);
}
?>