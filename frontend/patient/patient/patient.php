<?php
session_start();
include(__DIR__ . '/../../../backend/includes/auth.php');
include(__DIR__ . '/../../../backend/includes/db_connect.php');

require_login('/../../../login.php', ['patient']);

$patientID = (int) $_SESSION['patientID'];
$activePage = 'history';

$patientName = "Patient";
$stmt = $conn->prepare("SELECT firstName, lastName FROM patient WHERE patientID = ?");
$stmt->bind_param("i", $patientID);
$stmt->execute();
$result = $stmt->get_result();
if ($result && $result->num_rows > 0) {
    $patient = $result->fetch_assoc();
    $patientName = $patient['firstName'] . ' ' . $patient['lastName'];
}

ob_start();
?>

<!-- CSS files -->
<link rel="stylesheet" href="../../css/patient/patient_standard.css">
<link rel="stylesheet" href="<?php echo dirname($_SERVER['PHP_SELF']); ?>/../../css/patient/table.css">
<link rel="stylesheet" href="<?php echo dirname($_SERVER['PHP_SELF']); ?>/../../css/patient/history_patient.css">

<div class="history-page">
  <h2>History</h2>
  <p>View Prescription History for <?= htmlspecialchars($patientName) ?></p>

  <div id="history-root">
    <p>Loading prescription history...</p>
  </div>
</div>

<script>
  window.currentPatient = {
    id: <?= $patientID ?>,
    name: <?= json_encode($patientName) ?>
  };
</script>

<script src="patient.js?v=<?= time(); ?>"></script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../patient_standard.php';
?>
