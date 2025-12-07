<?php
require_once __DIR__ . '/../../backend/includes/auth.php';
$user = require_role(['pharmacist']);

$prescriptionId = $_GET['id'] ?? null;
if (!$prescriptionId) {
    header('Location: prescription.php');
    exit();
}

$activePage = 'prescription';
$pageStyles = '<link rel="stylesheet" href="../css/pharmacist/edit_prescription.css">';

ob_start();
?>
<div class="edit-prescription-container" data-rx-id="<?= htmlspecialchars($prescriptionId) ?>">
    <header class="edit-prescription-header">
        <h1 id="rx-title">Loading Prescription...</h1>
        <div class="action-buttons">
            <a href="prescription.php" class="btn-back"><i class="fas fa-arrow-left"></i> Back to List</a>
        </div>
    </header>

    <p id="rx-message" class="message" hidden></p>

    <form id="rx-form" class="edit-form">
        <!-- This section will be populated by JavaScript -->
        <p id="form-loader">Loading form...</p>
    </form>
</div>

<script src="js/edit_prescription.js"></script>
<?php
$pageContent = ob_get_clean();
require_once 'pharmacy_standard.php';
?>