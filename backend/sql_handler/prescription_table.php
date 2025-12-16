<?php
// backend/sql_handler/prescription_table.php
include(__DIR__ . '/../includes/db_connect.php');
include(__DIR__ . '/../includes/auth.php');

function respond($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

$user   = require_user();
$role   = $user['role'];
$userID = (int)$user['id'];

/* ======================================================
   GET METHODS (UNCHANGED)
====================================================== */
if ($method === 'GET') {

    if (isset($_GET['patientID']) && isset($_GET['grouped'])) {

        $patientID = (int)$_GET['patientID'];

        if ($role === 'patient' && $patientID !== $userID) {
            respond(['error' => 'Not allowed'], 403);
        }

        $stmt = $conn->prepare("
            SELECT
                p.prescriptionID,
                p.status,
                p.issueDate,
                p.expirationDate,
                CONCAT('Dr ', d.firstName, ' ', d.lastName) AS doctorName,

                pi.prescriptionItemID,

                m.genericName AS medicine,
                m.brandName   AS brand,
                m.form,
                m.strength,

                pi.dosage,
                pi.frequency,
                pi.duration,
                pi.prescribed_amount,
                pi.refill_count,
                pi.refillInterval,
                pi.instructions

            FROM prescription p
            JOIN prescriptionitem pi ON p.prescriptionID = pi.prescriptionID
            JOIN medication m        ON pi.medicationID   = m.medicationID
            JOIN doctor d            ON p.doctorID        = d.doctorID
            WHERE p.patientID = ?
            ORDER BY p.prescriptionID DESC, pi.prescriptionItemID ASC
        ");

        $stmt->bind_param("i", $patientID);
        $stmt->execute();
        $result = $stmt->get_result();

        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }

        respond($data);
    }

    if (isset($_GET['prescriptionID'])) {
        $id = (int)$_GET['prescriptionID'];

        $stmt = $conn->prepare("
            SELECT p.*, pat.firstName AS patientFirstName, pat.lastName AS patientLastName
            FROM prescription p
            JOIN patient pat ON pat.patientID = p.patientID
            WHERE p.prescriptionID = ?
            LIMIT 1
        ");

        if (!$stmt) {
            respond(['error' => 'Prepare failed: '.$conn->error], 500);
        }

        $stmt->bind_param('i', $id);
        $stmt->execute();
        $rx = $stmt->get_result()->fetch_assoc();
        respond($rx ?: []);
    }

    if ($role === 'patient') {

        $stmt = $conn->prepare("
            SELECT
                p.prescriptionID,
                p.issueDate,
                p.expirationDate,
                p.status,
                GROUP_CONCAT(DISTINCT m.brandName SEPARATOR ', ') AS medicine,
                CONCAT(d.firstName, ' ', d.lastName) AS doctor_name,
                GROUP_CONCAT(DISTINCT pi.dosage SEPARATOR '; ') AS dosage,
                GROUP_CONCAT(DISTINCT pi.instructions SEPARATOR ' | ') AS notes
            FROM prescription p
            JOIN doctor d ON p.doctorID = d.doctorID
            JOIN prescriptionitem pi ON p.prescriptionID = pi.prescriptionID
            JOIN medication m ON pi.medicationID = m.medicationID
            WHERE p.patientID = ?
            GROUP BY p.prescriptionID
            ORDER BY p.prescriptionID
        ");

        $stmt->bind_param('i', $userID);

    } elseif ($role === 'doctor') {

        $stmt = $conn->prepare(
            'SELECT * FROM prescription WHERE doctorID = ? ORDER BY prescriptionID'
        );
        $stmt->bind_param('i', $userID);

    } elseif (in_array($role, ['admin', 'pharmacist'], true)) {

        $stmt = $conn->prepare(
            'SELECT * FROM prescription ORDER BY prescriptionID'
        );

    } else {
        respond(['error' => 'Unknown role'], 403);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    respond($data);
}

/* ======================================================
   POST METHODS (EXTENDED, NOT BROKEN)
====================================================== */
else if ($method === 'POST') {

    if (!in_array($role, ['admin', 'doctor'], true)) {
        respond(['error' => 'Not allowed'], 403);
    }

    $raw  = file_get_contents("php://input");
    $data = json_decode($raw, true);

    if (!is_array($data)) {
        respond(['error' => 'Invalid JSON'], 400);
    }

    /* ---------- MULTI-PRESCRIPTION MODE (NEW) ---------- */
    if (($data['mode'] ?? '') === 'multi') {

        $patientID = (int)($data['patientID'] ?? 0);
        $issueDate = $data['issueDate'] ?? '';
        $items     = $data['medications'] ?? [];

        if (!$patientID || !$issueDate || empty($items)) {
            respond(['error' => 'Missing required fields'], 400);
        }

        $expirationDate = date('Y-m-d', strtotime($issueDate . ' +30 days'));
        $status = 'Active';
        $doctorID = $userID;

        $conn->begin_transaction();

        try {
            $stmt = $conn->prepare("
                INSERT INTO prescription
                (patientID, issueDate, expirationDate, status, doctorID)
                VALUES (?, ?, ?, ?, ?)
            ");

            $stmt->bind_param(
                'isssi',
                $patientID,
                $issueDate,
                $expirationDate,
                $status,
                $doctorID
            );

            $stmt->execute();
            $prescriptionID = $stmt->insert_id;

            if (!$prescriptionID) {
                throw new Exception('Prescription insert failed');
            }

            $stmtItem = $conn->prepare("
                INSERT INTO prescriptionitem
                (prescriptionID, medicationID, dosage, frequency)
                VALUES (?, ?, ?, ?)
            ");

            foreach ($items as $m) {
                $medID = (int)$m['medicationID'];
                $dos   = trim($m['dosage']);
                $freq  = trim($m['frequency']);

                if (!$medID || !$dos || !$freq) {
                    throw new Exception('Invalid medication entry');
                }

                $stmtItem->bind_param(
                    'iiss',
                    $prescriptionID,
                    $medID,
                    $dos,
                    $freq
                );
                $stmtItem->execute();
            }

            $conn->commit();
            respond(['success' => true], 201);

        } catch (Throwable $e) {
            $conn->rollback();
            respond([
                'error'   => 'Failed to save prescription',
                'details'=> $e->getMessage()
            ], 500);
        }
    }

    /* ---------- LEGACY SINGLE-PRESCRIPTION MODE ---------- */
    $patientID = trim($data['patientID'] ?? '');
    $issueDate = trim($data['issueDate'] ?? '');
    $expirationDate = trim($data['expirationDate'] ?? '');
    $status = trim($data['status'] ?? '');
    $doctorID = $userID;

    if ($patientID === '') {
        respond(['error' => 'PatientID is required'], 400);
    }

    $stmt = $conn->prepare(
        'INSERT INTO prescription (patientID, issueDate, expirationDate, status, doctorID)
         VALUES (?, ?, ?, ?, ?)'
    );

    $stmt->bind_param(
        'isssi',
        $patientID,
        $issueDate,
        $expirationDate,
        $status,
        $doctorID
    );

    if ($stmt->execute()) {
        respond([
            'status'    => 'success',
            'message'   => 'New Prescription Added',
            'insert_ID' => $stmt->insert_id
        ], 201);
    }

    respond(['error'=> 'Insert Failed: '.$stmt->error], 500);
}

/* ======================================================
   DELETE METHOD (UNCHANGED)
====================================================== */
else if ($method === 'DELETE') {

    if (!in_array($role, ['admin', 'doctor'], true)) {
        respond(['error' => 'Not allowed'], 403);
    }

    if (!isset($_GET['prescriptionID'])) {
        respond(['error' => 'prescriptionID is required'], 400);
    }

    $prescriptionID = (int)$_GET['prescriptionID'];

    if ($role === 'doctor') {
        $stmt = $conn->prepare(
            'DELETE FROM prescription WHERE prescriptionID = ? AND doctorID = ?'
        );
        $stmt->bind_param('ii', $prescriptionID, $userID);
    } else {
        $stmt = $conn->prepare(
            'DELETE FROM prescription WHERE prescriptionID = ?'
        );
        $stmt->bind_param('i', $prescriptionID);
    }

    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        respond(['status' => 'success']);
    }

    respond(['error' => 'Prescription not found or not allowed'], 404);
}
?>