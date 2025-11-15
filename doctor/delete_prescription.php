<?php
include('../includes/db_connect.php');

// Validate prescription ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<script>alert('Invalid prescription ID'); window.location='view_prescription.php';</script>";
    exit;
}

$prescriptionID = (int)$_GET['id'];

// STEP 1 — Get patientID before deleting anything
$getPatient = $conn->prepare("SELECT patientID FROM prescription WHERE prescriptionID = ?");
$getPatient->bind_param("i", $prescriptionID);
$getPatient->execute();
$result = $getPatient->get_result();
$presData = $result->fetch_assoc();
$getPatient->close();

if (!$presData) {
    echo "<script>alert('Prescription not found'); window.location='view_prescription.php';</script>";
    exit;
}

$patientID = $presData['patientID'];

// Begin transaction
mysqli_begin_transaction($conn);

try {

    // STEP 2 — Delete prescription items
    $deleteItems = $conn->prepare("DELETE FROM prescriptionitem WHERE prescriptionID = ?");
    $deleteItems->bind_param("i", $prescriptionID);
    if (!$deleteItems->execute()) {
        throw new Exception("Error deleting prescription items");
    }
    $deleteItems->close();

    // STEP 3 — Delete prescription
    $deletePres = $conn->prepare("DELETE FROM prescription WHERE prescriptionID = ?");
    $deletePres->bind_param("i", $prescriptionID);
    if (!$deletePres->execute()) {
        throw new Exception("Error deleting prescription");
    }
    $deletePres->close();

    mysqli_commit($conn);

    // STEP 4 — Correct redirect depending where delete was pressed
    if (isset($_GET['from']) && $_GET['from'] === "patient") {
        echo "<script>
                alert('Prescription deleted successfully!');
                window.location='view_patient_prescription.php?id=$patientID';
              </script>";
    } else {
        echo "<script>
                alert('Prescription deleted successfully!');
                window.location='view_prescription.php';
              </script>";
    }

} catch (Exception $e) {

    mysqli_rollback($conn);

    echo "<script>
            alert('Delete failed: {$e->getMessage()}');
            window.history.back();
          </script>";
}
?>
