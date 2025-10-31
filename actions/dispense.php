<?php
include '../db.php';

$prescriptionID = $_POST['prescriptionID'];
$pharmacyID = $_POST['pharmacyID'];
$dispenseID = $_POST['dispenseID'];
$quantityDispensed = $_POST['quantityDispensed'];
$dateDispensed = $_POST['dateDispensed'];
$pharmacistName = $_POST['pharmacistName'];
$status = $_POST['status'];
$nextAvailableDates = $_POST['nextAvailableDates'];

$sql = "INSERT INTO dispenseRecord (prescriptionID, pharmacyID, dispenseID, quantityDispensed, dateDispensed, pharmacistName, status, nextAvailableDates) VALUES ('$prescriptionID', '$pharmacyID', '$dispenseID', '$quantityDispensed', '$dateDispensed', '$pharmacistName', '$status', '$nextAvailableDates')";
if ($conn->query($sql) === TRUE) {
    echo "New dispense record added successfully.";
} else {
    echo "Error: " . $conn->error;
}

$conn->close();
?>