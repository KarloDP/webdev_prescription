<?php
include '../includes/db_connect.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$required = [
    'prescriptionItemID', 'pharmacyID', 'dispenseID', 
    'quantityDispensed', 'dateDispensed', 
    'pharmacistName', 'status', 'nextAvailableDates'
];

foreach ($required as $field) {
    if (!isset($_POST[$field]) || trim($_POST[$field]) === '') {
        http_response_code(400);
        die("Error: Missing field '$field'");
    }
}

$prescriptionItemID      = (int) $_POST['prescriptionItemID'];
$pharmacyID          = (int) $_POST['pharmacyID'];
$dispenseID          = (int) $_POST['dispenseID'];
$quantityDispensed   = (int) $_POST['quantityDispensed'];
$dateDispensed       = trim($_POST['dateDispensed']);
$pharmacistName      = trim($_POST['pharmacistName']);
$status              = trim($_POST['status']);
$nextAvailableDates  = trim($_POST['nextAvailableDates']);

if ($quantityDispensed <= 0) {
    die("Error: Quantity dispensed must be positive");
}

$stmt = $conn->prepare("
    INSERT INTO dispenseRecord
    (prescriptionItemID, pharmacyID, dispenseID, quantityDispensed, dateDispensed, pharmacistName, status, nextAvailableDates) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
");

if (!$stmt) {
    die("Database error: " . $conn->error);
}

$stmt->bind_param(
    "iiiissss",
    $prescriptionItemID, $pharmacyID, $dispenseID, 
    $quantityDispensed, $dateDispensed, 
    $pharmacistName, $status, $nextAvailableDates
);

if ($stmt->execute()) {
    echo "New dispense record added successfully!";
} else {
    echo "Database error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>