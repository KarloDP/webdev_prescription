<?php
// view_prescription.php

session_start();
include('../includes/db_connect.php');

$activePage = 'prescriptions';
//backend\DISTRIBUTE_TO_APPROPRIATE_FILES\doctor\view_prescription.php
// Fetch all prescriptions with patient + doctor names
$sql = "
    SELECT 
        p.prescriptionID,
        p.issueDate,
        p.expirationDate,
        p.status,
        pat.firstName AS patientFirst,
        pat.lastName AS patientLast,
        d.firstName AS doctorFirst,
        d.lastName AS doctorLast
    FROM prescription p
    LEFT JOIN patient pat ON pat.patientID = p.patientID
    LEFT JOIN doctor d ON d.doctorID = p.doctorID
    ORDER BY p.prescriptionID DESC
";

$list = $conn->query($sql);

// ─────────── BUILD PAGE CONTENT ───────────
ob_start();
?>

    <div class="card">
        <h2 style="margin-bottom:15px;">Prescription List</h2>

        <table>
            <thead>
            <tr>
                <th>ID</th>
                <th>Patient</th>
                <th>Doctor</th>
                <th>Issue Date</th>
                <th>Expiration</th>
                <th>Status</th>
                <th style="width:220px;">Actions</th>
            </tr>
            </thead>

            <tbody>
            <?php while ($p = $list->fetch_assoc()): ?>
                <tr>
                    <td><?= $p['prescriptionID'] ?></td>

                    <td>
                        <?= htmlspecialchars($p['patientFirst'] . " " . $p['patientLast']) ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($p['doctorFirst'] . " " . $p['doctorLast']) ?>
                    </td>

                    <td><?= htmlspecialchars($p['issueDate']) ?></td>
                    <td><?= htmlspecialchars($p['expirationDate']) ?></td>

                    <td><?= htmlspecialchars($p['status']) ?></td>

                    <td>
                        <a href="view_patient_prescription.php?id=<?= $p['prescriptionID'] ?>" class="btn">
                            View
                        </a>

                        <a href="edit_prescription.php?id=<?= $p['prescriptionID'] ?>" class="btn" style="background:#007bff;">
                            Edit
                        </a>

                        <a href="delete_prescription.php?id=<?= $p['prescriptionID'] ?>"
                           class="btn btn-danger"
                           onclick="return confirm('Delete this prescription?');">
                            Delete
                        </a>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>

<?php
$content = ob_get_clean();
include('doctor_standard.php');
?>