<?php
session_start();
require_once __DIR__ . '/../../../backend/includes/auth.php';
//include(__DIR__ . '/../includes/db_connect.php');

if (!isset($_SESSION['patientID'])) {
    header("Location: ../TestLoginPatient.php");
    exit;
}

$patientID   = (int)$_SESSION['patientID'];
$activePage  = 'prescription_medication';
$patientName = 'Patient';

// Fetch patient name (keep this in PHP)
$stmt = $conn->prepare("SELECT firstName, lastName FROM patient WHERE patientID = ?");
$stmt->bind_param("i", $patientID);
$stmt->execute();
$result = $stmt->get_result();
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $patientName = $row['firstName'] . ' ' . $row['lastName'];
}
$stmt->close();

// --- PAGE CONTENT BEGINS ---
ob_start();
?>

<link rel="stylesheet" href="../assets/css/table.css">
<link rel="stylesheet" href="../assets/css/medication_patient.css">

<div class="medication-page">
    <h2>Grouped Prescriptions</h2>
    <p>Medications per prescription for <?= htmlspecialchars($patientName) ?>.</p>

    <!-- Table Container (JS will render actual rows) -->
    <div id="prescription-groups">  
        <p>Loading prescriptions...</p>
    </div>

    <!-- Back Button -->
    <div style="margin-top:20px;">
        <a href="medication.php" class="btn-view"
           style="display:inline-block;padding:10px 15px;background:#6c757d;color:#fff;border-radius:4px;text-decoration:none;">
            ← Back
        </a>
    </div>
</div>

<script>
    window.currentPatient = {
        id: <?= json_encode($patientID) ?>,
        name: <?= json_encode($patientName) ?>
    };
</script>

<script src="prescription_medication.js"></script>  <!-- 👈 NEW JS FILE -->

<?php
$content = ob_get_clean();
include __DIR__ . '/../patient_standard.php';
?>