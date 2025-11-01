<?php
include 'includes/db_connect.php';

$medicationID = $_POST['medicationID'];

$sql = "DELETE FROM stock WHERE medicationID = '$medicationID'";
if ($conn->query($sql) === TRUE) {
    echo "Stock deleted successfully.";
} else {
    echo "Error: " . $conn->error;
}

$conn->close();
?>