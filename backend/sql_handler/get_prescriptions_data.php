<?php
// Return JSON for prescriptions page (patients, activePrescriptions, prescriptionHistory)
ob_start();
ini_set('display_errors', '0');
error_reporting(E_ALL);
header('Content-Type: application/json');

include(__DIR__ . '/../includes/db_connect.php');
include(__DIR__ . '/../includes/auth.php');

try {
    $user = require_role(['doctor']); // ensure doctor is logged in
    $doctorID = (int) ($user['id'] ?? $user['doctorID'] ?? $user['doctor_id'] ?? $user['user_id'] ?? 0);
    if ($doctorID <= 0) throw new Exception('Unable to determine doctor ID from session.');

    $data = ['patients' => [], 'activePrescriptions' => [], 'prescriptionHistory' => []];

    // --- Patients ---
    $sqlPatients = "
        SELECT p.patientID, p.firstName, p.lastName, p.birthDate, p.gender, p.email,
               MAX(pr.issueDate) AS lastVisit,
               (SELECT pr2.prescriptionID FROM prescription pr2 WHERE pr2.patientID = p.patientID ORDER BY pr2.issueDate DESC LIMIT 1) AS lastPrescriptionID
        FROM patient p
        LEFT JOIN prescription pr ON p.patientID = pr.patientID
        WHERE p.doctorID = ?
        GROUP BY p.patientID
        ORDER BY p.lastName, p.firstName
    ";
    if ($stmt = $conn->prepare($sqlPatients)) {
        $stmt->bind_param('i', $doctorID);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($r = $res->fetch_assoc()) $data['patients'][] = $r;
        $stmt->close();
    }

    // --- Active prescriptions (use medication.genericName & brandName) ---
    $sqlActive = "
        SELECT p.firstName, p.lastName,
               m.genericName AS genericName,
               m.brandName AS brandName,
               pi.dosage, pi.frequency, pr.issueDate, pi.instructions AS notes
        FROM prescriptionitem pi
        JOIN prescription pr ON pi.prescriptionID = pr.prescriptionID
        JOIN patient p ON pr.patientID = p.patientID
        JOIN medication m ON pi.medicationID = m.medicationID
        WHERE pr.doctorID = ? AND pr.status = 'Active'
        ORDER BY pr.issueDate DESC
    ";
    if ($stmt = $conn->prepare($sqlActive)) {
        $stmt->bind_param('i', $doctorID);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($r = $res->fetch_assoc()) {
            // prefer brandName then genericName for display
            $r['medicineName'] = trim($r['brandName'] ?: $r['genericName']);
            unset($r['brandName'], $r['genericName']);
            $data['activePrescriptions'][] = $r;
        }
        $stmt->close();
    }

    // --- Prescription history (non-active) ---
    $sqlHistory = "
        SELECT pr.issueDate, m.genericName AS genericName, m.brandName AS brandName,
               pi.dosage, pr.status, pi.instructions AS notes
        FROM prescriptionitem pi
        JOIN prescription pr ON pi.prescriptionID = pr.prescriptionID
        JOIN medication m ON pi.medicationID = m.medicationID
        WHERE pr.doctorID = ? AND pr.status != 'Active'
        ORDER BY pr.issueDate DESC
    ";
    if ($stmt = $conn->prepare($sqlHistory)) {
        $stmt->bind_param('i', $doctorID);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($r = $res->fetch_assoc()) {
            $r['medicineName'] = trim($r['brandName'] ?: $r['genericName']);
            unset($r['brandName'], $r['genericName']);
            $data['prescriptionHistory'][] = $r;
        }
        $stmt->close();
    }

    ob_end_clean();
    echo json_encode($data);
    $conn->close();
    exit;
} catch (Exception $e) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
    if (isset($conn) && $conn) $conn->close();
    exit;
}
?>