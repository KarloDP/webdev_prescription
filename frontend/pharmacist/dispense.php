<?php
require_once __DIR__ . '/../../backend/includes/auth.php';
require_once __DIR__ . '/../../backend/includes/db_connect.php';

// Protect the page and get user data
$user = require_role(['pharmacist']);

// Set the active page for the sidebar and add page-specific styles
$activePage = 'dispense';
$pageStyles = '<link rel="stylesheet" href="../css/pharmacist/dispense.css">';

// --- Data Fetching ---

// 1. Fetch a summary list of prescriptions for the left sidebar
$prescriptionSummary = [];
$sqlSummary = "
    SELECT
        p.prescriptionID,
        p.issueDate,
        pat.firstName,
        pat.lastName
    FROM
        prescription AS p
    JOIN
        patient AS pat ON p.patientID = pat.patientID
    ORDER BY
        p.issueDate DESC";
$resultSummary = $conn->query($sqlSummary);
if ($resultSummary) {
    $prescriptionSummary = $resultSummary->fetch_all(MYSQLI_ASSOC);
}

// 2. Determine which prescription details to show (default to the first one if available
//    or if a specific ID is passed via GET parameter).
$selectedPrescriptionID = $_GET['prescription_id'] ?? ($prescriptionSummary[0]['prescriptionID'] ?? null);

$selectedPrescriptionDetails = null;
if ($selectedPrescriptionID !== null) {
    $sqlDetails = "
        SELECT
            p.prescriptionID,
            DATE_FORMAT(p.issueDate, '%M %e, %Y') AS formattedIssueDate,
            pat.firstName AS patientFirstName,
            pat.lastName AS patientLastName,
            pat.address,
            pat.birthdate, /* Using 'birthdate' as per your confirmation */
            pat.gender,
            doc.lastName AS doctorLastName,
            pi.prescriptionItemID,
            pi.dosage,
            pi.prescribed_amount,
            pi.instructions AS medicationInstructions,
            med.genericName AS medicationName,
            med.medicationID
        FROM
            prescription AS p
        JOIN
            patient AS pat ON p.patientID = pat.patientID
        JOIN
            doctor AS doc ON p.doctorID = doc.doctorID
        JOIN
            prescriptionitem AS pi ON p.prescriptionID = pi.prescriptionID
        JOIN
            medication AS med ON pi.medicationID = med.medicationID
        WHERE
            p.prescriptionID = ?
        ORDER BY pi.prescriptionItemID ASC;
    ";
    $stmtDetails = $conn->prepare($sqlDetails);
    if ($stmtDetails) {
        $stmtDetails->bind_param('i', $selectedPrescriptionID);
        $stmtDetails->execute();
        $resultDetails = $stmtDetails->get_result();

        if ($resultDetails) {
            $rawDetails = $resultDetails->fetch_all(MYSQLI_ASSOC);
            if (!empty($rawDetails)) {
                // Extract patient and prescription header info from the first row
                $selectedPrescriptionDetails = [
                    'prescriptionID' => $rawDetails[0]['prescriptionID'],
                    'formattedIssueDate' => $rawDetails[0]['formattedIssueDate'],
                    'patientFirstName' => $rawDetails[0]['patientFirstName'],
                    'patientLastName' => $rawDetails[0]['patientLastName'],
                    'patientAddress' => $rawDetails[0]['address'],
                    'patientDateOfBirth' => $rawDetails[0]['birthdate'], // Corrected column name
                    'patientGender' => $rawDetails[0]['gender'],
                    'doctorLastName' => $rawDetails[0]['doctorLastName'],
                    'medications' => []
                ];

                // Calculate age from date of birth
                $dob = new DateTime($selectedPrescriptionDetails['patientDateOfBirth']);
                $now = new DateTime();
                $age = $now->diff($dob)->y;
                $selectedPrescriptionDetails['patientAge'] = $age;

                // Add all medication items for this prescription
                foreach ($rawDetails as $item) {
                    $selectedPrescriptionDetails['medications'][] = [
                        'prescriptionItemID' => $item['prescriptionItemID'],
                        'medicationName' => $item['medicationName'],
                        'dosage' => $item['dosage'],
                        'prescribed_amount' => $item['prescribed_amount'],
                        'medicationInstructions' => $item['medicationInstructions'],
                        'medicationID' => $item['medicationID']
                    ];
                }
            }
        }
        $stmtDetails->close();
    }
}

// Start output buffering to capture the HTML content
ob_start();
?>

