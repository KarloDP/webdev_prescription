<?php
// delete_prescription.php
include('../includes/db_connect.php');
session_start();

$prescriptionID = intval($_GET['id'] ?? 0);
$from = $_GET['from'] ?? '';   // "patient" or ""

if ($prescriptionID <= 0) {
    header("Location: view_prescription.php");
    exit;
}

// FIRST: get patientID so redirect works if deletion came from patient page
$stmt = $conn->prepare("SELECT patientID FROM prescription WHERE prescriptionID = ?");
$stmt->bind_param("i", $prescriptionID);
$stmt->execute();
$res = $stmt->get_result();
$pres = $res->fetch_assoc();
$stmt->close();

if (!$pres) {
    header("Location: view_prescription.php");
    exit;
}

$patientID = $pres['patientID'];

mysqli_begin_transaction($conn);

try {
    // DELETE prescription items
    $d1 = $conn->prepare("DELETE FROM prescriptionitem WHERE prescriptionID = ?");
    $d1->bind_param("i", $prescriptionID);
    if (!$d1->execute()) throw new Exception("Failed to delete items");
    $d1->close();

    // DELETE prescription header
    $d2 = $conn->prepare("DELETE FROM prescription WHERE prescriptionID = ?");
    $d2->bind_param("i", $prescriptionID);
    if (!$d2->execute()) throw new Exception("Failed to delete prescription");
    $d2->close();

    mysqli_commit($conn);

} catch (Exception $e) {
    mysqli_rollback($conn);
    die("Error deleting prescription: " . htmlspecialchars($e->getMessage()));
}

// Redirects
if ($from === "patient") {
    header("Location: view_patient_prescription.php?id=" . $patientID);
} else {
    header("Location: view_prescription.php");
}
exit;
?>
