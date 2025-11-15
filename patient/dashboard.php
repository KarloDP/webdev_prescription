<?php
session_start();
include('../includes/auth.php');
include('../includes/db_connect.php');

// Check if session is set
if (!isset($_SESSION['patientID'])) {
    header("Location: ../TestLoginPatient.php");
    exit;
}

$patientID = $_SESSION['patientID'];

// Fetch patient name
$stmt = $conn->prepare("SELECT firstName, lastName FROM patient WHERE patientID = ?");
$stmt->bind_param("i", $patientID);
$stmt->execute();
$result = $stmt->get_result();

$patientName = "Patient";
if ($result && $result->num_rows > 0) {
    $patient = $result->fetch_assoc();
    $patientName = $patient['firstName'] . ' ' . $patient['lastName'];
}

// Count active prescriptions
$stmt = $conn->prepare("SELECT COUNT(*) AS total FROM prescription WHERE patientID = ? AND status = 'Active'");
$stmt->bind_param("i", $patientID);
$stmt->execute();
$active_count = $stmt->get_result()->fetch_assoc()['total'] ?? 0;

// Get last 4 prescriptions
$stmt = $conn->prepare("
    SELECT p.prescriptionID, m.genericName, p.issueDate
    FROM prescription p
    JOIN medication m ON p.medicationID = m.medicationID
    WHERE p.patientID = ?
    ORDER BY p.issueDate DESC
    LIMIT 4
");
$stmt->bind_param("i", $patientID);
$stmt->execute();
$recentPrescriptions = $stmt->get_result();

// Set Active Page for Sidebar
$activePage = 'dashboard';

// Build dashboard content
$content = "
<h2>Welcome " . strtoupper($patientName) . "!</h2>
<p>View prescriptions.</p>

<div class='summary-box'>
    <h3>{$active_count} Active Prescriptions</h3>
    <a href='medications.php' class='btn-view'>View Details</a>
</div>

<div class='prescription-grid'>
";

if ($recentPrescriptions->num_rows > 0) {
    while ($row = $recentPrescriptions->fetch_assoc()) {
        $dateFormatted = date("F j", strtotime($row['issueDate']));
        $content .= "
            <div class='prescription-card'>
                <h4>{$row['genericName']}</h4>
                <p>{$dateFormatted}<br>First time use</p>
            </div>
        ";
    }
} else {
    $content .= "<p>No recent prescriptions found.</p>";
}

$content .= "</div>";

// Render layout
include 'patient_standard.php';
?>