<?php
// delete_patient.php
include('../includes/db_connect.php');
session_start();

$patientID = intval($_GET['id'] ?? 0);

if ($patientID <= 0) {
    header("Location: patients.php");
    exit;
}

mysqli_begin_transaction($conn);

try {
    // Get all prescriptions of this patient
    $stmt = $conn->prepare("SELECT prescriptionID FROM prescription WHERE patientID = ?");
    $stmt->bind_param("i", $patientID);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();

    // Delete all prescription items linked to those prescriptions
    while ($row = $result->fetch_assoc()) {
        $pID = $row['prescriptionID'];

        $d1 = $conn->prepare("DELETE FROM prescriptionitem WHERE prescriptionID = ?");
        $d1->bind_param("i", $pID);
        if (!$d1->execute()) throw new Exception("Failed to delete items");
        $d1->close();

        $d2 = $conn->prepare("DELETE FROM prescription WHERE prescriptionID = ?");
        $d2->bind_param("i", $pID);
        if (!$d2->execute()) throw new Exception("Failed to delete prescription");
        $d2->close();
    }

    // Finally delete patient
    $d3 = $conn->prepare("DELETE FROM patient WHERE patientID = ?");
    $d3->bind_param("i", $patientID);
    if (!$d3->execute()) throw new Exception("Failed to delete patient");
    $d3->close();

    mysqli_commit($conn);

} catch (Exception $e) {
    mysqli_rollback($conn);
    die("Error deleting patient: " . htmlspecialchars($e->getMessage()));
}

// Redirect
header("Location: patients.php");
exit;
?>
