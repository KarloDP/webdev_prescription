<?php
require_once "../includes/db_connect.php";
require_once "../includes/doctor_functions.php";
require_once "../includes/doctor_table.php";

$filter = $_GET['filter'] ?? 'active';
$search = $_GET['search'] ?? '';
$edit_id = isset($_GET['edit_id']) ? intval($_GET['edit_id']) : null;

//actions
if (isset($_GET['accept_id'])) acceptDoctor($_GET['accept_id'], $conn);
if (isset($_GET['delete_id'])) deleteDoctor($_GET['delete_id'], $_GET['filter'] ?? 'active', $conn);
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_doctor'])) updateDoctor($_POST, $conn);

//query doctors
$title = ($filter === 'pending') ? "Pending Doctor Requests" : "Active Doctors";
$query = ($filter === 'pending')
    ? "SELECT * FROM doctor WHERE status='pending'"
    : "SELECT * FROM doctor WHERE status='active'";

if ($search !== '') {
    $safe = $conn->real_escape_string($search);
    $query .= " AND (firstName LIKE '%$safe%' OR lastName LIKE '%$safe%' OR doctorID LIKE '%$safe%' 
                OR email LIKE '%$safe%' OR specialization LIKE '%$safe%' 
                OR licenseNumber LIKE '%$safe%' OR clinicAddress LIKE '%$safe%')";
}
$result = $conn->query($query);

include "admin_doctors.html";
