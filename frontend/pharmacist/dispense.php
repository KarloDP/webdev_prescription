<?php
// Note: auth.php is included by pharmacy_standard.php, so we don't need to include it here

$activePage = 'dispense';
$pageStyles = '<link rel="stylesheet" href="../css/pharmacist/dispense.css">';
$pageScripts = '<script src="js/dispense.js" defer></script>';

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

<?php
$pageContent = ob_get_clean();
require_once __DIR__ . '/pharmacy_standard.php';
?>