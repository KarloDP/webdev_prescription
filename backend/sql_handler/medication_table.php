<?php
include(__DIR__ . '/../includes/db_connect.php');
include(__DIR__ . '/../includes/auth.php');

header('Content-Type: application/json; charset=utf-8');

function respond($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$user   = require_user();

if ($method !== 'GET') {
    respond(['error' => 'Method not allowed'], 405);
}

/*
|--------------------------------------------------------------------------
| MODE 1: NO patientID → return ALL medications (for dropdowns)
|--------------------------------------------------------------------------
*/
if (!isset($_GET['patientID'])) {

    $sql = "
        SELECT
            medicationID,
            genericName,
            brandName
        FROM medication
        ORDER BY genericName, brandName
    ";

    $result = $conn->query($sql);

    if (!$result) {
        respond(['error' => $conn->error], 500);
    }

    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }

    respond($rows);
}

/*
|--------------------------------------------------------------------------
| MODE 2: patientID provided → return prescription history
|--------------------------------------------------------------------------
*/
$patientID = (int) $_GET['patientID'];

$sql = "
    SELECT
        p.prescriptionID,
        m.genericName,
        m.brandName,
        pi.dosage,
        pi.frequency,
        pi.duration
    FROM prescriptionitem pi
    JOIN prescription p ON pi.prescriptionID = p.prescriptionID
    JOIN medication m ON pi.medicationID = m.medicationID
    WHERE p.patientID = ?
    ORDER BY p.prescriptionID DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $patientID);
$stmt->execute();

$result = $stmt->get_result();
$rows = [];

while ($row = $result->fetch_assoc()) {
    $rows[] = $row;
}

respond($rows);
?>