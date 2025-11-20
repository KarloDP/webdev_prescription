<?php
include('../includes/db_connect.php');

// Validate patient ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<script>alert('Invalid Patient ID'); window.location='patients.php';</script>";
    exit;
}

$patientID = (int)$_GET['id'];

// Begin transaction
mysqli_begin_transaction($conn);

try {

    // Delete prescription items for this patient
    $deleteItems = $conn->prepare("
        DELETE pi FROM prescriptionitem pi
        JOIN prescription p ON pi.prescriptionID = p.prescriptionID
        WHERE p.patientID = ?
    ");
    $deleteItems->bind_param("i", $patientID);
    if (!$deleteItems->execute()) {
        throw new Exception("Error deleting prescription items.");
    }
    $deleteItems->close();

    // Delete prescriptions for this patient
    $deletePrescriptions = $conn->prepare("DELETE FROM prescription WHERE patientID = ?");
    $deletePrescriptions->bind_param("i", $patientID);
    if (!$deletePrescriptions->execute()) {
        throw new Exception("Error deleting prescriptions.");
    }
    $deletePrescriptions->close();

    // Delete patient record
    $deletePatient = $conn->prepare("DELETE FROM patient WHERE patientID = ?");
    $deletePatient->bind_param("i", $patientID);
    if (!$deletePatient->execute()) {
        throw new Exception("Error deleting patient.");
    }
    $deletePatient->close();

    // Commit deletion
    mysqli_commit($conn);

    echo "<script>
            alert('Patient and all related prescriptions deleted successfully!');
            window.location='patients.php';
          </script>";

} catch (Exception $e) {

    mysqli_rollback($conn);

    echo "<script>
            alert('Delete failed: {$e->getMessage()}');
            window.location='patients.php';
          </script>";
}
?>
