<?php
// view_patient_prescription.php
include('../includes/db_connect.php');
$activePage = 'patients';

$patientID = intval($_GET['id'] ?? 0);
if ($patientID <= 0) {
    header("Location: patients.php");
    exit;
}

/* ---------------- GET PATIENT ---------------- */
$stmt = $conn->prepare("SELECT * FROM patient WHERE patientID = ?");
$stmt->bind_param("i", $patientID);
$stmt->execute();
$patient = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$patient) {
    header("Location: patients.php");
    exit;
}

/* ---------------- GET PRESCRIPTIONS ---------------- */
$stmt = $conn->prepare("
    SELECT prescriptionID, issueDate, expirationDate, refillInterval, status
    FROM prescription
    WHERE patientID = ?
    ORDER BY issueDate DESC
");
$stmt->bind_param("i", $patientID);
$stmt->execute();
$presList = $stmt->get_result();
$stmt->close();

ob_start();
?>

<!-- PATIENT HEADER -->
<div class="card">
    <h2>Prescriptions for <?= htmlspecialchars($patient['firstName'] . ' ' . $patient['lastName']) ?></h2>
    <a class="btn" href="patients.php">← Back to Patient List</a>
    <!-- pass patient id to add_prescription so page can auto-select -->
    <a class="btn" href="add_prescription.php?id=<?= (int)$patientID ?>">+ Add RX for this patient</a>
</div>

<!-- MEDICAL SUMMARY -->
<div class="card">
    <h3>Medical Summary</h3>

    <p><strong>Health Condition:</strong><br>
        <?= nl2br(htmlspecialchars($patient['healthCondition'] ?? 'N/A')) ?></p>

    <p><strong>Allergies:</strong><br>
        <?= nl2br(htmlspecialchars($patient['allergies'] ?? 'N/A')) ?></p>

    <p><strong>Current Medication:</strong><br>
        <?= nl2br(htmlspecialchars($patient['currentMedication'] ?? 'N/A')) ?></p>

    <p><strong>Known Diseases:</strong><br>
        <?= nl2br(htmlspecialchars($patient['knownDiseases'] ?? 'N/A')) ?></p>
</div>

<!-- PRESCRIPTION LIST -->
<div class="card">
    <?php if ($presList->num_rows > 0): ?>
        <table>
            <thead>
            <tr>
                <th>ID</th><th>Medications</th><th>Issued</th><th>Expires</th><th>Refill Interval</th><th>Status</th><th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php while ($pres = $presList->fetch_assoc()): ?>
                <tr>
                    <td><?= (int)$pres['prescriptionID'] ?></td>

                    <!-- MEDICATION LIST PER PRESCRIPTION -->
                    <td>
                        <ul style="margin:0; padding-left:16px;">
                            <?php
                            $it = $conn->prepare("
                                SELECT m.genericName, m.brandName, pi.dosage, pi.frequency, pi.duration, pi.instructions, pi.refill_count
                                FROM prescriptionitem pi
                                JOIN medication m ON pi.medicationID = m.medicationID
                                WHERE pi.prescriptionID = ?
                            ");
                            $it->bind_param("i", $pres['prescriptionID']);
                            $it->execute();
                            $items = $it->get_result();

                            while ($row = $items->fetch_assoc()):
                                ?>
                                <li>
                                    <strong><?= htmlspecialchars($row['genericName'] . ' — ' . $row['brandName']) ?></strong><br>
                                    <?= htmlspecialchars($row['dosage']) ?> — <?= htmlspecialchars($row['frequency']) ?> — <?= htmlspecialchars($row['duration']) ?><br>
                                    <?= htmlspecialchars($row['instructions']) ?><br>
                                    <em>Refills used: <?= (int)$row['refill_count'] ?></em>
                                </li>
                            <?php endwhile;
                            $it->close();
                            ?>
                        </ul>
                    </td>

                    <td><?= htmlspecialchars($pres['issueDate']) ?></td>
                    <td><?= htmlspecialchars($pres['expirationDate']) ?></td>
                    <td><?= htmlspecialchars($pres['refillInterval']) ?></td>
                    <td><?= htmlspecialchars($pres['status']) ?></td>

                    <td>
                        <a href="edit_prescription.php?id=<?= (int)$pres['prescriptionID'] ?>">Edit</a> |
                        <a class="danger" href="delete_prescription.php?id=<?= (int)$pres['prescriptionID'] ?>&from=patient" onclick="return confirm('Delete this prescription?')">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>

    <?php else: ?>
        <p>No prescriptions found for this patient.</p>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
include('doctor_standard.php');
?>
