<?php
session_start();

// Set which page is active for sidebar highlighting
$activePage = 'dashboard';

// Start output buffering to capture dashboard content
ob_start();
?>
<h1>Hello There User</h1>
<p>Welcome to MediSync Dashboard</p>

<div class="stats-container">
    <div class="stat-box">
        <h2>0</h2>
        <p>Upcoming Appointments</p>
    </div>
    <div class="stat-box">
        <h2>0</h2>
        <p>Prescriptions</p>
    </div>
    <div class="stat-box">
        <h2>0</h2>
        <p>Notifications</p>
    </div>
</div>
<?php
$content = ob_get_clean();

// Include the main patient layout
include 'patient_standard.php';
?>
