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
                <th>Refill Interval</th>
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
                        <a href="view_patient_prescription.php?id=<?= $pres['patientID'] ?>">
                            <?= htmlspecialchars($pres['firstName'] . " " . $pres['lastName']) ?>
                        </a>
                    </td>

                    <td>
                        <?php
                        // List medication brand names
                        if (mysqli_num_rows($items) > 0) {
                            $medNames = [];
                            while ($item = mysqli_fetch_assoc($items)) {
                                $medNames[] = htmlspecialchars($item['brandName']);
                            }
                            echo implode(', ', $medNames);
                        } else {
                            echo "N/A";
                        }
                        ?>
                    </td>

                    <td><?= $pres['issueDate'] ?></td>
                    <td><?= $pres['expirationDate'] ?></td>
                    <td><?= $pres['refillInterval'] ?></td>
                    <td><?= $pres['status'] ?></td>

                    <td>
                        <a href="prescription_history.php?id=<?= $pres['prescriptionID'] ?>" style="color:green;">History</a> |
                        <a href="edit_prescription.php?id=<?= $pres['prescriptionID'] ?>" style="color:blue;">Edit</a> |
                        <a href="delete_prescription.php?id=<?= $pres['prescriptionID'] ?>"
                           onclick="return confirm('Delete this prescription?')"
                           style="color:red;">
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
