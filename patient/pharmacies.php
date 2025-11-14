<?php
session_start();
include('../includes/auth.php');
include('../includes/db_connect.php');
//include 'patient_standard.php';
if (!isset($_SESSION['patientID'])) {
  header("Location: ../TestLoginPatient.php");
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
  <title>View Pharmacies</title>
  <link rel="stylesheet" href="../admin/assets/css/role-patient.css">
  <style>
    .pharmacy-table {
      border-collapse: collapse;
      width: 100%;
      margin-bottom: 30px;
    }
    .pharmacy-table th, .pharmacy-table td {
      border: 1px solid #ccc;
      padding: 8px;
      text-align: left;
    }
    .pharmacy-table th {
      background-color: #f4f4f4;
    }
    .pharmacy-table td {
      background-color: #fff;
    }
  </style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
  <h3>Welcome To MediSync</h3>
  <div class="profile-section">
    <img src="../assets/img/profile.png" alt="Profile" class="profile-icon" />
    <p><?= strtoupper($patientName) ?> <span class="role-label"></span></p>
    <div id="dropdown-menu" class="dropdown-content">
    </div>
  </div>
  <ul>
    <li><a href="dashboard.php">Dashboard</a></li>
    <li><a href="patient.php">History</a></li>
    <li><a href="medication.php">Medications</a></li>
    <li><a href="pharmacies.php" class="active">Pharmacies</a></li>
  </ul>
</div>

<!-- Main Content -->
<div class="main-content">
  <h2>Pharmacy</h2>
  <p>View Pharmacies</p>

  <?php
  $stmt = $conn->prepare("
    SELECT name, contactNumber, address, clinicAddress
    FROM pharmacy
    ORDER BY name ASC
  ");
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result && $result->num_rows > 0) {
    echo "<table class='pharmacy-table'>";
    echo "<tr>
            <th>Pharmacy</th>
            <th>Contact Info</th>
            <th>Address</th>
            <th>Operating Hours</th>
          </tr>";

    while ($row = $result->fetch_assoc()) {
      echo "<tr>
              <td>{$row['name']}</td>
              <td>{$row['contactNumber']}</td>
              <td>{$row['address']}</td>
              <td>" . date("F j, Y", strtotime("2001-09-08")) . "</td>
            </tr>";
    }

    echo "</table>";
  } else {
    echo "<p>No pharmacies found.</p>";
  }
  ?>
</div>

</body>
</html>
