<?php
require_once(__DIR__ . '/../../../backend/includes/auth.php');
require_once(__DIR__ . '/../../../backend/includes/db_connect.php');

// Only doctors allowed here
require_login('/webdev_prescription/login.php', ['doctor']);

$activePage = 'dashboard';

// Doctor name from session
$doctorName = $_SESSION['user']['name'] ?? 'Doctor';

// FIX: REAL doctor ID in session
$doctorID = $_SESSION['user']['id'];
// This is the correct value used everywhere in backend
// NOT $_SESSION["doctorID"] which does NOT exist.

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
                <h1>Welcome back, <span class="name"><?= htmlspecialchars($doctorName) ?></span></h1>
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

    <!-- Patients Table -->
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

<!-- Correct doctor ID variable passed to JS -->
<script>
    const LOGGED_DOCTOR_ID = <?= $doctorID ?>;
    console.log("LOGGED_DOCTOR_ID from PHP =", LOGGED_DOCTOR_ID);
</script>

<script src="dashboard.js"></script>

<?php
$content = ob_get_clean();
include(__DIR__ . '/../doctor_standard.php');
?>
