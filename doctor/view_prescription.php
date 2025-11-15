<?php
include('../includes/db_connect.php');
include('../includes/header.php');
include('../includes/sidebar.php');

// Fetch all prescriptions (ADD patientID so we can link back!)
$prescriptions = mysqli_query($conn, "
    SELECT
        p.prescriptionID,
        p.patientID,
        pat.firstName,
        pat.lastName,
        p.issueDate,
        p.expirationDate,
        p.refillCount,
        p.refillInterval,
        p.status
    FROM prescription p
    JOIN patient pat ON p.patientID = pat.patientID
    ORDER BY p.issueDate DESC
");
?>

<div class="main-content">
    <h2>View Prescriptions</h2>

    <?php if (mysqli_num_rows($prescriptions) > 0): ?>
        <table border="1" cellpadding="10" cellspacing="0" style="width:100%; border-collapse:collapse;">
            <tr style="background-color:#f2f2f2;">
                <th>ID</th>
                <th>Patient</th>
                <th>Medications</th>
                <th>Issue Date</th>
                <th>Expiration Date</th>
                <th>Refills</th>
                <th>Interval</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>

            <?php while ($pres = mysqli_fetch_assoc($prescriptions)): ?>

                <?php
                // Fetch all items for this prescription
                $items = mysqli_query($conn, "
                    SELECT
                        pi.dosage,
                        pi.frequency,
                        pi.duration,
                        pi.instructions,
                        m.genericName,
                        m.brandName
                    FROM prescriptionitem pi
                    JOIN medication m ON pi.medicationID = m.medicationID
                    WHERE pi.prescriptionID = {$pres['prescriptionID']}
                ");
                ?>

                <tr>
                    <td><?= $pres['prescriptionID'] ?></td>

                    <td>
                        <?= htmlspecialchars($pres['firstName'] . " " . $pres['lastName']) ?><br>

                        <!-- BACK TO PATIENT PRESCRIPTIONS BUTTON -->
                        <a href="view_patient_prescription.php?id=<?= $pres['patientID'] ?>"
                           style="display:inline-block; margin-top:5px;
                                  background:#444; color:white; padding:4px 10px;
                                  font-size:12px; border-radius:5px;">
                            View Patient
                        </a>
                    </td>

                    <td>
                        <ul style="padding-left: 18px; margin:0;">
                            <?php while ($item = mysqli_fetch_assoc($items)): ?>
                                <li style="margin-bottom:6px;">
                                    <strong><?= htmlspecialchars($item['genericName'] . " — " . $item['brandName']) ?></strong><br>
                                    Dosage: <?= htmlspecialchars($item['dosage']) ?>,
                                    Frequency: <?= htmlspecialchars($item['frequency']) ?>,
                                    Duration: <?= htmlspecialchars($item['duration']) ?>,
                                    Instructions: <?= htmlspecialchars($item['instructions']) ?>
                                </li>
                            <?php endwhile; ?>
                        </ul>
                    </td>

                    <td><?= $pres['issueDate'] ?></td>
                    <td><?= $pres['expirationDate'] ?></td>
                    <td><?= $pres['refillCount'] ?></td>
                    <td><?= $pres['refillInterval'] ?></td>
                    <td><?= $pres['status'] ?></td>

                    <td>
                        <a href="edit_prescription.php?id=<?= $pres['prescriptionID'] ?>" style="color:blue;">Edit</a><br>
                        <a href="delete_prescription.php?id=<?= $pres['prescriptionID'] ?>"
                           style="color:red;"
                           onclick="return confirm('Are you sure you want to delete this prescription?');">
                           Delete
                        </a>
                    </td>
                </tr>

            <?php endwhile; ?>
        </table>

    <?php else: ?>
        <p style="text-align:center;">No prescriptions found.</p>
    <?php endif; ?>
</div>
