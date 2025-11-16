<?php
include('../includes/db_connect.php');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: patients.php");
    exit;
}
$patientID = intval($_GET['id']);

mysqli_begin_transaction($conn);
try {
    // delete items for all prescriptions of this patient
    $delItems = $conn->prepare("
        DELETE pi FROM prescriptionitem pi
        JOIN prescription p ON pi.prescriptionID = p.prescriptionID
        WHERE p.patientID = ?
    ");
    $delItems->bind_param("i", $patientID);
    $delItems->execute();
    $delItems->close();

    // delete prescriptions
    $delPres = $conn->prepare("DELETE FROM prescription WHERE patientID = ?");
    $delPres->bind_param("i", $patientID);
    $delPres->execute();
    $delPres->close();

    // delete patient
    $delPat = $conn->prepare("DELETE FROM patient WHERE patientID = ?");
    $delPat->bind_param("i", $patientID);
    $delPat->execute();
    $delPat->close();

    mysqli_commit($conn);
    header("Location: patients.php");
    exit;
} catch (Exception $e) {
    mysqli_rollback($conn);
    echo "Delete failed: " . $e->getMessage();
}
