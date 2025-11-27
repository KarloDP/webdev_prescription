<?php
session_start();
require_once __DIR__ . '/../../../backend/includes/auth.php';
//include(__DIR__ . '/../includes/db_connect.php');

// Redirect if not logged in as patient
if (!isset($_SESSION['patientID'])) {
    header("Location: ../TestLoginPatient.php");
    exit;
}

$patientID   = (int) $_SESSION['patientID'];
$activePage  = 'medications';

// Fetch patient name (for header)
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

// ----------------------
// BEGIN PAGE CONTENT
// ----------------------
ob_start();
?>

<div class="medication-page">
  <h2>Original Prescriptions</h2>
  <p>These are the original prescriptions issued to <?= htmlspecialchars($patientName) ?>.</p>

  <div style="margin-bottom: 20px;">
    <a href="../medication/medication.php" class="btn-view"
       style="display:inline-block;padding:10px 15px;background:#1e3d2f;color:#fff;border-radius:4px;text-decoration:none;">
      ← Back to Medications
    </a>
  </div>

  <!-- JS will inject one table per prescription here -->
  <div id="prescription-groups"></div>
</div>

<script>
  window.currentPatient = {
      id: <?= (int)$patientID ?>,
      name: <?= json_encode($patientName) ?>
  };
</script>
<script src="prescription.js?v=1"></script>

<?php
// END PAGE CONTENT
$content = ob_get_clean();

// Render using standard patient layout
include __DIR__ . '/../patient_standard.php';
?>