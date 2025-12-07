<?php
require_once __DIR__ . '/../../backend/includes/auth.php';
$user = require_role(['pharmacist']);

$activePage = 'dispense';
$pageStyles = '<link rel="stylesheet" href="/WebDev_Prescription/frontend/css/pharmacist/dispense.css">';

ob_start();
?>

<div class="medication-page-container">
    <!-- Left Sidebar for Prescription List -->
    <div class="prescription-history-sidebar">
        <h2>Active Prescriptions</h2>
        <div class="search-bar-small">
            <input type="text" id="list-search-input" placeholder="Search prescriptions...">
        </div>
        <div id="prescription-list-scroll" class="prescription-list-scroll">
            <p>Loading...</p>
        </div>
    </div>

    <!-- Main Content for Details -->
    <div id="prescription-details-content" class="prescription-details-content">
        <p>Select a prescription from the list to view details.</p>
    </div>
</div>

<!-- Link to your controller -->
<script src="/WebDev_Prescription/frontend/pharmacist/js/dispense.js"></script>

<?php
$pageContent = ob_get_clean();
require_once 'pharmacy_standard.php';
?>