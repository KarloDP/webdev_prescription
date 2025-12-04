<?php
require_once(__DIR__ . '/../../../backend/includes/auth.php');
require_login('/webdev_prescription/login.php', ['doctor']);

$activePage = 'prescriptions';
ob_start(); // Start capturing HTML output
?>

<div class="prescriptions-page">
    <div class="page-header">
        <h1 class="page-title">Prescriptions</h1>
        <button id="add-prescription-btn" class="btn btn-primary">+ Add Prescription</button>
    </div>

    <!-- Section 1: Patient Information -->
    <div class="section-container">
        <h2 class="section-title">Patient Information</h2>
        <div class="table-frame">
            <table class="table-base">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Age</th>
                        <th>Gender</th>
                        <th>Contact</th>
                    </tr>
                </thead>
                <tbody id="patients-table-body">
                    <tr><td colspan="4" class="loading-cell">Loading patients...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Section 2: Active Prescriptions -->
    <div class="section-container">
        <h2 class="section-title">Active Prescriptions</h2>
        <div class="table-frame">
            <table class="table-base">
                <thead>
                    <tr>
                        <th>Patient Name</th>
                        <th>Medicine</th>
                        <th>Dosage</th>
                        <th>Frequency</th>
                        <th>Start Date</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody id="active-prescriptions-body">
                    <tr><td colspan="6" class="loading-cell">Loading active prescriptions...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Section 3: Prescription History -->
    <div class="section-container">
        <h2 class="section-title">Prescription History</h2>
        <div class="table-frame">
            <table class="table-base">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Medicine</th>
                        <th>Dosage</th>
                        <th>Status</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody id="history-prescriptions-body">
                    <tr><td colspan="5" class="loading-cell">Loading history...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="prescriptions.js"></script>

<?php
$content = ob_get_clean();
include(__DIR__ . '/../doctor_standard.php');
?>