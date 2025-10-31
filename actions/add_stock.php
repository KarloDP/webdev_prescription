<?php
include '../db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $medicineName = $_POST['medicine_name'];
    $dosage = $_POST['dosage'];
    $quantity = $_POST['quantity'];

    $sql = "INSERT INTO stock (medicine_name, dosage, quantity) VALUES ('$medicineName', '$dosage', $quantity)";
    if ($conn->query($sql) === TRUE) {
        echo "New stock added successfully.";
    } else {
        echo "Error: " . $conn->error;
    }
}

$conn->close();
?>