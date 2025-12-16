<?php
//CODE BELOW IS CHATGPT GENERATED. NEEDS TO BE REVIEWED AND REFINED

// handles patient data retrieval and manipulation
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
$userID = (int)$user['id']; // for patients this is patientID, for doctors this is doctorID, etc.

/**
 * GET
 * - admins & pharmacists: list all patients, or a specific patient via ?patientID=
 * - doctors: list only their patients, or specific one if belongs to them
 * - patients: can only get their own info
 */
if ($method === 'GET') {

    // If a specific patientID is requested
    if (isset($_GET['patientID'])) {
        $patientID = (int)$_GET['patientID'];

        // Patient can only access their own record
        if ($role === 'patient' && $patientID !== $userID) {
            respond(['error' => 'Forbidden: patients can only view their own information'], 403);
        }

        if ($role === 'admin' || $role === 'pharmacist') {
            // Admin/pharmacist can see any patient
            $stmt = $conn->prepare(
                'SELECT * FROM patient WHERE patientID = ?'
            );
            $stmt->bind_param('i', $patientID);
        } elseif ($role === 'doctor') {
            // Doctor can see only their own patients
            $stmt = $conn->prepare(
                'SELECT * FROM patient
                 WHERE patientID = ? AND doctorID = ?'
            );
            $stmt->bind_param('ii', $patientID, $userID);
        } elseif ($role === 'patient') {
            // Already ensured $patientID === $userID
            $stmt = $conn->prepare(
                'SELECT * FROM patient WHERE patientID = ?'
            );
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

    // No specific patientID requested → list view
    if ($role === 'admin' || $role === 'pharmacist') {
        // List all patients
        $stmt = $conn->prepare(
            'SELECT * FROM patient ORDER BY lastName, firstName, patientID'
        );
    } elseif ($role === 'doctor') {
        // List only this doctor's patients
        $stmt = $conn->prepare(
            'SELECT * FROM patient
             WHERE doctorID = ?
             ORDER BY lastName, firstName, patientID'
        );
        $stmt->bind_param('i', $userID);
    } elseif ($role === 'patient') {
        // Patient: only their own info
        $stmt = $conn->prepare(
            'SELECT * FROM patient WHERE patientID = ?'
        );
        $stmt->bind_param('i', $userID);
    } else {
        respond(['error' => 'Forbidden'], 403);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        respond(['error' => 'No patients found for this query'], 404);
    }

    $patients = [];
    while ($row = $result->fetch_assoc()) {
        $patients[] = $row;
    }

    respond($patients);
}

/**
 * POST
 * - Only admins and doctors can add patients.
 * - Unauthenticated users should create accounts via login page (this file assumes require_user() already ran).
 */
else if ($method === 'POST') {

    if (!in_array($role, ['admin', 'doctor'], true)) {
        respond(['error' => 'Forbidden: only admins and doctors can add patients'], 403);
    }

    $input = json_decode(file_get_contents('php://input'), true);
    if (!is_array($input)) {
        respond(['error' => 'Invalid JSON body'], 400);
    }

    // Required fields for creating a patient
    $required = ['firstName', 'lastName', 'birthDate', 'gender', 'contactNumber', 'address', 'email'];
    foreach ($required as $field) {
        if (empty($input[$field])) {
            respond(['error' => "Missing required field: {$field}"], 400);
        }
    }

    $firstName      = $input['firstName'];
    $lastName       = $input['lastName'];
    $birthDate      = $input['birthDate']; // expect 'YYYY-MM-DD'
    $gender         = $input['gender'];
    $contactNumber  = (int)$input['contactNumber'];
    $address        = $input['address'];
    $email          = $input['email'];

    // Optional fields
    $healthCondition   = $input['healthCondition']   ?? null;
    $allergies         = $input['allergies']         ?? null;
    $currentMedication = $input['currentMedication'] ?? null;
    $knownDiseases     = $input['knownDiseases']     ?? null;

    // doctorID assignment logic:
    // - For doctors: automatically assign the logged-in doctor's ID
    // - For admins: they can optionally specify doctorID, else NULL
    if ($role === 'doctor') {
        $doctorID = $userID;
    } else { // admin
        if (isset($input['doctorID']) && $input['doctorID'] !== '') {
            $doctorID = (int)$input['doctorID'];
        } else {
            $doctorID = null; // patient not yet attached to a doctor
        }
    }

    $stmt = $conn->prepare(
        'INSERT INTO patient
            (firstName, lastName, birthDate, gender, contactNumber, address, email,
             doctorID, healthCondition, allergies, currentMedication, knownDiseases)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );

    $stmt->bind_param(
        'ssssississss',
        $firstName,
        $lastName,
        $birthDate,
        $gender,
        $contactNumber,
        $address,
        $email,
        $doctorID,
        $healthCondition,
        $allergies,
        $currentMedication,
        $knownDiseases
    );

    if (!$stmt->execute()) {
        respond(['error' => 'Failed to create patient', 'details' => $stmt->error], 500);
    }

    $newId = $stmt->insert_id;

    // Return the newly created patient row
    $stmt = $conn->prepare('SELECT * FROM patient WHERE patientID = ?');
    $stmt->bind_param('i', $newId);
    $stmt->execute();
    $result  = $stmt->get_result();
    $patient = $result->fetch_assoc();

    respond($patient, 201);
}

/**
 * DELETE
 * - Patients can delete only their own account.
 * - Admins can delete any patient account.
 */
else if ($method === 'DELETE') {

    // Determine which patientID is being targeted for deletion
    if ($role === 'patient') {
        // Patient can only delete themselves; ignore or reject other IDs
        if (isset($_GET['patientID']) && (int)$_GET['patientID'] !== $userID) {
            respond(['error' => 'Forbidden: patients can only delete their own account'], 403);
        }
        $patientID = $userID;
    } elseif ($role === 'admin') {
        if (!isset($_GET['patientID'])) {
            respond(['error' => 'patientID is required'], 400);
        }
        $patientID = (int)$_GET['patientID'];
    } else {
        respond(['error' => 'Forbidden: only patients (self) or admins can delete accounts'], 403);
    }

    $stmt = $conn->prepare('DELETE FROM patient WHERE patientID = ?');
    $stmt->bind_param('i', $patientID);

    if (!$stmt->execute()) {
        // Likely foreign key constraint (prescriptions, etc.)
        respond(['error' => 'Failed to delete patient', 'details' => $stmt->error], 500);
    }

    if ($stmt->affected_rows === 0) {
        respond(['error' => 'Patient not found or already deleted'], 404);
    }

    respond(['message' => 'Patient deleted successfully']);
}

else {
    respond(['error' => 'Method not allowed'], 405);
}
