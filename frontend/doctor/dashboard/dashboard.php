<?php
// filepath: c:\wamp64\www\WebDev_Prescription\frontend\doctor\dashboard\dashboard.php
require_once(__DIR__ . '/../../../backend/includes/auth.php');
require_once(__DIR__ . '/../../../backend/includes/db_connect.php');
require_login('/webdev_prescription/login.php', ['doctor']);

$activePage = 'dashboard';
$doctorName = $_SESSION['user']['name'] ?? 'Doctor';


ob_start();
?>
<div class="dash-hero">
  <h2>Welcome back, <?= htmlspecialchars($doctorName) ?></h2>
  <p>Here is a summary of your activity.</p>
</div>

<div class="doctor-dashboard">
    <!-- Welcome Row -->
    <div class="welcome-row">
        <div class="welcome-card">
            <div>
                <h1>Welcome back, <span class="name"><?= $doctorName ?></span></h1>
                <p class="subtitle">Here is a summary of your activity.</p>
            </div>
        </div>

<div class="stats-card">
            <div class="stats-row">
                <div class="stat">
                    <div id="patients-count" class="stat-number">...</div>
                    <div class="stat-label">Total Patients</div>
                </div>
                <div class="stat">
                    <div id="active-prescriptions-count" class="stat-number">...</div>
                    <div class="stat-label">Active Prescriptions</div>
                </div>
                <div class="stat">
                    <div id="meds-prescribed-count" class="stat-number">...</div>
                    <div class="stat-label">Medications Prescribed</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Patients Table Section -->
    <h2 class="section-title">Recent Patients</h2>
    <div class="table-frame">
        <table class="table-base">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Date of Birth</th>
                </tr>
            </thead>
            <tbody id="patients-table-body">
                <tr><td colspan="4">Loading patients...</td></tr>
            </tbody>
        </table>
    </div>
</div>

<script src="dashboard.js"></script>

<?php
$content = ob_get_clean();
include(__DIR__ . '/../doctor_standard.php');
?>