<div class="medication-page-container">
    <div class="prescription-history-sidebar">
        <h2>Edit Prescribed usages</h2>
        <p class="history-view-link">View Prescription History</p>
        <div class="search-bar-small">
            <input type="text" placeholder="Search for Users">
            <button type="submit"><i class="fas fa-search"></i></button>
        </div>
        <div class="prescription-list-scroll">
            <?php if (empty($prescriptionSummary)): ?>
                <p>No prescriptions found.</p>
            <?php else: ?>
                <?php $currentPatientName = ''; ?>
                <?php $count = 1; foreach ($prescriptionSummary as $rxSum): ?>
                    <?php
                        $patientFullName = htmlspecialchars($rxSum['firstName'] . ' ' . $rxSum['lastName']);
                        // Display patient name header only if it changes
                        if ($patientFullName !== $currentPatientName) {
                            echo '<h3 class="patient-name-header">' . $patientFullName . '</h3>';
                            $currentPatientName = $patientFullName;
                        }
                    ?>
                    <div class="prescription-item <?= ($selectedPrescriptionID == $rxSum['prescriptionID']) ? 'active' : '' ?>">
                        <a href="?prescription_id=<?= htmlspecialchars($rxSum['prescriptionID']) ?>">
                            <span>Prescription <?= $count++ ?></span>
                            <span><?= htmlspecialchars(date('F j, Y', strtotime($rxSum['issueDate']))) ?></span>
                        </a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="prescription-details-content">
        <?php if ($selectedPrescriptionDetails): ?>
            <header class="details-header">
                <h1>Prescription <?= htmlspecialchars($selectedPrescriptionDetails['prescriptionID']) ?></h1>
                <div class="action-buttons">
                    <a href="edit_prescription.php?id=<?= htmlspecialchars($selectedPrescriptionDetails['prescriptionID']) ?>" class="btn-edit-prescription">Edit Prescription</a>
                    <!-- Removed the "Save Changes" button as editing happens on a separate page -->
                </div>
            </header>

            <section class="patient-info-card">
                <div class="info-row">
                    <div class="info-item">
                        <span class="label">Name</span>
                        <span class="value"><?= htmlspecialchars($selectedPrescriptionDetails['patientFirstName'] . ' ' . $selectedPrescriptionDetails['patientLastName']) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="label">Address</span>
                        <span class="value"><?= htmlspecialchars($selectedPrescriptionDetails['patientAddress']) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="label">Age</span>
                        <span class="value"><?= htmlspecialchars($selectedPrescriptionDetails['patientAge']) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="label">Sex</span>
                        <span class="value"><?= htmlspecialchars($selectedPrescriptionDetails['patientGender']) ?></span>
                    </div>
                </div>
            </section>

            <section class="medication-rx-section">
                <h2>Rx</h2>
                <ul class="medication-rx-list">
                    <?php if (empty($selectedPrescriptionDetails['medications'])): ?>
                        <li>No medication items for this prescription.</li>
                    <?php else: ?>
                        <?php foreach ($selectedPrescriptionDetails['medications'] as $medItem): ?>
                            <?php
                                $unit = '';
                                if (!empty($medItem['dosage'])) {
                                    $dosageParts = explode(' ', $medItem['dosage']);
                                    // Try to extract a sensible unit from the last part of dosage
                                    if (count($dosageParts) > 1 && !is_numeric(end($dosageParts))) {
                                        $unit = end($dosageParts);
                                        // Simple pluralization logic
                                        if ($medItem['prescribed_amount'] > 1 && substr($unit, -1) !== 's') {
                                            $unit .= 's';
                                        }
                                    }
                                }
                            ?>
                            <li>
                                <strong><?= htmlspecialchars($medItem['medicationName']) ?> <?= htmlspecialchars($medItem['dosage']) ?></strong>
                                <ul>
                                    <li><?= htmlspecialchars($medItem['medicationInstructions'] ?? 'No instructions provided.') ?></li>
                                    <li>Dispense: <?= htmlspecialchars($medItem['prescribed_amount']) ?> <?= htmlspecialchars($unit) ?></li>
                                </ul>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            </section>

            <section class="other-details">
                <p>Physician's Sig</p>
                <p>LIC No.</p>
                <p>PTR No.</p>
                <p>S2 No.</p>
            </section>

        <?php else: ?>
            <p>No prescription selected or found.</p>
        <?php endif; ?>
    </div>
</div>

<?php
// Get the captured content
$pageContent = ob_get_clean();

// Include the standard layout
require_once 'pharmacy_standard.php';
?>