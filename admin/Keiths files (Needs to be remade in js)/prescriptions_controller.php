<?php
require_once "includes/db_connect.php";
require_once "includes/prescription_functions.php";
require_once "includes/prescription_table.php";

$search  = $_GET['search'] ?? '';
$edit_id = isset($_GET['edit_id']) ? intval($_GET['edit_id']) : null;

//delete
if (isset($_GET['delete_id'])) {
    deletePrescription($_GET['delete_id'], $conn);
    header("Location: prescriptions.php");
    exit;
}

//save
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_record'])) {
    savePrescription($_POST, $conn);
    header("Location: prescriptions.php");
    exit;
}

//query
$query = "SELECT * FROM prescription";
if ($search !== '') {
    $safe = $conn->real_escape_string($search);
    $query .= " WHERE status LIKE '%$safe%' OR issueDate LIKE '%$safe%' OR expirationDate LIKE '%$safe%'";
}
$result = $conn->query($query);

$title = "Prescriptions";
include "prescriptions.html";
