<?php
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/auth.php';

function respond($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

$user = require_role(['pharmacist', 'doctor', 'admin']); // Protect endpoint
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // This query finds the most recent prescription and doctor for each patient.
    $sql = "
        SELECT 
            p.patientID,
            p.firstName,
            p.lastName,
            p.contactNumber,
            p.address,
            sub.prescriptionID,
            doc.lastName AS doctorLastName
        FROM patient p
        LEFT JOIN (
            SELECT 
                patientID, 
                prescriptionID, 
                doctorID,
                ROW_NUMBER() OVER(PARTITION BY patientID ORDER BY issueDate DESC) as rn
            FROM prescription
        ) AS sub ON p.patientID = sub.patientID AND sub.rn = 1
        LEFT JOIN doctor doc ON sub.doctorID = doc.doctorID
        ORDER BY p.lastName, p.firstName;
    ";

    $result = $conn->query($sql);
    if (!$result) {
        respond(['error' => 'Database query failed: ' . $conn->error], 500);
    }
    $data = $result->fetch_all(MYSQLI_ASSOC);
    respond($data);

} else {
    respond(['error' => 'Method Not Allowed'], 405);
}
?>