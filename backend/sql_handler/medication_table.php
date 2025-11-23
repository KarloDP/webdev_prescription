<?php
// handles add data retrieval and insertion to the database


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

//GET method retieves information from database tables
if ($method === 'GET') {

    //return a specific medication based on an ID
    if (isset($_GET['medicaionID'])) {
        $medicationID = (int)$_GET['medicationID'];

        $stmt = $conn->prepare(
                'SELECT * FROM prescription WHERE prescriptionID = ?'
            );
        $stmt->bind_param('i', $prescriptionID);
    } else {
         $stmt = $conn->prepare(
            'SELECT * FROM prescription ORDER BY prescriptionID'
        );
    }

    $stmt->execute();
    $result = $stmt->get_result();

}

//POST method add entries to database tables
else if ($method === 'POST') { //admins and pharmacies should be able to add and edit entries to this table
    if (in_array($role, ['admin', 'pharmacist'],true)){
        $raw = file_get_contents("php://input");
        $data = json_decode($raw, true);

        $medicationID = trim($data['medicationID']);
        $genericName = trim($data['genericName']);
        $brandName = trim($data['brandName']);
        $form = trim($data['form']);
        $strength = trim($data['strength']);
        $manufacturer = trim($data['manufacturer']);
        $stock = trim($data['stock']);
    }

    $stmt = $conn->prepare('INSERT INTO medicaion (medicaionID, genericName, brandName, form, strength, manufacturer, stock) VALUES (?, ?, ?, ?, ?)');
    $stmt->bind_param('isssisi', $patientID,$issueDate,$expirationDate,$status,$doctorID);

    if ($stmt->execute()) {
        respond([
            'staus'=> 'success',
            'message' => 'New Medication Added',
            'insert_ID' => $stmt->insert_id
        ], 201);
    } else {
        respond(['error'=> 'Insert Failed: ' . $stmt->error], 500);
    }
}

//DELETE method removes entries from database tables
else if ($method === 'DELETE') {    //only admins should be able to remove entries from this table
    // Only admin can delete medications
    if ($role !== 'admin') {
        respond(['error' => 'You are not allowed to delete prescriptions'], 403);
    }

    if (!isset($_GET['medicationID'])) {
        respond(['error' => 'medicationID is required'], 400);
    }

    $medicationID = (int)$_GET['medicationID'];

    $stmt = $conn->prepare(
        'DELETE FROM medication WHERE medicationID = ?'
    );
    if (!$stmt) {
            respond(['error' => 'Prepare failed: ' . $conn->error], 500);
    }
    $stmt->bind_param('i', $medicationID);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        respond([
            'status'  => 'success',
            'message' => 'Medicattion deleted'
        ]);
    } else {
        // Either not found, or doctor tried to delete someone else's prescription
        respond(['error' => 'Medication not found or not allowed to delete'], 404);
    }
}
?>