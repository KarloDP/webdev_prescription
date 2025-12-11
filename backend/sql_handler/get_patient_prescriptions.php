<?php
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json');

function respond($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data);
    exit;
}

try {
    $user = require_user();
    
    if (!$user || $user['role'] !== 'patient') {
        respond(['error' => 'Unauthorized'], 401);
    }

    $patientID = (int)$user['id'];

    $stmt = $conn->prepare("
        SELECT 
            p.prescriptionID,
            p.issueDate,
            p.expirationDate,
            p.status,
            d.firstName as doctorFirstName,
            d.lastName as doctorLastName,
            m.genericName as medicationName,
            m.strength as medicationStrength,
            pi.dosage,
            pi.instructions,
            pi.prescribed_amount
        FROM prescription p
        LEFT JOIN prescriptionitem pi ON p.prescriptionID = pi.prescriptionID
        LEFT JOIN medication m ON pi.medicationID = m.medicationID
        LEFT JOIN doctor d ON p.doctorID = d.doctorID
        WHERE p.patientID = ?
        ORDER BY p.issueDate DESC
        LIMIT 10
    ");

    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param('i', $patientID);
    $stmt->execute();
    $result = $stmt->get_result();
    $rows = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // Group by prescription to create the card structure
    $prescriptions = [];
    foreach ($rows as $row) {
        $rxID = $row['prescriptionID'];
        if (!isset($prescriptions[$rxID])) {
            $prescriptions[$rxID] = [
                'prescriptionID' => $rxID,
                'issueDate' => $row['issueDate'],
                'expirationDate' => $row['expirationDate'],
                'status' => $row['status'],
                'doctorFirstName' => $row['doctorFirstName'],
                'doctorLastName' => $row['doctorLastName'],
                'medications' => []
            ];
        }
        if ($row['medicationName']) {
            $prescriptions[$rxID]['medications'][] = [
                'name' => $row['medicationName'],
                'strength' => $row['medicationStrength'],
                'dosage' => $row['dosage'],
                'instructions' => $row['instructions'],
                'amount' => $row['prescribed_amount']
            ];
        }
    }

    respond(array_values($prescriptions));

} catch (Exception $e) {
    respond(['error' => 'An error occurred', 'details' => $e->getMessage()], 500);
}
?>