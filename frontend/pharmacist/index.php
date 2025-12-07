<?php
require_once __DIR__ . '/../../backend/includes/auth.php';
$user = require_role(['pharmacist']);
$pharmacyName = htmlspecialchars($user['name'] ?? 'Pharmacist', ENT_QUOTES, 'UTF-8');

$activePage = 'dashboard';
ob_start();
?>

<link rel="stylesheet" href="../css/pharmacist/dashboard.css">

<header class="dashboard-header">
    <h1>Welcome <?= $pharmacyName ?></h1>
</header>

<section class="summary-cards">
    <div class="card">
        <div id="pending-count" class="card-value">0</div>
        <div class="card-title">Pending Prescriptions</div>
        <a href="prescription.php" class="card-link">View Details</a>
    </div>
    <div class="card">
        <div id="dispensed-count" class="card-value">0</div>
        <div class="card-title">Prescriptions Dispensed</div>
        <a href="#" class="card-link">View History</a>
    </div>
    <div class="card">
        <div id="active-count" class="card-value">0</div>
        <div class="card-title">Active Prescriptions</div>
        <a href="prescription.php" class="card-link">View Details</a>
    </div>
    <div class="card">
        <div id="expiring-count" class="card-value">0</div>
        <div class="card-title">Expiring Prescriptions</div>
        <a href="prescription.php" class="card-link">View Details</a>
    </div>
</section>

<section id="recent-rx-list" class="prescription-list">
    <p>Loading recent prescriptions...</p>
</section>

<script src="js/index.js"></script>

<?php
$pageContent = ob_get_clean();
require_once 'pharmacy_standard.php';
?>