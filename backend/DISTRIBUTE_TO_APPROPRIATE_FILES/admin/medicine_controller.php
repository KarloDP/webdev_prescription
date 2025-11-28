<?php
require_once "includes/db_connect.php";
require_once "includes/medicine_functions.php";
require_once "includes/medicine_table.php";

$search   = $_GET['search'] ?? '';
$add_mode = isset($_GET['add']);
$edit_id  = isset($_GET['edit_id']) ? intval($_GET['edit_id']) : null;

//delete
if (isset($_GET['delete_id'])) {
    deleteMedicine($_GET['delete_id'], $conn);
    header("Location: medicines.php");
    exit;
}

//save
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_record'])) {
    saveMedicine($_POST, $conn);
    header("Location: medicines.php");
    exit;
}

//query
$query = "SELECT * FROM medication";
if ($search !== '') {
    $safe = $conn->real_escape_string($search);
    $query .= " WHERE genericName LIKE '%$safe%' OR brandName LIKE '%$safe%' OR manufacturer LIKE '%$safe%'";
}
$result = $conn->query($query);

$title = "Medicines";
include "medicines.html";
