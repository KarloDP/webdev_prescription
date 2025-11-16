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

// Fetch prescriptions - CORRECTED QUERY
$prescriptions = mysqli_query($conn, "
    SELECT prescriptionID, issueDate, expirationDate, refillInterval, status
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
        <p><strong>Health Condition:</strong><br><?= nl2br(htmlspecialchars($patient['healthCondition'])) ?></p>
        <p><strong>Allergies:</strong><br><?= nl2br(htmlspecialchars($patient['allergies'])) ?></p>
        <p><strong>Current Medication:</strong><br><?= nl2br(htmlspecialchars($patient['currentMedication'])) ?></p>
        <p><strong>Known Diseases:</strong><br><?= nl2br(htmlspecialchars($patient['knownDiseases'])) ?></p>
    </div>

    <?php if (mysqli_num_rows($prescriptions) > 0): ?>

        <table border="1" cellpadding="10" cellspacing="0" style="width:100%; border-collapse:collapse;">
            <tr style="background-color:#f2f2f2;">
                <th>ID</th>
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
                    SELECT m.brandName
                    FROM prescriptionitem pi
                    JOIN medication m ON pi.medicationID = m.medicationID
                    WHERE pi.prescriptionID = {$pres['prescriptionID']}
                ");
                ?>
                <tr>
                    <td><?= $pres['prescriptionID'] ?></td>
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
        <p>No prescriptions found for this patient.</p>
    <?php endif; ?>
</div>
