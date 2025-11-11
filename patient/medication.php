<?php
session_start();
include('../includes/auth.php');
include('../includes/db_connect.php');

// Check if user is logged in
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
  <title>View Prescriptions</title>
  <link rel="stylesheet" href="../admin/assets/css/role-patient.css">
  <style>
    .prescription-table {
      border-collapse: collapse;
      width: 100%;
      margin-bottom: 30px;
    }
    .prescription-table th, .prescription-table td {
      border: 1px solid #ccc;
      padding: 8px;
      text-align: left;
    }
    .prescription-table th {
      background-color: #f4f4f4;
    }
    .prescription-table td {
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
    <p><?= strtoupper($patientName) ?> <span class="role-label">Parent</span></p>
    <div id="dropdown-menu" class="dropdown-content">
      <a href="dashboard.php">Dashboard</a>
      <a href="http://localhost/WebDev_Prescription/testlogout.php">Logout</a>
    </div>
  </div>
  <ul>
    <li><a href="dashboard.php">Dashboard</a></li>
    <li><a href="patient.php">History </a></li>
    <li><a href="medication.php" class="active">Medications</a></li>
    <li><a href="pharmacies.php">Pharmacies</a></li>
  </ul>
</div>

<!-- Main Content -->
<div class="main-content">
  <h2>View Prescriptions</h2>
  <p>Prescription history for <?= $patientName ?>.</p>

  <?php
  $stmt = $conn->prepare("
    SELECT
      m.genericName AS medicine,
      m.brandName,
      CONCAT('Dr ', d.firstName, ' ', d.lastName) AS doctorName,
      CONCAT(pi.dosage, ' ', pi.frequency, ' for ', pi.duration) AS prescribedAmount,
      COALESCE(SUM(dr.quantityDispensed), 0) AS totalBought,
      p.issueDate
    FROM prescription p
    JOIN medication m ON p.medicationID = m.medicationID
    JOIN doctor d ON p.doctorID = d.doctorID
    JOIN prescriptionItem pi ON p.prescriptionID = pi.prescriptionID
    LEFT JOIN dispenseRecord dr ON pi.prescriptionItemID = dr.prescriptionItemID
    WHERE p.patientID = ?
    GROUP BY p.prescriptionID
    ORDER BY p.issueDate DESC
  ");
  $stmt->bind_param("i", $patientID);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result && $result->num_rows > 0) {
    echo "<table class='prescription-table'>";
    echo "<tr>
            <th>Medicine</th>
            <th>Medication Brand</th>
            <th>Doctor Name</th>
            <th>Prescribed Amount</th>
            <th>Total Amount Bought</th>
            <th>Date Issued</th>
          </tr>";

    while ($row = $result->fetch_assoc()) {
      echo "<tr>
              <td>{$row['medicine']}</td>
              <td>{$row['brandName']}</td>
              <td>{$row['doctorName']}</td>
              <td>{$row['prescribedAmount']}</td>
              <td>{$row['totalBought']} pcs</td>
              <td>" . date("F j, Y", strtotime($row['issueDate'])) . "</td>
            </tr>";
    }

    echo "</table>";
  } else {
    echo "<p>No prescriptions found.</p>";
  }
  ?>
</div>

</body>
</html>
