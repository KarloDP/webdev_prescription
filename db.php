<?php
$servername = "127.0.0.1"; //try localhost if this causes an error
$username = "root";
$password = "";
$database = "prescriptionWebapp"; // must match what you created in phpMyAdmin

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
// echo "✅ Connected successfully!";
?>
