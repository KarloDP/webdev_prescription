<?php
// backend/sql_handler/dispenserecord_table.php
// Handles get/add/delete for dispenserecord table.

include(__DIR__ . '/../includes/db_connect.php');
include(__DIR__ . '/../includes/auth.php');
require_once __DIR__ . '/../includes/functions.php';

// Start output buffering immediately to catch any unwanted whitespace or includes output
ob_start();

// Disable display of errors in output (logs them instead) to prevent breaking JSON
ini_set('display_errors', '0');
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');

function respond($data, $statusCode = 200)
{
    // Clear the buffer of any previous output (like PHP warnings or whitespace)
    ob_clean();
    http_response_code($statusCode);
    echo json_encode($data);
    exit;
}

try {
    if (!($conn instanceof mysqli)) {
        throw new Exception('Database connection not initialized', 500);
    }

    // ===============================
    // GET: Retrieve dispense records
    // ===============================
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {

        // Patient session auth (frontend uses patientID, not unified user)
        if (!isset($_SESSION['patientID'])) {
            throw new Exception('Unauthorized', 401);
        }

        $role   = 'patient';
        $userID = (int) $_SESSION['patientID'];

        // Patients see only their own dispense history
        if ($role === 'patient') {

            $stmt = $conn->prepare("
                SELECT
                    dr.dispenseID,
                    dr.prescriptionItemID,
                    ph.name AS pharmacyName,
                    dr.dispensedQuantity,
                    dr.dispenseDate
                FROM dispenserecord dr
                JOIN pharmacy ph
                    ON dr.pharmacyID = ph.pharmacyID
                JOIN prescriptionitem pi
                    ON dr.prescriptionItemID = pi.prescriptionItemID
                JOIN prescription p
                    ON pi.prescriptionID = p.prescriptionID
                WHERE p.patientID = ?
                ORDER BY dr.dispenseDate DESC
            ");
            if (!$stmt) {
                throw new Exception('Prepare failed: ' . $conn->error, 500);
            }

            $stmt->bind_param('i', $userID);
            $stmt->execute();
            $result = $stmt->get_result();

            $records = [];
            while ($row = $result->fetch_assoc()) {
                $records[] = $row;
            }

            $stmt->close();
            respond($records);
        }
    }

    // =========================================
    // POST: Insert dispense record (pharmacist)
    // =========================================
    elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {

        // This endpoint is intended for pharmacists
        $user = require_role(['pharmacist']);
        $userID = (int) $user['id'];

        // Read raw JSON input
        $rawInput = file_get_contents('php://input');
        $input = json_decode($rawInput, true);

        if (!is_array($input)) {
            throw new Exception('Invalid JSON input', 400);
        }

        $prescriptionItemID = filter_var($input['prescriptionItemID'] ?? null, FILTER_VALIDATE_INT);
        $dispensedQuantity  = filter_var($input['dispensedQuantity'] ?? null, FILTER_VALIDATE_INT);

        if (!$prescriptionItemID || !$dispensedQuantity || $dispensedQuantity <= 0) {
            throw new Exception(
                'prescriptionItemID and positive dispensedQuantity are required',
                400
            );
        }

        // Resolve pharmacyID from logged-in pharmacist
        $pharmacyID = (int) ($user['pharmacyID'] ?? $user['id'] ?? 0);
        if ($pharmacyID <= 0) {
            throw new Exception('Could not resolve pharmacyID for this pharmacist', 400);
        }

        // Begin transaction
        if (!$conn->begin_transaction()) {
            throw new Exception('Failed to start transaction: ' . $conn->error, 500);
        }
        $txActive = true;

        // Lock prescription item row
        $stmt = $conn->prepare("
            SELECT prescribed_amount, prescriptionID
            FROM prescriptionitem
            WHERE prescriptionItemID = ?
            FOR UPDATE
        ");
        if (!$stmt) throw new Exception('Prepare failed: ' . $conn->error, 500);

        $stmt->bind_param('i', $prescriptionItemID);
        $stmt->execute();
        $item = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$item) {
            throw new Exception('Prescription item not found', 404);
        }

        $currentAmount  = (int) $item['prescribed_amount'];
        $prescriptionID = (int) $item['prescriptionID'];

        if ($dispensedQuantity > $currentAmount) {
            throw new Exception('Dispensed quantity exceeds remaining amount', 400);
        }

        // Update remaining prescribed amount
        $newAmount = $currentAmount - $dispensedQuantity;
        $stmt = $conn->prepare("
            UPDATE prescriptionitem
            SET prescribed_amount = ?
            WHERE prescriptionItemID = ?
        ");
        if (!$stmt) throw new Exception('Prepare failed: ' . $conn->error, 500);

        $stmt->bind_param('ii', $newAmount, $prescriptionItemID);
        $stmt->execute();
        $stmt->close();

        // Insert dispense record
        $stmt = $conn->prepare("
            INSERT INTO dispenserecord
                (prescriptionItemID, pharmacyID, dispensedQuantity, dispenseDate)
            VALUES (?, ?, ?, NOW())
        ");
        if (!$stmt) throw new Exception('Prepare failed: ' . $conn->error, 500);

        $stmt->bind_param(
            'iii',
            $prescriptionItemID,
            $pharmacyID,
            $dispensedQuantity
        );
        $stmt->execute();
        $insertId = $stmt->insert_id;
        $stmt->close();

        // Check if prescription is fully dispensed
        $stmt = $conn->prepare("
            SELECT SUM(prescribed_amount) AS total_remaining
            FROM prescriptionitem
            WHERE prescriptionID = ?
        ");
        if (!$stmt) throw new Exception('Prepare failed: ' . $conn->error, 500);

        $stmt->bind_param('i', $prescriptionID);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        $totalRemaining = (int) ($res['total_remaining'] ?? 0);

        if ($totalRemaining <= 0) {
            $stmt = $conn->prepare("
                UPDATE prescription
                SET status = 'Dispensed'
                WHERE prescriptionID = ?
            ");
            if (!$stmt) throw new Exception('Prepare failed: ' . $conn->error, 500);

            $stmt->bind_param('i', $prescriptionID);
            $stmt->execute();
            $stmt->close();
        }

        // Commit transaction
        $conn->commit();
        $txActive = false;

        // Audit log (if enabled)
        if (function_exists('log_audit')) {
            log_audit(
                $conn,
                $userID,
                'pharmacist',
                'Dispense Medication',
                "Dispensed $dispensedQuantity units for prescription item #$prescriptionItemID"
            );
        }

        respond(['success' => true, 'insert_id' => $insertId], 201);
    }

    // Unsupported method
    else {
        throw new Exception('Method not allowed', 405);
    }

} catch (Throwable $e) {

    if (!empty($txActive) && isset($conn) && ($conn instanceof mysqli)) {
        $conn->rollback();
    }

    $code = ($e->getCode() >= 400 && $e->getCode() < 600)
        ? $e->getCode()
        : 500;

    respond(['error' => true, 'details' => $e->getMessage()], $code);
}
?>
