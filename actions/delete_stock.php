<?php
include '../db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $medicineName = $_POST['medicine_name'];

    $sql = "DELETE FROM stock WHERE medicine_name = '$medicineName'";
    if ($conn->query($sql) === TRUE) {
        echo "Stock deleted successfully.";
    } else {
        echo "Error: " . $conn->error;
    }
}

$conn->close();
?>