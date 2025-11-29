<?php
include('../includes/db_connect.php');

// Get Prescription ID from URL
$prescriptionID = isset($_GET['id']) ? intval($_GET['id']) : 0;
//backend\DISTRIBUTE_TO_APPROPRIATE_FILES\doctor\prescription_history.php
if ($prescriptionID <= 0) {
    echo "<div class='main-content'><p>Invalid Prescription ID provided.</p></div>";
    exit;
}

// Fetch prescription and patient info using prepared statements
$stmt = $conn->prepare("
    SELECT p.patientID, p.issueDate, pat.firstName, pat.lastName
    FROM prescription p
    JOIN patient pat ON p.patientID = pat.patientID
    WHERE p.prescriptionID = ?
");
$stmt->bind_param("i", $prescriptionID);
$stmt->execute();
$result = $stmt->get_result();
$prescription = $result->fetch_assoc();
$stmt->close();

if (!$prescription) {
    echo "<div class='main-content'><p>Prescription not found.</p></div>";
    exit;
}
$patientID = $prescription['patientID'];

// Fetch all items for this prescription
$itemsStmt = $conn->prepare("
    SELECT pi.prescriptionItemID, pi.dosage, pi.frequency, pi.duration, m.genericName, m.brandName
    FROM prescriptionitem pi
    JOIN medication m ON pi.medicationID = m.medicationID
    WHERE pi.prescriptionID = ?
    ORDER BY pi.prescriptionItemID ASC
");
$itemsStmt->bind_param("i", $prescriptionID);
$itemsStmt->execute();
$itemsResult = $itemsStmt->get_result();
?>

<div class="main-content">
    <h2>Dispense History for Prescription #<?= htmlspecialchars($prescriptionID) ?></h2>
    <p style="margin-bottom: 20px;">
        <strong>Patient:</strong> <?= htmlspecialchars($prescription['firstName'] . ' ' . $prescription['lastName']) ?><br>
        <strong>Issue Date:</strong> <?= htmlspecialchars(date("F j, Y", strtotime($prescription['issueDate']))) ?>
    </p>

    <a href="view_patient_prescription.php?id=<?= $patientID ?>"
       style="padding:8px 15px; background:#333; color:white; text-decoration:none;
              border-radius:5px; margin-bottom:25px; display:inline-block;">
       ← Back to Patient's Prescriptions
    </a>

    <?php if ($itemsResult->num_rows > 0): ?>
        <?php while ($item = $itemsResult->fetch_assoc()): ?>
            <div class="prescription-item-history" style="border:1px solid #ccc; border-radius:8px; padding:15px; margin-bottom:20px; background-color: #f9f9f9;">
                <h4 style="margin-top:0;">
                    Medication: <?= htmlspecialchars($item['genericName'] . ' (' . $item['brandName'] . ')') ?>
                </h4>
                <p style="font-size:14px;">
                    <strong>Dosage:</strong> <?= htmlspecialchars($item['dosage']) ?> |
                    <strong>Frequency:</strong> <?= htmlspecialchars($item['frequency']) ?> |
                    <strong>Duration:</strong> <?= htmlspecialchars($item['duration']) ?>
                </p>

                <h5 style="margin-bottom:10px; border-bottom:1px solid #eee; padding-bottom:5px;">Dispense Records</h5>
                <?php
                // Fetch dispense records for this specific item, including the new column
                $dispenseStmt = $conn->prepare("
                    SELECT dispenseID, pharmacyID, quantityDispensed, dateDispensed, pharmacistName, status, nextAvailableDates
                    FROM dispenserecord
                    WHERE prescriptionItemID = ?
                    ORDER BY dateDispensed DESC
                ");
                $dispenseStmt->bind_param("i", $item['prescriptionItemID']);
                $dispenseStmt->execute();
                $dispenseResult = $dispenseStmt->get_result();
                ?>

                <?php if ($dispenseResult->num_rows > 0): ?>
                    <table border="1" cellpadding="8" cellspacing="0" style="width:100%; border-collapse:collapse; font-size:14px; background-color:white;">
                        <thead style="background-color:#f0f0f0;">
                            <tr>
                                <th>Dispense ID</th>
                                <th>Pharmacy ID</th>
                                <th>Quantity</th>
                                <th>Date Dispensed</th>
                                <th>Next Available</th>
                                <th>Pharmacist</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($dispense = $dispenseResult->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($dispense['dispenseID']) ?></td>
                                    <td><?= htmlspecialchars($dispense['pharmacyID']) ?></td>
                                    <td><?= htmlspecialchars($dispense['quantityDispensed']) ?></td>
                                    <td><?= htmlspecialchars(date("Y-m-d", strtotime($dispense['dateDispensed']))) ?></td>
                                    <td><?= htmlspecialchars($dispense['nextAvailableDates']) ?></td>
                                    <td><?= htmlspecialchars($dispense['pharmacistName']) ?></td>
                                    <td><?= htmlspecialchars($dispense['status']) ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p style="font-size:14px; color:#777;">No dispense records found for this medication item.</p>
                <?php endif; 
                $dispenseStmt->close();
                ?>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>No medication items found for this prescription.</p>
    <?php endif; 
    $itemsStmt->close();
    ?>
</div>