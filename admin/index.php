<?php
require_once "../includes/db_connect.php";

// Counts
$doctors = $conn->query("SELECT COUNT(*) AS total FROM doctor WHERE status='active'")
                ->fetch_assoc()['total'];
$doctor_requests = $conn->query("SELECT COUNT(*) AS total FROM doctor WHERE status='pending'")
                        ->fetch_assoc()['total'];
$medicines = $conn->query("SELECT COUNT(*) AS total FROM medication")
                  ->fetch_assoc()['total'];
$prescriptions = $conn->query("SELECT COUNT(*) AS total FROM prescription")
                      ->fetch_assoc()['total'];
$hospitals = $conn->query("SELECT COUNT(*) AS total FROM pharmacy")
                  ->fetch_assoc()['total'];
$reports = $conn->query("SELECT COUNT(*) AS total FROM logs")
                ->fetch_assoc()['total'];
?>
<!DOCTYPE html>
<html>
<head>
  <title>Admin Dashboard</title>
</head>
<body>
  <h1>Integrative Medicine Dashboard</h1>

  <div class="card">
    <h3>Total Doctors</h3>
    <p><?= $doctors ?></p>
    <a href="users.php?filter=active" class="btn btn-primary">View Details >></a>
  </div>

  <div class="card">
    <h3>Total Doctor Requests</h3>
    <p><?= $doctor_requests ?></p>
    <a href="users.php?filter=pending" class="btn btn-primary">View Details >></a>
  </div>

  <div class="card">
    <h3>Total Medicines</h3>
    <p><?= $medicines ?></p>
    <a href="database.php?view=medicines" class="btn btn-primary">View Details >></a>
  </div>

  <div class="card">
    <h3>Total Prescriptions</h3>
    <p><?= $prescriptions ?></p>
    <a href="database.php?view=prescriptions" class="btn btn-primary">View Details >></a>
  </div>

  <div class="card">
    <h3>Total Hospitals</h3>
    <p><?= $hospitals ?></p>
    <a href="database.php?view=hospitals" class="btn btn-primary">View Details >></a>
  </div>

  <div class="card">
    <h3>Total System Reports</h3>
    <p><?= $reports ?></p>
    <a href="logs.php" class="btn btn-primary">View Details >></a>
  </div>
</body>
</html>
