<?php
// view_patient_prescription.php

session_start();
include('../includes/db_connect.php');

$activePage = 'prescriptions';

// Get prescription ID
$prescriptionID = intval($_GET['id'] ?? 0);
if ($prescriptionID <= 0) {
    $content = "<div class='card'>Invalid prescription ID.</div>";
    include('doctor_standard.php');
    exit;
}
//backend\DISTRIBUTE_TO_APPROPRIATE_FILES\doctor\view_patient_prescription.php
// Fetch prescription header
$sql = "
    SELECT p.*, 
           pat.firstName AS patientFirst, pat.lastName AS patientLast,
           d.firstName AS doctorFirst, d.lastName AS doctorLast
    FROM prescription p
    LEFT JOIN patient pat ON pat.patientID = p.patientID
    LEFT JOIN doctor d ON d.doctorID = p.doctorID
    WHERE p.prescriptionID = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $prescriptionID);
$stmt->execute();
$pres = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$pres) {
    $content = "<div class='card'>Prescription not found.</div>";
    include('doctor_standard.php');
    exit;
}

// Fetch all medications inside this prescription
$itemSQL = "
    SELECT pi.*, m.genericName, m.brandName
    FROM prescriptionitem pi
    LEFT JOIN medication m ON m.medicationID = pi.medicationID
    WHERE pi.prescriptionID = ?
";
$items = $conn->prepare($itemSQL);
$items->bind_param("i", $prescriptionID);
$items->execute();
$medList = $items->get_result();
$items->close();


// ───────────────────────────────────────────
// BUILD PAGE CONTENT
// ───────────────────────────────────────────
ob_start();
?>

    <div class="card">
        <h2>Prescription #<?= htmlspecialchars($prescriptionID) ?></h2>

        <p><strong>Patient:</strong>
            <?= htmlspecialchars($pres['patientFirst'] . " " . $pres['patientLast']) ?>
        </p>

        <p><strong>Doctor:</strong>
            <?= htmlspecialchars($pres['doctorFirst'] . " " . $pres['doctorLast']) ?>
        </p>

        <p><strong>Issue Date:</strong> <?= htmlspecialchars($pres['issueDate']) ?></p>
        <p><strong>Expiration Date:</strong> <?= htmlspecialchars($pres['expirationDate']) ?></p>

        <p><strong>Status:</strong> <?= htmlspecialchars($pres['status']) ?></p>

        <p><strong>Refill Interval:</strong>
            <?= htmlspecialchars($pres['refillInterval']) ?>
        </p>
    </div>

    <div class="card">
        <h3>Medications</h3>

        <table>
            <thead>
            <tr>
                <th>Medication</th>
                <th>Dosage</th>
                <th>Frequency</th>
                <th>Duration</th>
                <th>Refill Count</th>
                <th>Instructions</th>
            </tr>
            </thead>

            <tbody>
            <?php while ($m = $medList->fetch_assoc()): ?>
                <tr>
                    <td>
                        <?= htmlspecialchars($m['genericName']) ?> —
                        <?= htmlspecialchars($m['brandName']) ?>
                    </td>

                    <td><?= htmlspecialchars($m['dosage']) ?></td>
                    <td><?= htmlspecialchars($m['frequency']) ?></td>
                    <td><?= htmlspecialchars($m['duration']) ?></td>
                    <td><?= intval($m['refill_count']) ?></td>

                    <td><?= nl2br(htmlspecialchars($m['instructions'])) ?></td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>

        <br>

        <a href="edit_prescription.php?id=<?= $prescriptionID ?>" class="btn" style="background:#007bff;">
            Edit Prescription
        </a>

        <a href="view_prescription.php" class="btn" style="background:#6c757d;">
            Back to Prescription List
        </a>
    </div>

<?php
$content = ob_get_clean();
include('doctor_standard.php');
?>