<?php
session_start();

// Set active page for sidebar highlighting
$activePage = 'dashboard';

// Capture dashboard content
ob_start();
?>
<h1>Hello Pharmacist</h1>
<p>Welcome to MediSync Pharmacy Dashboard</p>

<div class="stats-container">
    <div class="stat-box">
        <h2>0</h2>
        <p>Prescriptions to Dispense</p>
    </div>
    <div class="stat-box">
        <h2>0</h2>
        <p>Patients Served</p>
    </div>
    <div class="stat-box">
        <h2>0</h2>
        <p>Audit Logs</p>
    </div>
</div>
<?php
$content = ob_get_clean();

// Include the layout
include 'pharmacist_standard.php';
?>
