<?php
header('Content-Type: application/json');
include(__DIR__ . '/../includes/db_connect.php');
include(__DIR__ . '/../includes/auth.php');

// FIX: Use the correct require_role function from your current auth.php
$user = require_role(['doctor']); 
$doctorID = (int)$user['id'];

// Fetch a limited number of patients for the dashboard preview
$stmt = $conn->prepare(
    "SELECT 
        patientID AS PatientID, 
        firstName AS FirstName, 
        lastName AS LastName, 
        birthDate AS DateOfBirth 
     FROM patient 
     WHERE doctorID = ? 
     ORDER BY patientID ASC 
     LIMIT 4"
);
$stmt->bind_param('i', $doctorID);
$stmt->execute();
$result = $stmt->get_result();

$patients = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $patients[] = $row;
    }
}

echo json_encode($patients);

$stmt->close();
$conn->close();
?>