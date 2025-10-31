<?php
include '../db.php';

if ($_SERVER["REQUEST_METHOD"] == "DELETE") {
    $medicationID = $_POST['medicationID'];

    $sql = "DELETE FROM stock WHERE medicationID = '$medicationID'";
    if ($conn->query($sql) === TRUE) {
        echo "Stock deleted successfully.";
    } else {
        echo "Error: " . $conn->error;
    }
}

$conn->close();
?>