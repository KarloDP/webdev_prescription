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

// NEW: Which prescription are we showing all medications for?
$prescriptionID = isset($_GET['prescriptionID'])
    ? (int) $_GET['prescriptionID']
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

// NEW: fetch prescription header info if prescriptionID is passed
$header = null;
if ($prescriptionID > 0) {
    $sql = "
        SELECT p.prescriptionID, p.issueDate, p.expirationDate, p.status,
               d.firstName AS docFirst, d.lastName AS docLast
        FROM prescription p
        JOIN doctor d ON p.doctorID = d.doctorID
        WHERE p.prescriptionID = ? AND p.patientID = ?
        LIMIT 1
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $prescriptionID, $patientID);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res && $res->num_rows > 0) {
        $header = $res->fetch_assoc();
    }
    $stmt->close();
}

// NEW: fetch all medications for a prescription
$medications = [];
if ($prescriptionID > 0) {
    $sql = "
        SELECT pi.prescriptionItemID, m.genericName, m.brandName, m.form, m.strength,
               pi.dosage, pi.frequency, pi.duration, pi.prescribed_amount,
               pi.refill_count, pi.refillInterval, pi.instructions
        FROM prescriptionitem pi
        JOIN medication m ON pi.medicationID = m.medicationID
        WHERE pi.prescriptionID = ?
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $prescriptionID);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $medications[] = $row;
    }
    $stmt->close();
}

// --- PAGE CONTENT BEGINS ---
ob_start();
?>

<div class="medication-page">
    <?php if ($header): ?>
        <h2>Prescription <?= 'RX-' . str_pad($header['prescriptionID'], 2, '0', STR_PAD_LEFT) ?></h2>
        <p>
            Doctor: <?= htmlspecialchars($header['docFirst'] . ' ' . $header['docLast']) ?><br>
            Issued: <?= htmlspecialchars($header['issueDate']) ?><br>
            Expires: <?= htmlspecialchars($header['expirationDate']) ?><br>
            Status: <?= htmlspecialchars($header['status']) ?><br>
            Patient: <?= htmlspecialchars($patientName) ?>
        </p>
    <?php endif; ?>

    <?php if ($prescriptionID > 0): ?>
        <h3>Medications</h3>
        <table class="table-base">
            <thead>
                <tr>
                    <th>Medicine</th><th>Brand</th><th>Form</th><th>Strength</th>
                    <th>Dosage</th><th>Frequency</th><th>Duration</th>
                    <th>Amount</th><th>Refills</th><th>Instructions</th>
                    <th>Refill Interval</th><th>History</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($medications): ?>
                    <?php foreach ($medications as $m): ?>
                        <tr>
                            <td><?= htmlspecialchars($m['genericName']) ?></td>
                            <td><?= htmlspecialchars($m['brandName']) ?></td>
                            <td><?= htmlspecialchars($m['form']) ?></td>
                            <td><?= htmlspecialchars($m['strength']) ?></td>
                            <td><?= htmlspecialchars($m['dosage']) ?></td>
                            <td><?= htmlspecialchars($m['frequency']) ?></td>
                            <td><?= htmlspecialchars($m['duration']) ?></td>
                            <td><?= htmlspecialchars($m['prescribed_amount']) ?></td>
                            <td><?= htmlspecialchars($m['refill_count']) ?></td>
                            <td><?= htmlspecialchars($m['instructions']) ?></td>
                            <td><?= htmlspecialchars($m['refillInterval']) ?></td>
                            <td>
                                <a href="prescription_medication.php?prescriptionItemID=<?= $m['prescriptionItemID'] ?>"
                                   class="btn-view">View History</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="12">No medications found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <?php if ($prescriptionItemID > 0): ?>
        <h3>Dispense History</h3>
        <div id="dispense-history-container">
            <p>Loading dispense history...</p>
        </div>
    <?php endif; ?>

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
    window.currentPrescriptionID = <?= json_encode($prescriptionID) ?>;
</script>

<script src="prescription_medication.js?v=1"></script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../patient_standard.php';
?>
