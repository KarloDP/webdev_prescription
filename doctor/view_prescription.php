<?php
include('../includes/db_connect.php');
$activePage = 'prescriptions';

$stmt = $conn->prepare("SELECT p.prescriptionID, p.patientID, p.issueDate, p.expirationDate, p.refillInterval, p.status, pat.firstName, pat.lastName FROM prescription p JOIN patient pat ON p.patientID = pat.patientID ORDER BY p.issueDate DESC");
$stmt->execute();
$pres = $stmt->get_result();
$stmt->close();

ob_start();
?>

    <div class="card">
        <h2>View Prescriptions</h2>
        <a class="btn" href="add_prescription.php">+ Add Prescription</a>
    </div>

    <div class="card">
        <?php if ($pres->num_rows > 0): ?>
            <table>
                <thead><tr><th>ID</th><th>Patient</th><th>Medications</th><th>Issue</th><th>Expires</th><th>Interval</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                <?php while ($r = $pres->fetch_assoc()): ?>
                    <tr>
                        <td><?= $r['prescriptionID'] ?></td>
                        <td>
                            <?= htmlspecialchars($r['firstName'].' '.$r['lastName']) ?><br>
                            <a href="view_patient_prescription.php?id=<?= $r['patientID'] ?>">View Patient</a>
                        </td>
                        <td>
                            <ul style="margin:0; padding-left:14px;">
                                <?php
                                $it = $conn->prepare("SELECT m.genericName, m.brandName, pi.dosage, pi.frequency FROM prescriptionitem pi JOIN medication m ON pi.medicationID = m.medicationID WHERE pi.prescriptionID = ?");
                                $it->bind_param("i", $r['prescriptionID']);
                                $it->execute();
                                $items = $it->get_result();
                                while ($row = $items->fetch_assoc()):
                                    ?>
                                    <li><strong><?= htmlspecialchars($row['genericName'].' — '.$row['brandName']) ?></strong> (<?= htmlspecialchars($row['dosage']) ?>, <?= htmlspecialchars($row['frequency']) ?>)</li>
                                <?php endwhile; $it->close(); ?>
                            </ul>
                        </td>
                        <td><?= $r['issueDate'] ?></td>
                        <td><?= $r['expirationDate'] ?></td>
                        <td><?= $r['refillInterval'] ?></td>
                        <td><?= htmlspecialchars($r['status']) ?></td>
                        <td>
                            <a href="edit_prescription.php?id=<?= $r['prescriptionID'] ?>">Edit</a><br>
                            <a class="danger" href="delete_prescription.php?id=<?= $r['prescriptionID'] ?>" onclick="return confirm('Delete prescription?')">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No prescriptions found.</p>
        <?php endif; ?>
    </div>

<?php
$content = ob_get_clean();
include('doctor_standard.php');
