<?php
session_start();

// Load auth and database
require_once __DIR__ . '/../../../backend/includes/auth.php';
require_once __DIR__ . '/../../../backend/includes/db_connect.php';
require_login('/../../../login.php', ['patient']);

$patientID = $_SESSION['patientID'];
$activePage = 'pharmacies'; // highlight sidebar

// Get patient name
$patientName = "Patient";
$stmt = $conn->prepare("SELECT firstName, lastName FROM patient WHERE patientID = ?");
$stmt->bind_param("i", $patientID);
$stmt->execute();
$result = $stmt->get_result();
if ($result && $result->num_rows > 0) {
    $patient = $result->fetch_assoc();
    $patientName = $patient['firstName'] . ' ' . $patient['lastName'];
}

// Capture page content
ob_start();
?>

<!-- CSS files -->
<link rel="stylesheet" href="../../css/patient/patient_standard.css">
<link rel="stylesheet" href="<?php echo dirname($_SERVER['PHP_SELF']); ?>/../../css/patient/table.css">
<link rel="stylesheet" href="<?php echo dirname($_SERVER['PHP_SELF']); ?>/../../css/pharmacies_patient.css">

<!-- Page container -->
<div class="patient-dashboard">
  <div id="pharmacies-root">
    <p>Loading pharmacies...</p>
  </div>
</div>

<script>
  // Pass patient name to JS
  window.patientName = <?= json_encode($patientName); ?>;
</script>

<!-- JS file -->
<script src="pharmacies.js?v=<?= time(); ?>"></script>

<?php
// Render with layout
$content = ob_get_clean();
include __DIR__ . '/../patient_standard.php';
?>
