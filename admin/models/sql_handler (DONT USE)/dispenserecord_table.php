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

$method = $_SERVER['REQUEST_METHOD'];

$user   = require_user();          // from auth.php
$role   = $user['role'];           // 'patient', 'doctor', 'pharmacist', 'admin'
$userID = (int)$user['id'];        // patientID/doctorID/pharmacistID/adminID depending on role

/* ===========================================
   GET  - view dispense history
   =========================================== */
if ($method === 'GET') {

    // If a specific dispenseID is requested
    if (isset($_GET['dispenseID'])) {
        $dispenseID = (int)$_GET['dispenseID'];

        if ($role === 'patient') {
            // Patients: only their own records (via join patient -> prescription)
            $sql = "
                SELECT dr.*
                FROM dispenserecord dr
                JOIN prescriptionitem pi ON dr.prescriptionItemID = pi.prescriptionItemID
                JOIN prescription p      ON pi.prescriptionID = p.prescriptionID
                WHERE dr.dispenseID = ? AND p.patientID = ?
            ";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                respond(['error' => 'Prepare failed: ' . $conn->error], 500);
            }
            $stmt->bind_param('ii', $dispenseID, $userID);

        } elseif (in_array($role, ['admin', 'pharmacist', 'doctor'], true)) {
            // Admin, pharmacist, doctor: can see any record
            $sql = "SELECT * FROM dispenserecord WHERE dispenseID = ?";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                respond(['error' => 'Prepare failed: ' . $conn->error], 500);
            }
            $stmt->bind_param('i', $dispenseID);

        } else {
            respond(['error' => 'Unknown role: ' . $role], 403);
        }

        $stmt->execute();
        $result = $stmt->get_result();

        if (!$result || $result->num_rows === 0) {
            respond(['error' => 'Dispense record not found'], 404);
        }

        respond($result->fetch_assoc());
    }

    // No specific dispenseID → list records
    if ($role === 'patient') {
        // Only dispenserecords that belong to this patient
        $sql = "
            SELECT dr.*
            FROM dispenserecord dr
            JOIN prescriptionitem pi ON dr.prescriptionItemID = pi.prescriptionItemID
            JOIN prescription p      ON pi.prescriptionID = p.prescriptionID
            WHERE p.patientID = ?
            ORDER BY dr.dateDispensed DESC, dr.dispenseID DESC
        ";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            respond(['error' => 'Prepare failed: ' . $conn->error], 500);
        }
        $stmt->bind_param('i', $userID);

    } elseif (in_array($role, ['admin', 'pharmacist', 'doctor'], true)) {
        //for all administrative users
        //find record by dispenseID
        if (isset($_GET['dispenseID'])) {
            $dispenseID = (int)$_GET['dispenseID'];
            $stmt = $conn->prepare(
                'SELECT *
                FROM dispenserecord
                WHERE dispenseID = ?'
            );
            if (!$stmt) {
                respond(['error' => 'Prepare failed: ' . $conn->error], 500);
            }
            $stmt->bind_param('i', $dispenseID);
        
        //find records by prescriptionItemID
        }else if(isset($_GET['prescriptionItemID'])){
            $prescriptionItemID = (int)$_GET['prescriptionItemID'];
            $stmt = $conn->prepare(
                'SELECT *
                FROM dispenserecord
                WHERE prescriptionItemID = ?
                ORDER BY dateDispensed DESC, dispenseID DESC'
            );
            if (!$stmt) {
                respond(['error' => 'Prepare failed: ' . $conn->error], 500);
            }
            $stmt->bind_param('i', $prescriptionItemID);
        }
        
        else {
            //see all records
            $stmt = $conn->prepare(
                'SELECT *
                FROM dispenserecord
                ORDER BY dateDispensed DESC, dispenseID DESC'
            );
        }
        if (!$stmt) {
            respond(['error' => 'Prepare failed: ' . $conn->error], 500);
        }
    } else {
        respond(['error' => 'Unknown role: ' . $role], 403);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    if (!$result) {
        respond(['error' => 'Database error: ' . $conn->error], 500);
    }

    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    respond($data);
}

/* ===========================================
   POST - add dispense record
   Only pharmacists can add.
   =========================================== */
elseif ($method === 'POST') {

    if ($role !== 'pharmacist') {
        respond(['error' => 'Only pharmacists can add dispense records'], 403);
    }

    $raw  = file_get_contents('php://input');
    $data = json_decode($raw, true);

    if (!is_array($data)) {
        respond(['error' => 'Invalid JSON body'], 400);
    }

    $prescriptionItemID = isset($data['prescriptionItemID']) ? (int)$data['prescriptionItemID'] : 0;
    $pharmacyID         = isset($data['pharmacyID'])         ? (int)$data['pharmacyID']         : 0;
    $quantityDispensed  = isset($data['quantityDispensed'])  ? (int)$data['quantityDispensed']  : 0;
    $dateDispensed      = trim($data['dateDispensed']      ?? '');     // 'YYYY-MM-DD'
    $status             = trim($data['status']             ?? '');
    $nextAvailableDates = trim($data['nextAvailableDates'] ?? '');     // 'YYYY-MM-DD'

    // pharmacistName from session user
    $pharmacistName = $user['name'] ?? 'Pharmacist';

    if ($prescriptionItemID <= 0 || $pharmacyID <= 0 || $quantityDispensed <= 0 ||
        $dateDispensed === '' || $status === '' || $nextAvailableDates === '') {
        respond(['error' => 'All fields are required'], 400);
    }

    $sql = "
        INSERT INTO dispenserecord
            (prescriptionItemID, pharmacyID, quantityDispensed, dateDispensed,
             pharmacistName, status, nextAvailableDates)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        respond(['error' => 'Prepare failed: ' . $conn->error], 500);
    }

    // 3 ints, 4 strings (dates treated as strings)
    $stmt->bind_param(
        'iiissss',
        $prescriptionItemID,
        $pharmacyID,
        $quantityDispensed,
        $dateDispensed,
        $pharmacistName,
        $status,
        $nextAvailableDates
    );

    if ($stmt->execute()) {
        respond([
            'status'      => 'success',
            'message'     => 'Dispense record added',
            'insert_ID'   => $stmt->insert_id
        ], 201);
    } else {
        respond(['error' => 'Insert failed: ' . $stmt->error], 500);
    }
}

/* ===========================================
   DELETE - delete dispense record
   Only admins can delete.
   =========================================== */
elseif ($method === 'DELETE') {

    if ($role !== 'admin') {
        respond(['error' => 'Only admins can delete dispense records'], 403);
    }

    if (!isset($_GET['dispenseID'])) {
        respond(['error' => 'dispenseID is required'], 400);
    }

    $dispenseID = (int)$_GET['dispenseID'];

    $stmt = $conn->prepare("DELETE FROM dispenserecord WHERE dispenseID = ?");
    if (!$stmt) {
        respond(['error' => 'Prepare failed: ' . $conn->error], 500);
    }
    $stmt->bind_param('i', $dispenseID);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        respond([
            'status'  => 'success',
            'message' => 'Dispense record deleted'
        ]);
    } else {
        respond(['error' => 'Dispense record not found'], 404);
    }
}

/* ===========================================
   Unsupported method
   =========================================== */
else {
    respond(['error' => 'Method not allowed'], 405);
}
?>