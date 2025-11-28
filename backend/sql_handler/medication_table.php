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
$role   = $user['role'];
$userID = (int)$user['id'];

if ($method === 'GET') {
    $patientID = isset($_GET['patientID']) ? (int) $_GET['patientID'] : 0;

    if ($patientID <= 0) {
        respond([]); // invalid patientID
        exit;
    }

    $sql = "
        SELECT
            p.prescriptionID,
            m.genericName AS medicine,
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
    if ($stmt) {
        $stmt->bind_param("i", $patientID);
        $stmt->execute();
        $result = $stmt->get_result();

        $rows = [];
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }

        respond($rows);
        $stmt->close();
    } else {
        respond(['error' => 'Query preparation failed: ' . $conn->error], 500);
    }
}
?>
