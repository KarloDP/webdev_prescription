<?php
include(__DIR__ . '/../includes/db_connect.php');
include(__DIR__ . '/../includes/auth.php');

function respond($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

$user   = require_user();
$role   = $user['role'];
$userID = (int)$user['id'];

/* ===================== GET ===================== */
if ($method === 'GET') {

    if (isset($_GET['patientID'])) {
        $patientID = (int)$_GET['patientID'];

        if ($role === 'patient' && $patientID !== $userID) {
            respond(['error' => 'Forbidden'], 403);
        }

        if (in_array($role, ['admin', 'pharmacist'], true)) {
            $stmt = $conn->prepare('SELECT * FROM patient WHERE patientID = ?');
            $stmt->bind_param('i', $patientID);

        } elseif ($role === 'doctor') {
            $stmt = $conn->prepare(
                'SELECT * FROM patient WHERE patientID = ? AND doctorID = ?'
            );
            $stmt->bind_param('ii', $patientID, $userID);

        } elseif ($role === 'patient') {
            $stmt = $conn->prepare('SELECT * FROM patient WHERE patientID = ?');
            $stmt->bind_param('i', $patientID);

        } else {
            respond(['error' => 'Forbidden'], 403);
        }

        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            respond(['error' => 'Patient not found'], 404);
        }

        respond($result->fetch_assoc());
    }

    /* ===== LIST VIEW ===== */
    if (in_array($role, ['admin', 'pharmacist'], true)) {

        $stmt = $conn->prepare(
            'SELECT * FROM patient ORDER BY lastName, firstName, patientID'
        );

    } elseif ($role === 'doctor') {

        if (isset($_GET['scope']) && $_GET['scope'] === 'all') {
            $stmt = $conn->prepare(
                'SELECT * FROM patient ORDER BY lastName, firstName, patientID'
            );
        } else {
            $stmt = $conn->prepare(
                'SELECT * FROM patient
                 WHERE doctorID = ?
                 ORDER BY lastName, firstName, patientID'
            );
            $stmt->bind_param('i', $userID);
        }

    } elseif ($role === 'patient') {

        $stmt = $conn->prepare(
            'SELECT * FROM patient WHERE patientID = ?'
        );
        $stmt->bind_param('i', $userID);

    } else {
        respond(['error' => 'Forbidden'], 403);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    $patients = [];
    while ($row = $result->fetch_assoc()) {
        $patients[] = $row;
    }

    respond($patients);
}

/* ===================== POST ===================== */
else if ($method === 'POST') {

    if (!in_array($role, ['admin', 'doctor'], true)) {
        respond(['error' => 'Forbidden'], 403);
    }

    $input = json_decode(file_get_contents('php://input'), true);
    if (!is_array($input)) {
        respond(['error' => 'Invalid JSON'], 400);
    }

    $required = ['firstName', 'lastName', 'birthDate', 'gender', 'contactNumber', 'address', 'email'];
    foreach ($required as $field) {
        if (empty($input[$field])) {
            respond(['error' => "Missing field: {$field}"], 400);
        }
    }

    $doctorID = ($role === 'doctor') ? $userID : ($input['doctorID'] ?? null);

    $stmt = $conn->prepare(
        'INSERT INTO patient
        (firstName, lastName, birthDate, gender, contactNumber, address, email,
         doctorID, healthCondition, allergies, currentMedication, knownDiseases)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );

    $stmt->bind_param(
        'ssssississss',
        $input['firstName'],
        $input['lastName'],
        $input['birthDate'],
        $input['gender'],
        $input['contactNumber'],
        $input['address'],
        $input['email'],
        $doctorID,
        $input['healthCondition'] ?? null,
        $input['allergies'] ?? null,
        $input['currentMedication'] ?? null,
        $input['knownDiseases'] ?? null
    );

    if (!$stmt->execute()) {
        respond(['error' => 'Insert failed', 'details' => $stmt->error], 500);
    }

    $id = $stmt->insert_id;
    $stmt = $conn->prepare('SELECT * FROM patient WHERE patientID = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();

    respond($stmt->get_result()->fetch_assoc(), 201);
}

/* ===================== DELETE ===================== */
else if ($method === 'DELETE') {

    if ($role === 'patient') {
        $patientID = $userID;
    } elseif ($role === 'admin') {
        if (!isset($_GET['patientID'])) {
            respond(['error' => 'patientID required'], 400);
        }
        $patientID = (int)$_GET['patientID'];
    } else {
        respond(['error' => 'Forbidden'], 403);
    }

    $stmt = $conn->prepare('DELETE FROM patient WHERE patientID = ?');
    $stmt->bind_param('i', $patientID);

    if (!$stmt->execute()) {
        respond(['error' => 'Delete failed', 'details' => $stmt->error], 500);
    }

    respond(['success' => true]);
}

else {
    respond(['error' => 'Method not allowed'], 405);
}