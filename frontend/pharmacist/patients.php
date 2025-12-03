<?php
require_once __DIR__ . '/../../backend/includes/auth.php';
require_once __DIR__ . '/../../backend/includes/db_connect.php';

// Protect the page and get user data
$user = require_role(['pharmacist']);

// Fetch patient data
// This query gets each patient once, along with one of their prescription IDs and associated doctor.
$patients = [];
$sql = "
    SELECT
        pat.firstName,
        pat.lastName,
        pat.contactNumber,
        pat.address,
        p.prescriptionID,
        doc.lastName AS doctorLastName
    FROM patient AS pat
    JOIN prescription AS p ON pat.patientID = p.patientID
    JOIN doctor AS doc ON p.doctorID = doc.doctorID
    GROUP BY pat.patientID
    ORDER BY pat.lastName ASC, pat.firstName ASC
";

$result = $conn->query($sql);
if ($result) {
    $patients = $result->fetch_all(MYSQLI_ASSOC);
}

// Set the active page for the sidebar and add page-specific styles
$activePage = 'patients';
$pageStyles = '<link rel="stylesheet" href="../css/pharmacist/patients.css">';

// Start output buffering
ob_start();
?>

<div class="patients-container">
    <header class="patients-header">
        <h1>Patients</h1>
        <div class="search-bar">
            <input type="text" placeholder="Search for anything here...">
            <button type="submit"><i class="fas fa-search"></i></button>
        </div>
    </header>

    <div class="patients-table-container">
        <table class="patients-table">
            <thead>
                <tr>
                    <th>Patient</th>
                    <th>Contact Info</th>
                    <th>Prescription Id</th>
                    <th>Address</th>
                    <th>Doctor</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($patients)): ?>
                    <tr>
                        <td colspan="5">No patients found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($patients as $patient): ?>
                        <tr>
                            <td><?= htmlspecialchars($patient['firstName'] . ' ' . $patient['lastName']) ?></td>
                            <td><?= htmlspecialchars($patient['contactNumber']) ?></td>
                            <td>RX-<?= htmlspecialchars($patient['prescriptionID']) ?></td>
                            <td><?= htmlspecialchars($patient['address']) ?></td>
                            <td>Dr. <?= htmlspecialchars($patient['doctorLastName']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
// Get the captured content
$pageContent = ob_get_clean();

// Include the standard layout
require_once 'pharmacy_standard.php';
?>