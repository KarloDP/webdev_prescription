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

    if (isset($_GET['patientID']) && isset($_GET['grouped'])) {

        $patientID = (int)$_GET['patientID'];

        // Ensure only the logged-in patient can get their own data
        if ($role === 'patient' && $patientID !== $userID) {
            respond(['error' => 'Not allowed'], 403);
        }

        // SQL for grouped items per prescription
        $stmt = $conn->prepare("
            SELECT
                p.prescriptionID,
                p.status,
                p.issueDate,
                CONCAT('Dr ', d.firstName, ' ', d.lastName) AS doctorName,
                
                m.genericName AS medicine,
                m.brandName AS brand,
                pi.dosage,
                pi.frequency,
                pi.duration,
                pi.prescribed_amount,
                pi.refill_count,
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
        $result = $stmt->get_result();

        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }

        respond($data);
    }

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
            $stmt->bind_param('i', $prescriptionID);
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
            '
                SELECT
                p.prescriptionID,
                p.issueDate,
                p.expirationDate,
                p.status,

                -- what the card shows as medicine
                GROUP_CONCAT(DISTINCT m.brandName SEPARATOR ", ") AS medicine,

                -- what the card shows as doctor name
                CONCAT(d.firstName, " ", d.lastName) AS doctor_name,

                -- notes / dosage for the card (you can tweak this)
                GROUP_CONCAT(DISTINCT pi.dosage SEPARATOR "; ") AS dosage,
                GROUP_CONCAT(DISTINCT pi.instructions SEPARATOR " | ") AS notes
                FROM prescription p
                JOIN doctor d
                ON p.doctorID = d.doctorID
                JOIN prescriptionitem pi
                ON p.prescriptionID = pi.prescriptionID
                JOIN medication m
                ON pi.medicationID = m.medicationID
                WHERE p.patientID = ?
                GROUP BY
                p.prescriptionID,
                p.issueDate,
                p.expirationDate,
                p.status,
                doctor_name
                ORDER BY p.prescriptionID
            '
        );
        $stmt->bind_param('i', $userID);
    //return all prescription with a certain doctors ID
    } elseif ($role === 'doctor') {
        $stmt = $conn->prepare(
            'SELECT * FROM prescription WHERE doctorID = ? ORDER BY prescriptionID'
        );
        $stmt->bind_param('i', $userID);
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
} 

//POST method add entries to database tables
else if ($method == 'POST') {
    if (in_array($role, ['admin', 'doctor'],true)){
        $raw = file_get_contents("php://input");
        $data = json_decode($raw, true);

        $patientID = trim($data['patientID']);
        $issueDate = trim($data['issueDate']);
        $expirationDate = trim($data['expirationDate']);
        $status = trim($data['status']);
        $doctorID = $userID;
    }
    if ($patientID === '') {
        respond(['error' => 'PatienID is required'], 400);
    }
    $stmt = $conn->prepare('INSERT INTO prescription (patientID, issueDate, expirationDate, status, doctorID) VALUES (?, ?, ?, ?, ?)');
    $stmt->bind_param('isssi', $patientID,$issueDate,$expirationDate,$status,$doctorID);

    if ($stmt->execute()) {
        respond([
            'staus'=> 'success',
            'message' => 'New Prescription Added',
            'insert_ID' => $stmt->insert_id
        ], 201);
    } else {
        respond(['error'=> 'Insert Failed: ' . $stmt->error], 500);
    }
} 

//DELETE method removes entries from database tables
else if ($method === 'DELETE') { //ChatGPT generated, remember that since it will likely be a cause of issues
    // Only admin or doctor can delete
    if (!in_array($role, ['admin', 'doctor'], true)) {
        respond(['error' => 'You are not allowed to delete prescriptions'], 403);
    }

    if (!isset($_GET['prescriptionID'])) {
        respond(['error' => 'prescriptionID is required'], 400);
    }

    $prescriptionID = (int)$_GET['prescriptionID'];

    // If doctor: only allow deleting prescriptions that belong to this doctor
    if ($role === 'doctor') {
        $stmt = $conn->prepare(
            'DELETE FROM prescription WHERE prescriptionID = ? AND doctorID = ?'
        );
        if (!$stmt) {
            respond(['error' => 'Prepare failed: ' . $conn->error], 500);
        }
        $stmt->bind_param('ii', $prescriptionID, $userID);
    } else {
        // admin: can delete any prescription
        $stmt = $conn->prepare(
            'DELETE FROM prescription WHERE prescriptionID = ?'
        );
        if (!$stmt) {
            respond(['error' => 'Prepare failed: ' . $conn->error], 500);
        }
        $stmt->bind_param('i', $prescriptionID);
    }

    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        respond([
            'status'  => 'success',
            'message' => 'Prescription deleted'
        ]);
    } else {
        // Either not found, or doctor tried to delete someone else's prescription
        respond(['error' => 'Prescription not found or not allowed to delete'], 404);
    }
}
?>