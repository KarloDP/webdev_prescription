<?php
require_once __DIR__ . '/../../backend/includes/auth.php';
require_once __DIR__ . '/../../backend/includes/db_connect.php';

// Protect the page and get user data
$user = require_role(['pharmacist']);
$pharmacyId = (int)$user['id'];
$pharmacyName = htmlspecialchars($user['name'] ?? 'Pharmacist', ENT_QUOTES, 'UTF-8');

// --- DATA FETCHING ---

// 1. Get summary card counts
// Note: These queries might need refinement based on your exact business logic.

// Pending Prescriptions (Prescriptions with an 'Active' status)
$pendingResult = $conn->query("SELECT COUNT(*) as count FROM prescription WHERE status = 'Active'");
$pendingCount = $pendingResult->fetch_assoc()['count'] ?? 0;

// Prescriptions Dispensed by this pharmacy
$dispensedStmt = $conn->prepare("SELECT COUNT(DISTINCT prescriptionItemID) as count FROM dispenserecord WHERE pharmacyID = ?");
$dispensedStmt->bind_param('i', $pharmacyId);
$dispensedStmt->execute();
$dispensedCount = $dispensedStmt->get_result()->fetch_assoc()['count'] ?? 0;

// Expiring Prescriptions (Active and expiring within 30 days)
$expiringResult = $conn->query("SELECT COUNT(*) as count FROM prescription WHERE status = 'Active' AND expirationDate BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)");
$expiringCount = $expiringResult->fetch_assoc()['count'] ?? 0;

// 2. Get recent prescriptions list
$prescriptions = [];
$sql = "
    SELECT
        p.prescriptionID,
        pat.firstName AS patientFirstName,
        pat.lastName AS patientLastName,
        med.genericName AS medicationName,
        doc.lastName AS doctorLastName,
        pi.dosage,
        pi.prescribed_amount
    FROM prescription AS p
    JOIN patient AS pat ON p.patientID = pat.patientID
    JOIN prescriptionitem AS pi ON p.prescriptionID = pi.prescriptionID
    JOIN medication AS med ON pi.medicationID = med.medicationID
    JOIN doctor AS doc ON p.doctorID = doc.doctorID
    WHERE p.status = 'Active'
    ORDER BY p.issueDate DESC
    LIMIT 6
";
$prescriptionResult = $conn->query($sql);
if ($prescriptionResult) {
    $prescriptions = $prescriptionResult->fetch_all(MYSQLI_ASSOC);
}


// Set the active page for the sidebar
$activePage = 'dashboard';

// Start output buffering to capture the HTML content
ob_start();
?>

<!-- Add a link to the dashboard-specific stylesheet -->
<link rel="stylesheet" href="../css/pharmacist/dashboard.css">

<header class="dashboard-header">
    <h1>Welcome <?= $pharmacyName ?></h1>
</header>

<section class="summary-cards">
    <div class="card">
        <div class="card-value"><?= $pendingCount ?></div>
        <div class="card-title">Pending Prescriptions</div>
        <a href="#" class="card-link">View Details</a>
    </div>
    <div class="card">
        <div class="card-value"><?= $dispensedCount ?></div>
        <div class="card-title">Prescriptions Dispensed</div>
        <a href="#" class="card-link">View Details</a>
    </div>
    <div class="card">
        <div class="card-value"><?= $pendingCount ?></div>
        <div class="card-title">Active Prescriptions</div>
        <a href="#" class="card-link">View Details</a>
    </div>
    <div class="card">
        <div class="card-value"><?= $expiringCount ?></div>
        <div class="card-title">Expiring Prescriptions</div>
        <a href="#" class="card-link">View Details</a>
    </div>
</section>

<section class="prescription-list">
    <?php if (empty($prescriptions)): ?>
        <p>No active prescriptions found.</p>
    <?php else: ?>
        <?php foreach ($prescriptions as $rx): ?>
        <div class="prescription-card">
            <div class="card-header">
                <span>Patient: <strong><?= htmlspecialchars($rx['patientFirstName'] . ' ' . substr($rx['patientLastName'], 0, 1) . '.') ?></strong></span>
                <span>Rx ID: <strong><?= htmlspecialchars($rx['prescriptionID']) ?></strong></span>
            </div>
            <div class="card-body">
                <p>Medication: <strong><?= htmlspecialchars($rx['medicationName']) ?></strong></p>
                <p>Remaining: <strong>Dr. <?= htmlspecialchars($rx['doctorLastName']) ?>, <?= htmlspecialchars($rx['prescribed_amount']) ?> Tablets</strong></p>
            </div>
            <div class="card-footer">
                <button class="btn-view">View Prescription</button>
                <button class="btn-dispense">Dispense Medication</button>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</section>

<?php
// Get the captured content
$pageContent = ob_get_clean();

// Include the standard layout
require_once 'pharmacy_standard.php';
?>