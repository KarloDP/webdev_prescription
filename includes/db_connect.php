<?php
$servername = "localhost";
$username = "root";
$password = "";
$database = "prescriptionWebapp"; // must match what you created in phpMyAdmin

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
} else { 
    echo "Connected successfully!";
}
?>
