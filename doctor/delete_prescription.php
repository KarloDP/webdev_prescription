<?php
include('../includes/db_connect.php');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: view_prescription.php");
    exit;
}
$prescriptionID = intval($_GET['id']);

// get patient for redirect if needed
$stmt = $conn->prepare("SELECT patientID FROM prescription WHERE prescriptionID = ?");
$stmt->bind_param("i", $prescriptionID);
$stmt->execute();
$res = $stmt->get_result()->fetch_assoc();
$stmt->close();

$patientID = $res['patientID'] ?? null;

mysqli_begin_transaction($conn);
try {
    $delItems = $conn->prepare("DELETE FROM prescriptionitem WHERE prescriptionID = ?");
    $delItems->bind_param("i", $prescriptionID);
    $delItems->execute();
    $delItems->close();

    $delPres = $conn->prepare("DELETE FROM prescription WHERE prescriptionID = ?");
    $delPres->bind_param("i", $prescriptionID);
    $delPres->execute();
    $delPres->close();

    mysqli_commit($conn);

    if (isset($_GET['from']) && $_GET['from'] === 'patient' && $patientID) {
        header("Location: view_patient_prescription.php?id=".$patientID);
    } else {
        header("Location: view_prescription.php");
    }
    exit;
} catch (Exception $e) {
    mysqli_rollback($conn);
    echo "Delete failed: " . $e->getMessage();
}
