<?php
// frontend/patient/profile/profile.php

ob_start();
session_start();

include(__DIR__ . '/../../../backend/includes/auth.php');
include(__DIR__ . '/../../../backend/includes/db_connect.php');
require_login('/WebDev_Prescription/login.php', ['patient']);

$patientID = $_SESSION['patientID'];

// Fetch patient details from database
$stmt = $conn->prepare("SELECT firstName, lastName, email, patientID, birthDate, contactNumber, address
                        FROM patient WHERE patientID = ?");
$stmt->bind_param("i", $patientID);
$stmt->execute();
$result = $stmt->get_result();
$patient = $result->fetch_assoc();
?>

<div id="profile-root" class="profile-page">
  <h1>Patient Profile</h1>

  <?php if ($patient): ?>
    <div class="profile-card">
      <p><strong>Name:</strong> <?= htmlspecialchars($patient['firstName'] . ' ' . $patient['lastName']) ?></p>
      <p><strong>Email:</strong> <?= htmlspecialchars($patient['email']) ?></p>
      <p><strong>Patient ID:</strong> <?= htmlspecialchars($patient['patientID']) ?></p>
      <p><strong>Birthdate:</strong> <?= htmlspecialchars($patient['birthDate']) ?></p>
      <p><strong>Contact Number:</strong> <?= htmlspecialchars($patient['contactNumber']) ?></p>
      <p><strong>Address:</strong> <?= htmlspecialchars($patient['address']) ?></p>
    </div>
  <?php else: ?>
    <div class="profile-card">
      <p>No patient data found.</p>
    </div>
  <?php endif; ?>

  <div class="profile-actions">
    <a href="edit_profile.php" class="btn">Edit Profile</a>
  </div>
</div>

<link rel="stylesheet" href="/frontend/css/profile.css">
<script src="profile.js"></script>

<?php
$content = ob_get_clean();
$activePage = 'profile';
include_once __DIR__ . '/../patient_standard.php';
?>
