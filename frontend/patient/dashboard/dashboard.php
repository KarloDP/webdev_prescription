<?php
// frontend/patient/dashboard/dashboard.php

require_once __DIR__ . '/../../../backend/includes/auth.php';

// Only allow logged-in patients
require_login('/webdev_prescription/login.php', ['patient']);

// Sidebar active key for patient_standard.php
$activePage = 'dashboard';

// Name for greeting + top nav
$patientName = $_SESSION['patient_name']
    ?? ($_SESSION['user']['name'] ?? 'Patient');

// Capture page-specific content
ob_start();
?>
  <div id="patient-dashboard-root">
    <p>Loading dashboard...</p>
  </div>
  
  <script>
    // Make patient name available to dashboard.js
    window.patientName = <?= json_encode($patientName, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
  </script>

  <!-- Dashboard JS (loaded relative to this PHP file) -->
  <script src="dashboard.js"></script>
<?php
$content = ob_get_clean();

// Use the shared patient layout
include __DIR__ . '/../patient_standard.php';
?>