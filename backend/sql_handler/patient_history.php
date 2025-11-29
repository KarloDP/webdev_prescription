<?php
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json; charset=utf-8');
//backend\sql_handler\doctor_table.php
// Define respond() here since you don't have respond.php
function respond($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data);
    exit;
}

$user = require_user();
$role = $user['role'];
$userID = (int)$user['id'];

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    respond(['error' => 'Method not allowed'], 405);
}

if ($role !== 'patient') {
    respond(['error' => 'Access denied: only patients can view their history'], 403);
}

$sql = "
    SELECT
        pi.prescriptionID,
        m.genericName AS medicine,
        CONCAT('Dr. ', d.firstName, ' ', d.lastName) AS doctorName,
        p.status,
        pi.prescribed_amount,
        p.issueDate
    FROM prescriptionitem pi
    JOIN prescription p ON pi.prescriptionID = p.prescriptionID
    JOIN medication m ON pi.medicationID = m.medicationID
    JOIN doctor d ON pi.doctorID = d.doctorID
    WHERE p.patientID = ?
    ORDER BY p.issueDate DESC, pi.prescriptionItemID DESC
";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    respond(['error' => 'Prepare failed: ' . $conn->error], 500);
}
$stmt->bind_param("i", $userID);
$stmt->execute();
$result = $stmt->get_result();

$rows = [];
while ($row = $result->fetch_assoc()) {
    $rows[] = $row;
}

respond($rows);
