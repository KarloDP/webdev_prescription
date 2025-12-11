<?php
// filepath: c:\wamp64\www\WebDev_Prescription\backend\sql_handler\add_prescription.php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/auth.php';

function respond($data, $code = 200) {
    http_response_code($code);
    echo json_encode($data);
    exit;
}

try {
    $user = require_role(['doctor']);
    $doctorID = (int)$user['id'];

    $body = json_decode(file_get_contents('php://input'), true);
    if (!is_array($body)) throw new Exception('Invalid JSON', 400);

    $mode      = $body['mode'] ?? '';
    $brand     = trim($body['brandName'] ?? '');
    $generic   = trim($body['genericName'] ?? '');
    $dosage    = trim($body['dosage'] ?? '');
    $frequency = trim($body['frequency'] ?? '');
    $issueDate = trim($body['issueDate'] ?? '');
    $notes     = trim($body['notes'] ?? '');

    if (!$dosage || !$frequency || !$issueDate) {
        throw new Exception('dosage, frequency, issueDate are required', 400);
    }
    if (!$brand && !$generic) {
        throw new Exception('brandName or genericName is required', 400);
    }

    // Resolve medicationID by brand or generic
    $medicationID = null;
    if ($stmt = $conn->prepare("SELECT medicationID FROM medication WHERE brandName = ? OR genericName = ? LIMIT 1")) {
        $searchName = $brand ?: $generic;
        $stmt->bind_param('ss', $searchName, $searchName);
        $stmt->execute();
        $med = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if ($med) $medicationID = (int)$med['medicationID'];
    }
    if (!$medicationID) {
        throw new Exception('Medicine not found. Please use an existing brand or generic name.', 400);
    }

    // Resolve patient
    if ($mode === 'existing') {
        $patientID = (int)($body['patientId'] ?? 0);
        if ($patientID <= 0) throw new Exception('patientId is required for existing mode', 400);
    } elseif ($mode === 'new') {
        $first  = trim($body['firstName'] ?? '');
        $last   = trim($body['lastName'] ?? '');
        $age    = (int)($body['age'] ?? 0);
        $gender = trim($body['gender'] ?? '');
        $email  = trim($body['email'] ?? '');
        $contact = trim($body['contact'] ?? '');
        if (!$first || !$last || $age <= 0 || !$gender || !$contact) {
            throw new Exception('firstName, lastName, age, gender, contact are required for new mode', 400);
        }
        $birthYear = (int)date('Y') - $age;
        $birthDate = $birthYear . '-01-01';

        $stmt = $conn->prepare("
            INSERT INTO patient (firstName, lastName, birthDate, gender, contactNumber, address, email, doctorID)
            VALUES (?, ?, ?, ?, ?, '', ?, ?)
        ");
        if (!$stmt) throw new Exception('Prepare failed: ' . $conn->error, 500);
        $stmt->bind_param('ssssssi', $first, $last, $birthDate, $gender, $contact, $email, $doctorID);
        $stmt->execute();
        $patientID = $stmt->insert_id;
        $stmt->close();
    } else {
        throw new Exception('mode must be "existing" or "new"', 400);
    }

    // Dates & status
    $expirationDate = date('Y-m-d', strtotime($issueDate . ' +30 days'));
    $status = 'Active';

    if (!$conn->begin_transaction()) throw new Exception('Failed to start transaction: ' . $conn->error, 500);
    $tx = true;

    // Insert prescription
    $stmt = $conn->prepare("
        INSERT INTO prescription (patientID, issueDate, expirationDate, status, doctorID)
        VALUES (?, ?, ?, ?, ?)
    ");
    if (!$stmt) throw new Exception('Prepare failed: ' . $conn->error, 500);
    $stmt->bind_param('isssi', $patientID, $issueDate, $expirationDate, $status, $doctorID);
    $stmt->execute();
    $prescriptionID = $stmt->insert_id;
    $stmt->close();

    // Insert prescription item
    $prescribed_amount = 30;
    $refill_count = 0;
    $refillInterval = '0000-00-00';
    $duration = '';

    $stmt = $conn->prepare("
        INSERT INTO prescriptionitem
            (doctorID, prescriptionID, medicationID, dosage, frequency, duration, prescribed_amount, refill_count, refillInterval, instructions)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    if (!$stmt) throw new Exception('Prepare failed: ' . $conn->error, 500);
    $stmt->bind_param(
        'iiisssiiis',
        $doctorID,
        $prescriptionID,
        $medicationID,
        $dosage,
        $frequency,
        $duration,
        $prescribed_amount,
        $refill_count,
        $refillInterval,
        $notes
    );
    $stmt->execute();
    $stmt->close();

    $conn->commit();
    respond(['success' => true, 'prescriptionID' => $prescriptionID, 'patientID' => $patientID], 201);

} catch (Throwable $e) {
    if (!empty($tx) && isset($conn) && $conn instanceof mysqli) $conn->rollback();
    $code = ($e->getCode() >= 400 && $e->getCode() < 600) ? $e->getCode() : 500;
    respond(['error' => true, 'details' => $e->getMessage()], $code);
}