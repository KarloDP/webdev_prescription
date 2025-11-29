<?php
//CODE BELOW IS CHATGPT GENERATED. NEEDS TO BE REVIEWED AND REFINED
//backend\sql_handler\doctor_table.php  
include(__DIR__ . '/../includes/db_connect.php');
include(__DIR__ . '/../includes/auth.php');

function respond($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

$user   = require_user();      // from auth.php
$role   = $user['role'];
$userID = (int)$user['id'];

if ($method === 'GET') {

    // 1) If doctorID is given, return that specific doctor
    if (isset($_GET['doctorID'])) {
        $doctorID = (int)$_GET['doctorID'];

        $stmt = $conn->prepare(
            'SELECT * FROM doctor WHERE doctorID = ?'
        );
        $stmt->bind_param('i', $doctorID);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            respond(['error' => 'Doctor not found'], 404);
        }

        respond($result->fetch_assoc());
    }

    // 2) Patient: see all doctors who wrote prescriptions for them
    if ($role === 'patient') {
        $stmt = $conn->prepare(
            'SELECT DISTINCT d.*
             FROM doctor AS d
             JOIN prescription AS p
               ON p.doctorID = d.doctorID
             WHERE p.patientID = ?'
        );
        $stmt->bind_param('i', $userID);
    } else {
        // 3) Other roles: return all doctors
        $stmt = $conn->prepare('SELECT * FROM doctor');
    }

    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        // If patient has no prescriptions, or no doctors exist
        respond(['error' => 'No doctors found for this query'], 404);
    }

    $doctors = [];
    while ($row = $result->fetch_assoc()) {
        $doctors[] = $row;
    }

    respond($doctors);
}

/**
 * POST
 * Doctors can submit doctor account applications for admin to approve.
 * We’ll insert into `doctor` with status = 'pending'.
 */
else if ($method === 'POST') {

    // Only allow users with role 'doctor' to submit application
    if ($role !== 'doctor') {
        respond(['error' => 'Forbidden: only doctors can submit applications'], 403);
    }

    $input = json_decode(file_get_contents('php://input'), true);

    if (!is_array($input)) {
        respond(['error' => 'Invalid JSON body'], 400);
    }

    // Required fields (adapt as needed)
    $required = ['firstName', 'lastName', 'specialization', 'licenseNumber', 'email', 'clinicAddress'];
    foreach ($required as $field) {
        if (empty($input[$field])) {
            respond(['error' => "Missing required field: {$field}"], 400);
        }
    }

    $firstName      = $input['firstName'];
    $lastName       = $input['lastName'];
    $specialization = $input['specialization'];
    $licenseNumber  = (int)$input['licenseNumber'];
    $email          = $input['email'];
    $clinicAddress  = $input['clinicAddress'];

    $stmt = $conn->prepare(
        'INSERT INTO doctor
            (firstName, lastName, specialization, licenseNumber, email, clinicAddress, status)
         VALUES (?, ?, ?, ?, ?, ?, "pending")'
    );

    // s = string, i = integer
    $stmt->bind_param(
        'sssiss',
        $firstName,
        $lastName,
        $specialization,
        $licenseNumber,
        $email,
        $clinicAddress
    );

    if (!$stmt->execute()) {
        respond(['error' => 'Failed to create doctor application', 'details' => $stmt->error], 500);
    }

    $newId = $stmt->insert_id;

    // Return the newly created doctor row
    $stmt = $conn->prepare('SELECT * FROM doctor WHERE doctorID = ?');
    $stmt->bind_param('i', $newId);
    $stmt->execute();
    $result = $stmt->get_result();
    $doctor = $result->fetch_assoc();

    respond($doctor, 201);
}

/**
 * DELETE
 * This feature should only be available to the admin.
 * Admin can delete doctor by ?doctorID=.
 */
else if ($method === 'DELETE') {

    if ($role !== 'admin') {
        respond(['error' => 'Forbidden: only admin can delete doctors'], 403);
    }

    if (!isset($_GET['doctorID'])) {
        respond(['error' => 'doctorID is required'], 400);
    }

    $doctorID = (int)$_GET['doctorID'];

    $stmt = $conn->prepare('DELETE FROM doctor WHERE doctorID = ?');
    $stmt->bind_param('i', $doctorID);

    if (!$stmt->execute()) {
        // Likely foreign key constraint (patients / prescriptionitems still linked)
        respond(['error' => 'Failed to delete doctor', 'details' => $stmt->error], 500);
    }

    if ($stmt->affected_rows === 0) {
        respond(['error' => 'Doctor not found or already deleted'], 404);
    }

    respond(['message' => 'Doctor deleted successfully']);
}

else {
    respond(['error' => 'Method not allowed'], 405);
}
?>