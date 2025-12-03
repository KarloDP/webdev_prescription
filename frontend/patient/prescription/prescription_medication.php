<?php
// frontend/patient/prescription/prescription_medication.php

session_start();

// Auth + DB (same style as your other new pages)
require_once __DIR__ . '/../../../backend/includes/auth.php';
require_once __DIR__ . '/../../../backend/includes/db_connect.php';

require_login('/webdev_prescription/login.php', ['patient']);

// Must be logged in as patient
if (!isset($_SESSION['patientID'])) {
    header("Location: ../TestLoginPatient.php");
    exit;
}

$patientID = (int) $_SESSION['patientID'];
$activePage = 'prescriptions'; // sidebar highlight

// Which medication (prescription item) are we showing history for?
$prescriptionItemID = isset($_GET['prescriptionItemID'])
    ? (int) $_GET['prescriptionItemID']
    : 0;

// Fetch patient name
$patientName = 'Patient';
$stmt = $conn->prepare("SELECT firstName, lastName FROM patient WHERE patientID = ?");
$stmt->bind_param("i", $patientID);
$stmt->execute();
$res = $stmt->get_result();
if ($res && $res->num_rows > 0) {
    $row = $res->fetch_assoc();
    $patientName = trim($row['firstName'] . ' ' . $row['lastName']);
}
$stmt->close();

// Optional: fetch basic info about this prescription item + medication
$medLabel = '';
$rxLabel  = '';
if ($prescriptionItemID > 0) {
    $sql = "
        SELECT
            p.prescriptionID,
            m.genericName,
            m.brandName
        FROM prescriptionitem pi
        JOIN prescription p ON pi.prescriptionID = p.prescriptionID
        JOIN medication   m ON pi.medicationID   = m.medicationID
        WHERE pi.prescriptionItemID = ? AND p.patientID = ?
        LIMIT 1
    ";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("ii", $prescriptionItemID, $patientID);
        $stmt->execute();
        $infoRes = $stmt->get_result();
        if ($infoRes && $infoRes->num_rows > 0) {
            $info = $infoRes->fetch_assoc();
            $rxLabel = 'RX-' . str_pad($info['prescriptionID'], 2, '0', STR_PAD_LEFT);

            $generic = $info['genericName'] ?? '';
            $brand   = $info['brandName'] ?? '';
            if ($brand && $generic) {
                $medLabel = "{$brand} ({$generic})";
            } else {
                $medLabel = $brand ?: $generic;
            }
        }
        $stmt->close();
    }
}

// --- PAGE CONTENT BEGINS ---
ob_start();
?>

<div class="medication-page">
    <h2>Dispense History</h2>

    <p>
        Dispense history for
        <strong><?= htmlspecialchars($medLabel ?: 'Selected Medication', ENT_QUOTES, 'UTF-8') ?></strong>
        <?php if ($rxLabel): ?>
            in prescription <strong><?= htmlspecialchars($rxLabel, ENT_QUOTES, 'UTF-8') ?></strong>
        <?php endif; ?>
        for <?= htmlspecialchars($patientName, ENT_QUOTES, 'UTF-8'); ?>.
    </p>

    <!-- Container where JS will render the history table -->
    <div id="dispense-history-container">
        <p>Loading dispense history...</p>
    </div>

    <!-- Back Button -->
    <div style="margin-top:20px;">
        <a href="prescription.php" class="btn-view"
           style="display:inline-block;padding:10px 15px;background:#6c757d;color:#fff;border-radius:4px;text-decoration:none;">
            ← Back to Prescriptions
        </a>
    </div>
</div>

<script>
    // Make context available to JS
    window.currentPatient = {
        id: <?= json_encode($patientID) ?>,
        name: <?= json_encode($patientName) ?>
    };
    window.currentPrescriptionItemID = <?= json_encode($prescriptionItemID) ?>;
</script>

<script src="prescription_medication.js?v=1"></script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../patient_standard.php';
?>