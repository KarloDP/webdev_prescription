<?php
// Capture any accidental output so we can return clean JSON
ob_start();

// Turn off sending PHP errors to the browser (log them instead)
ini_set('display_errors', '0');
error_reporting(E_ALL);

// Return JSON
header('Content-Type: application/json');

include(__DIR__ . '/../includes/db_connect.php');
include(__DIR__ . '/../includes/auth.php');

try {
    $user = require_role(['doctor']); // will exit/throw if not authorized
    $doctorID = (int)$user['id'];

    $patients = [];

    $sql = "
        SELECT
            p.patientID,
            p.firstName,
            p.lastName,
            p.birthDate,
            p.gender,
            MAX(pr.issueDate) AS lastVisit,
            (SELECT pr_inner.prescriptionID 
             FROM prescription pr_inner 
             WHERE pr_inner.patientID = p.patientID 
             ORDER BY pr_inner.issueDate DESC 
             LIMIT 1) AS lastPrescriptionID
        FROM patient p
        LEFT JOIN prescription pr ON p.patientID = pr.patientID
        WHERE p.doctorID = ?
        GROUP BY p.patientID
        ORDER BY p.lastName ASC, p.firstName ASC
    ";

    if (!$stmt = $conn->prepare($sql)) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }

    $stmt->bind_param('i', $doctorID);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $patients[] = $row;
    }
    $stmt->close();

    // Discard any buffered output (warnings, accidental HTML)
    ob_end_clean();

    echo json_encode($patients);
    $conn->close();
    exit;
} catch (Exception $e) {
    // Discard any buffered output and return JSON error
    ob_end_clean();
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
    if (isset($conn) && $conn) $conn->close();
    exit;
}
?>