<?php
include '../db.php';

$medicationID = $_POST['medicine_name'];
$genericName = $_POST['generic_name'];
$form = $_POST['form'];
$strength = $_POST['strength'];
$manufacturer = $_POST['manufacturer'];

$sql = "INSERT INTO stock (medicine_name, generic_name, form, strength, manufacturer) VALUES ('$medicationID', '$genericName', '$form', '$strength', '$manufacturer')";
if ($conn->query($sql) === TRUE) {
    echo "New stock added successfully.";
} else {
    echo "Error: " . $conn->error;
}

$conn->close();
?>