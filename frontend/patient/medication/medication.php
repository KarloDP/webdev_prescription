<?php
session_start();

require_once __DIR__ . '/../../../backend/includes/auth.php';
require_once __DIR__ . '/../../../backend/includes/db_connect.php';
require_login('/../../../login.php', ['patient']);

$patientID = (int) $_SESSION['patientID'];
$activePage = 'medications';

$patientName = $_SESSION['patient_name'] ?? 'Patient';
if (empty($_SESSION['patient_name'])) {
    $s = $conn->prepare("SELECT firstName, lastName FROM patient WHERE patientID = ? LIMIT 1");
    if ($s) {
        $s->bind_param("i", $patientID);
        $s->execute();
        $r = $s->get_result();
        if ($r && $r->num_rows === 1) {
            $row = $r->fetch_assoc();
            $patientName = trim($row['firstName'] . ' ' . $row['lastName']);
            $_SESSION['patient_name'] = $patientName;
        }
        $s->close();
    }
}

ob_start();
?>


<link rel="stylesheet" href="../../css/patient/patient_standard.css">

<div class="patient-dashboard">
  <h2>Medications for <?= htmlspecialchars($patientName) ?></h2>
  <div id="medications-root">
    <p>Loading medications...</p>
  </div>
  <div class="actions">
     <a href="../prescription/prescription.php" class="btn btn-primary">Back</a>
   </div>
</div>

<script>
  window.currentPatient = {
    id: <?= (int)$patientID ?>,
    name: <?= json_encode($patientName) ?>
  };
</script>

<script src="medication.js?v=<?= time(); ?>"></script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../patient_standard.php';
?>
