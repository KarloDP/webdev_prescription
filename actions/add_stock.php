<?php
include '../includes/db_connect.php';

$medicationID = $_POST['medicationID'];
$genericName = $_POST['generic_name'];
$brandName = $_POST['brand_name'];
$form = $_POST['form'];
$strength = $_POST['strength'];
$manufacturer = $_POST['manufacturer'];
$stock = $_POST['stock'];

$stmt = $conn->prepare("
    INSERT INTO medication (medicationID, genericName, brandName, form, strength, manufacturer, stock)
    VALUES (?, ?, ?, ?, ?, ?, ?)
");

$stmt->bind_param("isssssi", $medicationID, $genericName, $brandName, $form, $strength, $manufacturer, $stock);

if ($stmt->execute()) {
    echo "Medication stock added successfully!";
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>