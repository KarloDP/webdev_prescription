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
    // If patientID is provided, return prescriptions for that patient
    if (isset($_GET['patientID'])) {
        $patientID = (int) $_GET['patientID'];
        if ($patientID <= 0) {
            respond([]);
        }

        // Patients can only see their own records
        if ($role === 'patient' && $patientID !== $userID) {
            respond(['error' => 'Forbidden'], 403);
        }

        $sql = "
            SELECT
                pi.prescriptionItemID,
                pi.prescriptionID,
                m.medicationID,
                m.genericName AS medicine,
                m.brandName,
                m.form,
                m.strength,
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

    // If no patientID is provided, return the full medication list (for doctors/admins/pharmacists)
    if (in_array($role, ['doctor', 'admin', 'pharmacist'], true)) {
        $sql = "
            SELECT
                medicationID,
                genericName AS medicine,
                brandName,
                form,
                strength,
                manufacturer,
                stock
            FROM medication
            ORDER BY genericName ASC
        ";

        $result = $conn->query($sql);
        if ($result) {
            $rows = [];
            while ($row = $result->fetch_assoc()) {
                $rows[] = $row;
            }
            respond($rows);
        } else {
            respond(['error' => 'Query failed: ' . $conn->error], 500);
        }
    }

    // Patients must always pass patientID
    if ($role === 'patient') {
        respond(['error' => 'patientID required'], 400);
    }

    respond(['error' => 'Forbidden'], 403);
}
