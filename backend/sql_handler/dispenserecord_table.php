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

try {
    if (!($conn instanceof mysqli)) {
        throw new Exception('Database connection not initialized', 500);
    }

    // GET: Retrieve dispense records
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $user = require_user();
        $role = $user['role'];
        $userID = (int)$user['id'];

        // Patients see only their own dispense history
        if ($role === 'patient') {
            $stmt = $conn->prepare("
                SELECT dr.dispenseID, dr.prescriptionItemID, dr.pharmacyID, 
                       dr.dispensedQuantity, dr.dispenseDate,
                       m.brandName, m.genericName, pi.dosage, pi.frequency
                FROM dispenserecord dr
                JOIN prescriptionitem pi ON dr.prescriptionItemID = pi.prescriptionItemID
                JOIN prescription p ON pi.prescriptionID = p.prescriptionID
                JOIN medication m ON pi.medicationID = m.medicationID
                WHERE p.patientID = ?
                ORDER BY dr.dispenseDate DESC
            ");
            if (!$stmt) throw new Exception('Prepare failed: ' . $conn->error, 500);
            $stmt->bind_param('i', $userID);
        } 
        // Pharmacists see records from their pharmacy
        elseif ($role === 'pharmacist') {
            $stmt = $conn->prepare("
                SELECT dr.dispenseID, dr.prescriptionItemID, dr.pharmacyID, 
                       dr.dispensedQuantity, dr.dispenseDate,
                       m.brandName, m.genericName, pi.dosage
                FROM dispenserecord dr
                JOIN prescriptionitem pi ON dr.prescriptionItemID = pi.prescriptionItemID
                JOIN medication m ON pi.medicationID = m.medicationID
                WHERE dr.pharmacyID = ?
                ORDER BY dr.dispenseDate DESC
            ");
            if (!$stmt) throw new Exception('Prepare failed: ' . $conn->error, 500);
            $stmt->bind_param('i', $userID);
        }
        // Doctors/Admins see all
        else {
            $stmt = $conn->prepare("
                SELECT dr.dispenseID, dr.prescriptionItemID, dr.pharmacyID, 
                       dr.dispensedQuantity, dr.dispenseDate,
                       m.brandName, m.genericName, pi.dosage
                FROM dispenserecord dr
                JOIN prescriptionitem pi ON dr.prescriptionItemID = pi.prescriptionItemID
                JOIN medication m ON pi.medicationID = m.medicationID
                ORDER BY dr.dispenseDate DESC
            ");
            if (!$stmt) throw new Exception('Prepare failed: ' . $conn->error, 500);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $records = [];
        while ($row = $result->fetch_assoc()) {
            $records[] = $row;
        }
        $stmt->close();
        respond($records);
    }
    // POST: Insert dispense record (pharmacist only)
    elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $user = require_role(['pharmacist']);
        $doctorID = (int)$user['id'];

        $input = json_decode(file_get_contents('php://input'), true);
        if (!is_array($input)) throw new Exception('Invalid JSON', 400);

        $prescriptionItemID = filter_var($input['prescriptionItemID'] ?? null, FILTER_VALIDATE_INT);
        $dispensedQuantity  = filter_var($input['dispensedQuantity'] ?? null, FILTER_VALIDATE_INT);
        if (!$prescriptionItemID || !$dispensedQuantity || $dispensedQuantity <= 0) {
            throw new Exception('prescriptionItemID and positive dispensedQuantity are required', 400);
        }

        $pharmacyID = (int)($user['pharmacyID'] ?? $user['id'] ?? 0);
        if ($pharmacyID <= 0) {
            throw new Exception('Could not resolve pharmacyID for this pharmacist', 400);
        }

        if (!$conn->begin_transaction()) {
            throw new Exception('Failed to start transaction: ' . $conn->error, 500);
        }
        $txActive = true;

        // Lock and validate
        $stmt = $conn->prepare("SELECT prescribed_amount, prescriptionID FROM prescriptionitem WHERE prescriptionItemID = ? FOR UPDATE");
        if (!$stmt) throw new Exception('Prepare failed: ' . $conn->error, 500);
        $stmt->bind_param('i', $prescriptionItemID);
        $stmt->execute();
        $item = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$item) throw new Exception('Prescription item not found', 404);

        $currentAmount  = (int)$item['prescribed_amount'];
        $prescriptionID = (int)$item['prescriptionID'];

        if ($dispensedQuantity > $currentAmount) {
            throw new Exception('Dispensed quantity exceeds remaining amount', 400);
        }

        // Update remaining
        $newAmount = $currentAmount - $dispensedQuantity;
        $stmt = $conn->prepare("UPDATE prescriptionitem SET prescribed_amount = ? WHERE prescriptionItemID = ?");
        if (!$stmt) throw new Exception('Prepare failed: ' . $conn->error, 500);
        $stmt->bind_param('ii', $newAmount, $prescriptionItemID);
        $stmt->execute();
        $stmt->close();

        // Insert dispense record
        $stmt = $conn->prepare(
            "INSERT INTO dispenserecord (prescriptionItemID, pharmacyID, dispensedQuantity) VALUES (?, ?, ?)"
        );
        if (!$stmt) throw new Exception('Prepare failed: ' . $conn->error, 500);
        $stmt->bind_param('iii', $prescriptionItemID, $pharmacyID, $dispensedQuantity);
        $stmt->execute();
        $insertId = $stmt->insert_id;
        $stmt->close();

        // Check if fully dispensed
        $stmt = $conn->prepare("SELECT SUM(prescribed_amount) AS total_remaining FROM prescriptionitem WHERE prescriptionID = ?");
        if (!$stmt) throw new Exception('Prepare failed: ' . $conn->error, 500);
        $stmt->bind_param('i', $prescriptionID);
        $stmt->execute();
        $totalRemaining = (int)($stmt->get_result()->fetch_assoc()['total_remaining'] ?? 0);
        $stmt->close();

        if ($totalRemaining <= 0) {
            $stmt = $conn->prepare("UPDATE prescription SET status = 'Dispensed' WHERE prescriptionID = ?");
            if (!$stmt) throw new Exception('Prepare failed: ' . $conn->error, 500);
            $stmt->bind_param('i', $prescriptionID);
            $stmt->execute();
            $stmt->close();
        }

        $conn->commit();
        $txActive = false;
        respond(['success' => true, 'insert_id' => $insertId], 201);

    } else {
        throw new Exception('Method not allowed', 405);
    }

} catch (Throwable $e) {
    if (!empty($txActive) && isset($conn) && ($conn instanceof mysqli)) {
        $conn->rollback();
    }
    $code = ($e->getCode() >= 400 && $e->getCode() < 600) ? $e->getCode() : 500;
    respond(['error' => true, 'details' => $e->getMessage()], $code);
}
?>