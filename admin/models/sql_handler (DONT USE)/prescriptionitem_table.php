<?php
//CODE BELOW IS CHATGPT GENERATED. NEEDS TO BE REVIEWED AND REFINED

// handles add, retrieval, and deletion for prescriptionitem table

include(__DIR__ . '/../includes/db_connect.php');
include(__DIR__ . '/../includes/auth.php');

header('Content-Type: application/json; charset=utf-8');

function respond($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

$user  = require_user();   // from auth.php
$role  = $user['role'];
$userID = (int)$user['id'];

if ($method === 'GET') {

    // 1) Get a specific prescription item by ID
    if (isset($_GET['prescriptionItemID'])) {
        $prescriptionItemID = (int)$_GET['prescriptionItemID'];

        if ($role === 'patient') {
            // patient: only items belonging to their prescriptions
            $sql = "
                SELECT pi.*
                FROM prescriptionitem pi
                INNER JOIN prescription p ON pi.prescriptionID = p.prescriptionID
                WHERE pi.prescriptionItemID = ? AND p.patientID = ?
            ";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                respond(['error' => 'Prepare failed: ' . $conn->error], 500);
            }
            $stmt->bind_param('ii', $prescriptionItemID, $userID);

        } elseif ($role === 'doctor') {
            // doctor: only items they prescribed
            $sql = "
                SELECT pi.*
                FROM prescriptionitem pi
                WHERE pi.prescriptionItemID = ? AND pi.doctorID = ?
            ";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                respond(['error' => 'Prepare failed: ' . $conn->error], 500);
            }
            $stmt->bind_param('ii', $prescriptionItemID, $userID);

        } elseif (in_array($role, ['admin', 'pharmacist'], true)) {
            // admin / pharmacist: can see any item
            $sql = "SELECT * FROM prescriptionitem WHERE prescriptionItemID = ?";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                respond(['error' => 'Prepare failed: ' . $conn->error], 500);
            }
            $stmt->bind_param('i', $prescriptionItemID);

        } else {
            respond(['error' => 'Unknown role: ' . $role], 403);
        }

        $stmt->execute();
        $result = $stmt->get_result();

        if (!$result || $result->num_rows === 0) {
            respond(['error' => 'Prescription item not found'], 404);
        }

        respond($result->fetch_assoc());
    }

    // 2) Optionally: get all items for a specific prescriptionID
    if (isset($_GET['prescriptionID'])) {
        $prescriptionID = (int)$_GET['prescriptionID'];

        if ($role === 'patient') {
            $sql = "
                SELECT pi.*
                FROM prescriptionitem pi
                INNER JOIN prescription p ON pi.prescriptionID = p.prescriptionID
                WHERE pi.prescriptionID = ? AND p.patientID = ?
                ORDER BY pi.prescriptionItemID
            ";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                respond(['error' => 'Prepare failed: ' . $conn->error], 500);
            }
            $stmt->bind_param('ii', $prescriptionID, $userID);

        } elseif ($role === 'doctor') {
            $sql = "
                SELECT pi.*
                FROM prescriptionitem pi
                WHERE pi.prescriptionID = ? AND pi.doctorID = ?
                ORDER BY pi.prescriptionItemID
            ";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                respond(['error' => 'Prepare failed: ' . $conn->error], 500);
            }
            $stmt->bind_param('ii', $prescriptionID, $userID);

        } elseif (in_array($role, ['admin', 'pharmacist'], true)) {
            $sql = "
                SELECT *
                FROM prescriptionitem
                WHERE prescriptionID = ?
                ORDER BY prescriptionItemID
            ";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                respond(['error' => 'Prepare failed: ' . $conn->error], 500);
            }
            $stmt->bind_param('i', $prescriptionID);

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

    // 3) Get all prescription items (role-filtered)
    if ($role === 'patient') {
        $sql = "
            SELECT pi.*
            FROM prescriptionitem pi
            INNER JOIN prescription p ON pi.prescriptionID = p.prescriptionID
            WHERE p.patientID = ?
            ORDER BY pi.prescriptionItemID
        ";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            respond(['error' => 'Prepare failed: ' . $conn->error], 500);
        }
        $stmt->bind_param('i', $userID);

    } elseif ($role === 'doctor') {
        $sql = "
            SELECT *
            FROM prescriptionitem
            WHERE doctorID = ?
            ORDER BY prescriptionItemID
        ";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            respond(['error' => 'Prepare failed: ' . $conn->error], 500);
        }
        $stmt->bind_param('i', $userID);

    } elseif (in_array($role, ['admin', 'pharmacist'], true)) {
        $sql = "SELECT * FROM prescriptionitem ORDER BY prescriptionItemID";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            respond(['error' => 'Prepare failed: ' . $conn->error], 500);
        }

    } else {
        respond(['error' => 'Unknown role: ' . $role], 403);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    if (!$result) {
        respond(['error' => 'Database connection error: ' . $conn->error], 500);
    }

    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    respond($data);

} elseif ($method === 'POST') {

    // Only admin or doctor can create prescription items
    if (!in_array($role, ['admin', 'doctor'], true)) {
        respond(['error' => 'You are not allowed to add prescription items'], 403);
    }

    $raw = file_get_contents("php://input");
    $data = json_decode($raw, true);

    if (!is_array($data)) {
        respond(['error' => 'Invalid JSON body'], 400);
    }

    $prescriptionID   = isset($data['prescriptionID'])   ? (int)$data['prescriptionID']   : 0;
    $medicationID     = isset($data['medicationID'])     ? (int)$data['medicationID']     : 0;
    $dosage           = trim($data['dosage']           ?? '');
    $frequency        = trim($data['frequency']        ?? '');
    $duration         = trim($data['duration']         ?? '');
    $prescribedAmount = isset($data['prescribed_amount']) ? (int)$data['prescribed_amount'] : 0;
    $refillCount      = isset($data['refill_count'])      ? (int)$data['refill_count']      : 0;
    $refillInterval   = trim($data['refillInterval']   ?? ''); // 'YYYY-MM-DD'
    $instructions     = trim($data['instructions']     ?? '');
    $doctorID         = $userID; // doctor/admin creating this item

    if ($prescriptionID <= 0 || $medicationID <= 0) {
        respond(['error' => 'prescriptionID and medicationID are required and must be positive'], 400);
    }

    $sql = "
        INSERT INTO prescriptionitem
            (doctorID, prescriptionID, medicationID, dosage, frequency, duration,
             prescribed_amount, refill_count, refillInterval, instructions)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        respond(['error' => 'Prepare failed: ' . $conn->error], 500);
    }

    // i = int, s = string
    $stmt->bind_param(
        'iiisssiiss',
        $doctorID,
        $prescriptionID,
        $medicationID,
        $dosage,
        $frequency,
        $duration,
        $prescribedAmount,
        $refillCount,
        $refillInterval,
        $instructions
    );

    if ($stmt->execute()) {
        respond([
            'status'     => 'success',
            'message'    => 'New prescription item added',
            'insert_ID'  => $stmt->insert_id
        ], 201);
    } else {
        respond(['error' => 'Insert failed: ' . $stmt->error], 500);
    }

} elseif ($method === 'DELETE') {

    // Only admin or doctor can delete
    if (!in_array($role, ['admin', 'doctor'], true)) {
        respond(['error' => 'You are not allowed to delete prescription items'], 403);
    }

    if (!isset($_GET['prescriptionItemID'])) {
        respond(['error' => 'prescriptionItemID is required'], 400);
    }

    $prescriptionItemID = (int)$_GET['prescriptionItemID'];

    if ($role === 'doctor') {
        // doctor can only delete items they own
        $sql = "DELETE FROM prescriptionitem WHERE prescriptionItemID = ? AND doctorID = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            respond(['error' => 'Prepare failed: ' . $conn->error], 500);
        }
        $stmt->bind_param('ii', $prescriptionItemID, $userID);
    } else {
        // admin can delete any item
        $sql = "DELETE FROM prescriptionitem WHERE prescriptionItemID = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            respond(['error' => 'Prepare failed: ' . $conn->error], 500);
        }
        $stmt->bind_param('i', $prescriptionItemID);
    }

    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        respond([
            'status'  => 'success',
            'message' => 'Prescription item deleted'
        ]);
    } else {
        respond(['error' => 'Prescription item not found or not allowed to delete'], 404);
    }

} else {
    respond(['error' => 'Method not allowed'], 405);
}

?>