<?php
require_once "includes/db_connect.php";
require_once "includes/pharmacy_functions.php";
require_once "includes/pharmacy_table.php";

$search   = $_GET['search'] ?? '';
$add_mode = isset($_GET['add']);
$edit_id  = isset($_GET['edit_id']) ? intval($_GET['edit_id']) : null;

//delete
if (isset($_GET['delete_id'])) {
    deletePharmacy($_GET['delete_id'], $conn);
    header("Location: pharmacies.php");
    exit;
}

//save
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_record'])) {
    savePharmacy($_POST, $conn);
    header("Location: pharmacies.php");
    exit;
}

//query
$query = "SELECT * FROM pharmacy";
if ($search !== '') {
    $safe = $conn->real_escape_string($search);
    $query .= " WHERE name LIKE '%$safe%' OR address LIKE '%$safe%' OR email LIKE '%$safe%'";
}
$result = $conn->query($query);

$title = "Pharmacies";
include "pharmacies.html";
