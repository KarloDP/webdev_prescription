<?php
$servername = "localhost";
$username = "root";
$password = "";
$database = "webdev_prescription";
$port = 3306; // ✅ Confirmed active from netstat

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
} else { 
    echo "Connected successfully!";
}
?>
