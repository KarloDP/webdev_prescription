<?php
require_once __DIR__ . '/../../backend/includes/auth.php';
require_login('/../../login.php', ['patient']);
$user = require_role(['patient']);

$activePage = 'dashboard';
$pageStyles = '<link rel="stylesheet" href="../css/patient/dashboard.css">';

ob_start();
?>

<header class="dashboard-header">
    <h1>Welcome, <?= htmlspecialchars($user['firstName'] ?? 'Patient', ENT_QUOTES, 'UTF-8') ?></h1>
    <p>View your prescriptions and manage your medications.</p>
</header>z

<section class="summary-cards">
    <div class="card">
        <div class="card-icon"><i class="fas fa-notes-medical"></i></div>
        <div class="card-value" id="active-count">0</div>
        <div class="card-title">Active Prescriptions</div>
    </div>
    <div class="card">
        <div class="card-icon"><i class="fas fa-hourglass-end"></i></div>
        <div class="card-value" id="upcoming-count">0</div>
        <div class="card-title">Upcoming Refills</div>
    </div>
    <div class="card">
        <div class="card-icon"><i class="fas fa-map-marker-alt"></i></div>
        <div class="card-value" id="nearby-count">0</div>
        <div class="card-title">Nearby Pharmacies</div>
    </div>
</section>

<section class="active-prescriptions">
    <h2>Active Prescriptions</h2>
    <div id="active-prescriptions-container">
        <p>Loading prescriptions...</p>
    </div>
</section>

<script src="patient/patient.js"></script>

<?php
$pageContent = ob_get_clean();
require_once '../patient_standard.php';
?>