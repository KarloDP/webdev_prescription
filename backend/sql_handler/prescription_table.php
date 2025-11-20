<?php
// handles add data retrieval and insertion to the database
include(__DIR__ . '/../includes/db_connect.php');
include(__DIR__.'/../includes/auth.php');


function respond($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

$user = require_user(); //if exists in a helper, use to make sure user is logged in should be implemented in auth.php and session.php
$role = $user['role'];
$userID = (int)$user['id'];

if ($method == 'GET') {
    //return a specific prescription based on an ID
    if (isset($_GET['prescriptionID'])) {
        $prescriptionID = (int)$_GET['prescriptionID'];

        //patient
        if ($role === 'patient') {
            $stmt = $conn->prepare(
                'SELECT * FROM prescription WHERE prescriptionID = ? AND patientID = ?'
            );
            $stmt->bind_param('ii', $prescriptionID, $userID);
        } else { //all other adminisatrative users
            $stmt = $conn->prepare(
                'SELECT * FROM prescription WHERE prescriptionID = ?'
            );
            $stmt->bind_param('ii', $prescriptionID);
        }

        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            respond(['error' => 'Prescription not found'], 404);
        }
        respond($result->fetch_assoc());
    }

    //retun all prescription with a certain patients ID
    if ($role === 'patient') {
        $stmt = $conn->prepare(
            'SELECT * FROM prescription WHERE patientID = ? ORDER BY prescriptionID'
        );
        $stmt->bind_param('i', $userID);
    //return all prescription with a certain doctors ID
    } elseif ($role === 'doctor') {
        $stmt = $conn->prepare(
            'SELECT * FROM prescription WHERE doctorID = ? ORDER BY prescriptionID'
        );
        $stmt->bind_param('doctorID', $userID);
    //return all prescriptions
    } elseif (in_array($role, ['admin', 'pharmacist'], true)) {
        $stmt = $conn->prepare(
            'SELECT * FROM prescription ORDER BY prescriptionID'
        );
    } else {
        respond(['error' => 'Unknown role: '.$role], 403);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    if (!$result) {
        respond(['error' => 'Database connection error: '.$conn->error], 500);
    }

    $data=[];
    while ($row = $result->fetch_assoc()) {
        $data[]=$row;
    }
    respond($data);
} else if ($method == 'POST') {
    if (in_array($role, ['admin', 'doctor'],true)){
        $raw = file_get_contents("pgp://input");
        $data = json_decode($raw, true);

        $medicaionID = trim($data['medicationID']);
        $patientID = trim($data['patientID']);
        $issueDate = trim($data['issueDate']);
        $expirationDate = trim($data['expirationDate']);
        $refillInterval = trim($data['refillInterval']);
        $status = trim($data['status']);
        $doctorID = $userID;
    }
}
?>