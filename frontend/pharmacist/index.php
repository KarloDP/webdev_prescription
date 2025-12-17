<?php
// Note: auth.php is included by pharmacy_standard.php, so we don't need to include it here
// We'll get the user info after including the standard file

$activePage = 'dashboard';
$pageStyles = '<link rel="stylesheet" href="../css/pharmacist/dashboard.css">';

ob_start();
?>

<header class="dashboard-header">
    <h1>Welcome <?= htmlspecialchars($_SESSION['user']['name'] ?? 'Pharmacist', ENT_QUOTES, 'UTF-8') ?></h1>
</header>

<section class="summary-cards">
    <div class="card">
        <div id="pending-count" class="card-value">0</div>
        <div class="card-title">Pending Prescriptions</div>
    </div>
    <div class="card">
        <div id="dispensed-count" class="card-value">0</div>
        <div class="card-title">Prescriptions Dispensed</div>
    </div>
    <div class="card">
        <div id="active-count" class="card-value">0</div>
        <div class="card-title">Active Prescriptions</div>
    </div>
    <div class="card">
        <div id="expiring-count" class="card-value">0</div>
        <div class="card-title">Expiring Soon</div>
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