<?php
// frontend/patient/profile/profile.php

ob_start();
session_start();

include(__DIR__ . '/../../../backend/includes/auth.php');
include(__DIR__ . '/../../../backend/includes/db_connect.php');
require_login('/webdev_prescription/login.php', ['patient']);

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
  <div class="profile-card">
    <?php if ($patient): ?>
      <p><strong>Name:</strong> <?= htmlspecialchars($patient['firstName'] . ' ' . $patient['lastName']) ?></p>
      <p><strong>Email:</strong> <?= htmlspecialchars($patient['email']) ?></p>
      <p><strong>Patient ID:</strong> <?= htmlspecialchars($patient['patientID']) ?></p>
      <p><strong>Birthdate:</strong> <?= htmlspecialchars($patient['birthDate']) ?></p>
      <p><strong>Contact Number:</strong> <?= htmlspecialchars($patient['contactNumber']) ?></p>
      <p><strong>Address:</strong> <?= htmlspecialchars($patient['address']) ?></p>
    <?php else: ?>
      <p>No patient data found.</p>
    <?php endif; ?>
  </div>

  <div class="profile-actions">
    <a href="edit_profile.php" class="btn btn-primary">Edit Profile</a>
  </div>
</div>

<script src="profile.js"></script>
<?php
$content = ob_get_clean();
$activePage = 'profile';
include_once __DIR__ . '/../patient_standard.php';
