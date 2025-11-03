<?php
session_start();
include('../includes/auth.php');
include('../includes/db_connect.php');

// Check if session is set
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

// Count active prescriptions
$active_count = 0;
$stmt = $conn->prepare("SELECT COUNT(*) AS total FROM prescription WHERE patientID = ? AND status = 'Active'");
$stmt->bind_param("i", $patientID);
$stmt->execute();
$result = $stmt->get_result();
if ($result) {
  $active_count = $result->fetch_assoc()['total'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Patient Dashboard</title>
  <link rel="stylesheet" href="../admin/assets/css/role-patient.css">
  <script>
    function toggleDropdown() {
      const menu = document.getElementById("dropdown-menu");
      menu.style.display = menu.style.display === "block" ? "none" : "block";
    }
    window.onclick = function(event) {
      if (!event.target.matches('.profile-icon')) {
        const dropdowns = document.getElementsByClassName("dropdown-content");
        for (let i = 0; i < dropdowns.length; i++) {
          dropdowns[i].style.display = "none";
        }
      }
    }
  </script>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
  <h3>Welcome To MediSync Dashboard</h3>
  <div class="profile-section">
    <img src="../assets/img/profile.png" alt="Profile" class="profile-icon" onclick="toggleDropdown()" />
    <div id="dropdown-menu" class="dropdown-content">
      <a href="patient.php">My Profile</a>
      <a href="../logout.php">Logout</a>
    </div>
    <p><?= strtoupper($patientName) ?> <span class="role-label">Patient</span></p>
  </div>
  <ul>
    <li><a href="dashboard.php" class="active">Dashboard</a></li>
    <li><a href="patient.php">History</a></li>
    <li><a href="medications.php">Medications</a></li>
    <li><a href="pharmacies.php">Pharmacies</a></li>
  </ul>
</div>

<!-- Main Content -->
<div class="main-content">
  <h2>Welcome <?= strtoupper($patientName) ?> !!</h2>
  <p>View prescriptions.</p>

  <div class="summary-box">
    <h3><?= $active_count ?> Active Prescriptions</h3>
    <a href="prescriptions.php" class="btn-view">View Details</a>
  </div>

  <div class="prescription-grid">
    <?php
    $stmt = $conn->prepare("
      SELECT p.prescriptionID, m.genericName, p.issueDate
      FROM prescription p
      JOIN medication m ON p.medicationID = m.medicationID
      WHERE p.patientID = ?
      ORDER BY p.issueDate DESC
      LIMIT 4
    ");
    $stmt->bind_param("i", $patientID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
      while ($row = $result->fetch_assoc()) {
        echo "<div class='prescription-card'>
                <h4>{$row['genericName']}</h4>
                <p>" . date("F j", strtotime($row['issueDate'])) . "<br>First time use</p>
                <p>Prescribing doctor unavailable</p>
                <a href='prescription_view.php?id={$row['prescriptionID']}'>Medicine Details</a>
              </div>";
      }
    } else {
      echo "<p>No recent prescriptions found.</p>";
    }
    ?>
  </div>

  <a href="download_prescriptions.php" class="btn-download">Download Prescription</a>
</div>

</body>
</html>
