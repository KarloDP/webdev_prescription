<?php
require_once __DIR__ . '/../../backend/includes/auth.php';
require_once __DIR__ . '/../../backend/includes/db_connect.php';

// Protect the page and get user data
$user = require_role(['pharmacist']);

// Fetch prescription data
$prescriptions = [];
$sql = "
    SELECT
        p.prescriptionID,
        pat.firstName,
        pat.lastName,
        GROUP_CONCAT(med.genericName SEPARATOR ', ') AS medicationName,
        SUM(pi.prescribed_amount) AS totalQuantity,
        p.status,
        doc.lastName AS doctorLastName
    FROM prescription AS p
    JOIN patient AS pat ON p.patientID = pat.patientID
    JOIN prescriptionitem AS pi ON p.prescriptionID = pi.prescriptionID
    JOIN medication AS med ON pi.medicationID = med.medicationID
    JOIN doctor AS doc ON p.doctorID = doc.doctorID
    GROUP BY p.prescriptionID, pat.firstName, pat.lastName, p.status, doc.lastName
    ORDER BY p.issueDate DESC
";

$result = $conn->query($sql);
if ($result) {
    $prescriptions = $result->fetch_all(MYSQLI_ASSOC);
}

// Set the active page for the sidebar and add page-specific styles
$activePage = 'prescription';
$pageStyles = '<link rel="stylesheet" href="../css/pharmacist/prescription.css">';

// Start output buffering
ob_start();
?>

<div class="prescription-container">
    <header class="prescription-header">
        <h1>Prescription Details</h1>
        <div class="search-bar">
            <input type="text" placeholder="Search for anything here...">
            <button type="submit"><i class="fas fa-search"></i></button>
        </div>
    </header>

    <div class="prescription-table-container">
        <table class="prescription-table">
            <thead>
                <tr>
                    <th>Prescription Id</th>
                    <th>Name</th>
                    <th>Medication</th>
                    <th>Quantity</th>
                    <th>Status</th>
                    <th>Doctor</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($prescriptions)): ?>
                    <tr>
                        <td colspan="7">No prescriptions found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($prescriptions as $rx): ?>
                        <tr>
                            <td>RX-<?= htmlspecialchars($rx['prescriptionID']) ?></td>
                            <td><?= htmlspecialchars($rx['firstName'] . ' ' . $rx['lastName']) ?></td>
                            <td><?= htmlspecialchars($rx['medicationName']) ?></td>
                            <td><?= htmlspecialchars($rx['totalQuantity']) ?></td>
                            <td><?= htmlspecialchars($rx['status']) ?></td>
                            <td><?= htmlspecialchars($rx['doctorLastName']) ?></td>
                            <td><a href="edit_prescription.php?id=<?= htmlspecialchars($rx['prescriptionID']) ?>" class="btn-edit">Edit</a></td>
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