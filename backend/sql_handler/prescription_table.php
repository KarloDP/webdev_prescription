<?php
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

/* ===================== GET ===================== */
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
            JOIN medication m ON pi.medicationID = m.medicationID
            JOIN doctor d ON p.doctorID = d.doctorID
            WHERE p.patientID = ?
            ORDER BY p.prescriptionID DESC, pi.prescriptionItemID ASC
        ");

        $stmt->bind_param("i", $patientID);
        $stmt->execute();
        respond($stmt->get_result()->fetch_all(MYSQLI_ASSOC));
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

        $stmt->bind_param('i', $id);
        $stmt->execute();
        respond($stmt->get_result()->fetch_assoc() ?: []);
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

        $stmt = $conn->prepare("
            SELECT
                p.*,
                pat.firstName AS patientFirstName,
                pat.lastName AS patientLastName,
                CONCAT(pat.firstName, ' ', pat.lastName) AS patientFullName
            FROM prescription p
            JOIN patient pat ON pat.patientID = p.patientID
            WHERE p.doctorID = ?
            ORDER BY p.prescriptionID
        ");
        $stmt->bind_param('i', $userID);

    } elseif (in_array($role, ['admin', 'pharmacist'], true)) {
        $includeExpired = ($_GET['includeExpired'] ?? '') === '1';

        $sql = "
            SELECT
                p.*,
                pat.firstName AS patientFirstName,
                pat.lastName AS patientLastName,
                CONCAT(pat.firstName, ' ', pat.lastName) AS patientFullName,
                CASE
                    WHEN p.status = 'Expired'
                         OR (p.expirationDate IS NOT NULL AND p.expirationDate < CURDATE()) THEN 1
                    ELSE 0
                END AS isExpired
            FROM prescription p
            JOIN patient pat ON pat.patientID = p.patientID
        ";

        if (!$includeExpired) {
            $sql .= "
                WHERE (p.status IS NULL OR p.status NOT IN ('Expired', 'Cancelled'))
                  AND (p.expirationDate IS NULL OR p.expirationDate >= CURDATE())
            ";
        }

        $sql .= " ORDER BY p.prescriptionID";
        $stmt = $conn->prepare($sql);

    } else {
        respond(['error' => 'Unknown role'], 403);
    }

    $stmt->execute();
    respond($stmt->get_result()->fetch_all(MYSQLI_ASSOC));
}

/* ===================== POST ===================== */
else if ($method === 'POST') {

    if (!in_array($role, ['admin', 'doctor'], true)) {
        respond(['error' => 'Not allowed'], 403);
    }

    $data = json_decode(file_get_contents("php://input"), true);
    if (!is_array($data)) {
        respond(['error' => 'Invalid JSON'], 400);
    }

    if (($data['mode'] ?? '') === 'multi') {

        $patientID = (int)$data['patientID'];
        $issueDate = $data['issueDate'];
        $items     = $data['medications'] ?? [];

        if (!$patientID || !$issueDate || empty($items)) {
            respond(['error' => 'Missing required fields'], 400);
        }

        $expirationDate = $data['expirationDate']
            ?? date('Y-m-d', strtotime($issueDate . ' +30 days'));

        $status = 'Active';
        
        // Get prescription-level notes (will be added to first medication's instructions)
        $prescriptionNotes = trim($data['notes'] ?? '');

        $conn->begin_transaction();

        try {
            $stmt = $conn->prepare("
                INSERT INTO prescription
                (patientID, issueDate, expirationDate, status, doctorID)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->bind_param('isssi', $patientID, $issueDate, $expirationDate, $status, $userID);
            $stmt->execute();

            $prescriptionID = $stmt->insert_id;
            if (!$prescriptionID) {
                throw new Exception('Prescription insert failed');
            }

            $stmtItem = $conn->prepare("
                INSERT INTO prescriptionitem
                (
                    doctorID,
                    prescriptionID,
                    medicationID,
                    dosage,
                    frequency,
                    duration,
                    prescribed_amount,
                    refill_count,
                    refillInterval,
                    instructions
                )
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $isFirstItem = true;
            foreach ($items as $m) {

                $medID     = (int)($m['medicationID'] ?? 0);
                $dosage    = trim($m['dosage'] ?? '');
                $frequency = trim($m['frequency'] ?? '');
                $duration  = trim($m['duration'] ?? '');
                $qty       = (int)($m['prescribed_amount'] ?? 0);
                $refills   = (int)($m['refill_count'] ?? 0);

                // Handle refillInterval: use provided date, or default to expirationDate if empty/invalid
                $refillInput = trim($m['refillInterval'] ?? '');
                if (empty($refillInput) || $refillInput === '0000-00-00' || $refillInput === 'null') {
                    $refill = $expirationDate; // Use prescription expiration date as default
                } else {
                    // Validate the date format
                    $refillTimestamp = strtotime($refillInput);
                    if ($refillTimestamp === false) {
                        $refill = $expirationDate; // Fallback to expiration date if invalid
                    } else {
                        $refill = date('Y-m-d', $refillTimestamp);
                    }
                }

                // Combine prescription-level notes with medication instructions
                $medInstructions = trim($m['instructions'] ?? '');
                if ($isFirstItem && !empty($prescriptionNotes)) {
                    // Prepend prescription notes to first medication's instructions
                    if (!empty($medInstructions)) {
                        $instr = $prescriptionNotes . "\n\n" . $medInstructions;
                    } else {
                        $instr = $prescriptionNotes;
                    }
                } else {
                    $instr = $medInstructions;
                }
                
                $docID     = $userID;
                $isFirstItem = false; // Mark that we've processed the first item

                if (!$medID || !$dosage || !$frequency) {
                    throw new Exception('Invalid medication entry');
                }

                $stmtItem->bind_param(
                    'iiisssiiss',
                    $docID,
                    $prescriptionID,
                    $medID,
                    $dosage,
                    $frequency,
                    $duration,
                    $qty,
                    $refills,
                    $refill,
                    $instr
                );

                $stmtItem->execute();
            }

            $conn->commit();
            respond(['success' => true, 'insert_ID' => $prescriptionID], 201);

        } catch (Throwable $e) {
            $conn->rollback();
            respond(['error' => 'Insert failed', 'details' => $e->getMessage()], 500);
        }
    }

    $patientID = trim($data['patientID'] ?? '');
    $issueDate = trim($data['issueDate'] ?? '');
    $expirationDate = trim($data['expirationDate'] ?? '');
    $status = trim($data['status'] ?? '');

    if ($patientID === '') {
        respond(['error' => 'PatientID is required'], 400);
    }

    $stmt = $conn->prepare(
        'INSERT INTO prescription (patientID, issueDate, expirationDate, status, doctorID)
         VALUES (?, ?, ?, ?, ?)'
    );
    $stmt->bind_param('isssi', $patientID, $issueDate, $expirationDate, $status, $userID);

    if ($stmt->execute()) {
        respond(['status' => 'success', 'insert_ID' => $stmt->insert_id], 201);
    }

    respond(['error' => 'Insert failed'], 500);
}

/* ===================== DELETE ===================== */
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
