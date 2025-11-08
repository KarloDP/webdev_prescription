<?php
session_start();

// Set active page for sidebar highlighting
$activePage = 'dashboard';

// Capture dashboard content
ob_start();
?>
<h1>Hello Admin</h1>
<p>Welcome to MediSync Admin Dashboard</p>

<div class="stats-container">
    <div class="stat-box">
        <h2>0</h2>
        <p>Users Registered</p>
    </div>
    <div class="stat-box">
        <h2>0</h2>
        <p>Medicines in System</p>
    </div>
    <div class="stat-box">
        <h2>0</h2>
        <p>System Logs</p>
    </div>
</div>
<?php
$content = ob_get_clean();

// Include the layout
include 'admin_standard.php';
?>
