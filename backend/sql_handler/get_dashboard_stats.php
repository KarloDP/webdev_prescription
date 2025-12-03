<?php
header('Content-Type: application/json');
include(__DIR__ . '/../includes/db_connect.php');
include(__DIR__ . '/../includes/auth.php');

// FIX: Use the correct require_role function from your current auth.php
$user = require_role(['doctor']);
$doctorID = (int)$user['id'];

$stats = [
    'total_patients'         => 0,
    'active_prescriptions'   => 0,
    'medications_prescribed' => 0
];

// 1. Total Patients Count
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM patient WHERE doctorID = ?");
$stmt->bind_param('i', $doctorID);
$stmt->execute();
$result = $stmt->get_result();
if ($result) {
    $stats['total_patients'] = (int) $result->fetch_assoc()['count'];
}

// 2. Active Prescriptions Count
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM prescription WHERE doctorID = ? AND status = 'Active'");
$stmt->bind_param('i', $doctorID);
$stmt->execute();
$result = $stmt->get_result();
if ($result) {
    $stats['active_prescriptions'] = (int) $result->fetch_assoc()['count'];
}

// 3. Total Medication Items Prescribed
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM prescriptionitem WHERE doctorID = ?");
$stmt->bind_param('i', $doctorID);
$stmt->execute();
$result = $stmt->get_result();
if ($result) {
    $stats['medications_prescribed'] = (int) $result->fetch_assoc()['count'];
}

echo json_encode($stats);

$stmt->close();
$conn->close();
?>