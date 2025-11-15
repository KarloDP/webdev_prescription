<?php
require_once "../includes/db_connect.php";
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Totals
$doctors_active   = $conn->query("SELECT COUNT(*) AS total FROM doctor WHERE status='active'")->fetch_assoc()['total'];
$doctors_pending  = $conn->query("SELECT COUNT(*) AS total FROM doctor WHERE status='pending'")->fetch_assoc()['total'];
$medicines        = $conn->query("SELECT COUNT(*) AS total FROM medication")->fetch_assoc()['total'];
$prescriptions    = $conn->query("SELECT COUNT(*) AS total FROM prescription")->fetch_assoc()['total'];
$pharmacys        = $conn->query("SELECT COUNT(*) AS total FROM pharmacy")->fetch_assoc()['total'];
?>
<!DOCTYPE html>
<html>
<head>
  <title>Admin Dashboard</title>
  <style>
    body { font-family: Arial, sans-serif; margin:20px; }
    h1 { margin-bottom:20px; }
    .dashboard { display:flex; flex-wrap:wrap; gap:20px; }
    .card {
      flex:1 1 200px;
      background:#f9f9f9;
      border:1px solid #ddd;
      border-radius:6px;
      padding:20px;
      text-align:center;
      box-shadow:0 2px 4px rgba(0,0,0,0.1);
    }
    .card h3 { margin:0; font-size:18px; color:#333; }
    .card p { font-size:24px; margin:10px 0 0; font-weight:bold; }
    a { text-decoration:none; color:inherit; display:block; margin-top:8px; font-size:14px; }
  </style>
</head>
<body>
  <h1>Welcome Admin Dashboard</h1>
  <div class="dashboard">
    <div class="card">
      <h3>Active Doctors</h3>
      <p><?= $doctors_active ?></p>
      <a href="users.php?filter=active">View Active Doctors</a>
    </div>
    <div class="card">
      <h3>Pending Doctors</h3>
      <p><?= $doctors_pending ?></p>
      <a href="users.php?filter=pending">View Pending Requests</a>
    </div>
    <div class="card">
      <h3>Total Medicines</h3>
      <p><?= $medicines ?></p>
      <a href="database.php?view=medicines">View Medicines</a>
    </div>
    <div class="card">
      <h3>Total Prescriptions</h3>
      <p><?= $prescriptions ?></p>
      <a href="database.php?view=prescriptions">View Prescriptions</a>
    </div>
    <div class="card">
      <h3>Total Pharmacies</h3>
      <p><?= $pharmacys ?></p>
      <a href="database.php?view=pharmacys">View Pharmacies</a>
    </div>
  </div>
</body>
</html>
