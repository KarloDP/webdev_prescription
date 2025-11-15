<?php
include('../includes/db_connect.php');
include('../includes/header.php');
include('../includes/sidebar.php');

// Get patient ID
$patientID = $_GET['id'] ?? 0;
$patientID = (int)$patientID;

// Fetch patient info with medical fields
$patientQuery = mysqli_query($conn, "
    SELECT *
    FROM patient
    WHERE patientID = $patientID
");
$patient = mysqli_fetch_assoc($patientQuery);

// Fetch prescriptions
$prescriptions = mysqli_query($conn, "
    SELECT prescriptionID, issueDate, expirationDate, refillCount, refillInterval, status
    FROM prescription
    WHERE patientID = $patientID
    ORDER BY issueDate DESC
");
?>

<div class="main-content">
    <h2>Prescriptions for
        <?= htmlspecialchars($patient['firstName'] . " " . $patient['lastName']) ?>
    </h2>

    <a href="patients.php"
       style="padding:8px 15px; background:#333; color:white; text-decoration:none;
              border-radius:5px; margin-bottom:15px; display:inline-block;">
       ← Back to Patient List
    </a>

    <!-- MEDICAL INFO BOX -->
    <div style="background:#f1f1f1; padding:15px; border-radius:8px; margin-bottom:25px;">
        <h3>Patient Medical Summary</h3>

        <p><strong>Health Condition:</strong><br>
            <?= nl2br(htmlspecialchars($patient['healthCondition'])) ?></p>

        <p><strong>Allergies:</strong><br>
            <?= nl2br(htmlspecialchars($patient['allergies'])) ?></p>

        <p><strong>Current Medication:</strong><br>
            <?= nl2br(htmlspecialchars($patient['currentMedication'])) ?></p>

        <p><strong>Known Diseases:</strong><br>
            <?= nl2br(htmlspecialchars($patient['knownDiseases'])) ?></p>
    </div>

    <?php if (mysqli_num_rows($prescriptions) > 0): ?>

        <table border="1" cellpadding="10" cellspacing="0"
               style="width:100%; border-collapse:collapse;">
            <thead style="background-color:#f2f2f2;">
                <tr>
                    <th>ID</th>
                    <th>Medications</th>
                    <th>Issue Date</th>
                    <th>Expiration Date</th>
                    <th>Refills</th>
                    <th>Interval</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <?php while ($pres = mysqli_fetch_assoc($prescriptions)): ?>

                <?php
                // Fetch items for this prescription
                $items = mysqli_query($conn, "
                    SELECT pi.dosage, pi.frequency, pi.duration, pi.instructions,
                           m.genericName, m.brandName
                    FROM prescriptionitem pi
                    JOIN medication m ON pi.medicationID = m.medicationID
                    WHERE pi.prescriptionID = {$pres['prescriptionID']}
                ");
                ?>

                <tr>
                    <td><?= $pres['prescriptionID'] ?></td>

                    <td>
                        <ul style="padding-left:20px; margin:0;">
                            <?php while ($item = mysqli_fetch_assoc($items)): ?>
                                <li>
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

        <p>No prescriptions found for this patient.</p>

    <?php endif; ?>
</div>
