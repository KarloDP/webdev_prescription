<?php
session_start();

// Set active page for sidebar highlighting
$activePage = 'dashboard';

// Capture dashboard content
ob_start();
?>
<h1>Hello Doctor</h1>
<p>Welcome to MediSync Doctor Dashboard</p>

<div class="stats-container">
    <div class="stat-box">
        <h2>0</h2>
        <p>Patients Assigned</p>
    </div>
    <div class="stat-box">
        <h2>0</h2>
        <p>Prescriptions Created</p>
    </div>
    <div class="stat-box">
        <h2>0</h2>
        <p>Profile Updates</p>
    </div>
</div>
<?php
$content = ob_get_clean();

// Include the layout
include 'patient_standard.php';
?>
