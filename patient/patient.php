<?php
session_start();
include('../includes/auth.php');
include('../includes/db_connect.php');

if (!isset($_SESSION['patientID'])) {
  header("Location: ../login.php");
  exit;
}

$patientID = $_SESSION['patientID'];

// Fetch patient name
$patientName = "Patient";
$stmt = $conn->prepare("SELECT firstName, lastName FROM patient WHERE patientID = ?");
$stmt->bind_param("i", $patientID);
$stmt->execute();
$result = $stmt->get_result();
if ($result && $result->num_rows > 0) {
  $patient = $result->fetch_assoc();
  $patientName = $patient['firstName'] . ' ' . $patient['lastName'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Prescription History</title>
  <link rel="stylesheet" href="../admin/assets/css/role-patient.css">
  <style>
    .standard-history {
      border-collapse: collapse;
      width: 100%;
      margin-bottom: 30px;
    }
    .standard-history th, .standard-history td {
      border: 1px solid #ccc;
      padding: 8px;
      text-align: left;
    }
    .standard-history th {
      background-color: #f4f4f4;
    }
    .standard-history td {
      background-color: #fff;
    }
  </style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
  <h3>Welcome To MediSync Dashboard</h3>
  <div class="profile-section">
    <img src="../assets/img/profile.png" alt="Profile" class="profile-icon" />
    <p><?= strtoupper($patientName) ?> <span class="role-label">Patient History</span></p>
    <div id="dropdown-menu" class="dropdown-content">
      <a href="patient.php">My Profile</a>
      <a href="../logout.php">Logout</a>
    </div>
  </div>
  <ul>
    <li><a href="dashboard.php">Dashboard</a></li>
    <li><a href="patient.php" class="active">History</a></li>
    <li><a href="medication.php">Medications</a></li>
    <li><a href="pharmacies.php">Pharmacies</a></li>
  </ul>
</div>

<!-- Main Content -->
<div class="main-content">
  <h2>Prescription History</h2>
  <p>Here are your prescriptions listed row by row.</p>

  <?php
  $stmt = $conn->prepare("
    SELECT
      m.genericName AS medicine,
      p.prescriptionID,
      CONCAT('Dr ', d.firstName, ' ', d.lastName) AS doctorName,
      p.status,
      CONCAT(pi.dosage, ' ', pi.frequency, ' for ', pi.duration) AS qty,
      p.issueDate
    FROM prescription p
    JOIN medication m ON p.medicationID = m.medicationID
    JOIN doctor d ON p.doctorID = d.doctorID
    JOIN prescriptionItem pi ON p.prescriptionID = pi.prescriptionID
    WHERE p.patientID = ?
    ORDER BY p.issueDate DESC
  ");
  $stmt->bind_param("i", $patientID);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result && $result->num_rows > 0) {
    echo "<table class='standard-history'>";
    echo "<tr>
            <th>Medicine</th>
            <th>Prescription ID</th>
            <th>Doctor Name</th>
            <th>Status</th>
            <th>QTY</th>
            <th>Date Issued</th>
          </tr>";

    while ($row = $result->fetch_assoc()) {
      $rxID = 'D060203243454' . str_pad($row['prescriptionID'], 2, '0', STR_PAD_LEFT);
      echo "<tr>
              <td>{$row['medicine']}</td>
              <td>$rxID</td>
              <td>{$row['doctorName']}</td>
              <td>{$row['status']}</td>
              <td>{$row['qty']}</td>
              <td>" . date("F j, Y", strtotime($row['issueDate'])) . "</td>
            </tr>";
    }

    echo "</table>";
  } else {
    echo "<p>No prescription history found.</p>";
  }
  ?>
</div>

</body>
</html>
