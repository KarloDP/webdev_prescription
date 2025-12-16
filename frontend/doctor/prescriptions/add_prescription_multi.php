<?php
require_once(__DIR__ . '/../../../backend/includes/auth.php');
require_login('../login.php', ['doctor']);

$activePage = 'prescriptions';
ob_start();
?>

<div class="prescriptions-page">

    <div class="page-header" style="display:flex; justify-content:space-between; align-items:center;">
        <h1>Add Prescription</h1>
        <a href="prescriptions.php" class="btn btn-secondary">← Back to Prescriptions</a>
    </div>

    <!-- Prescription Info -->
    <div class="section-container">
        <label>Patient</label>
        <input type="text" id="patient-search" placeholder="Search patient...">
        <select id="patient-select">
            <option value="">Select patient</option>
        </select>

        <label>Issue Date</label>
        <input type="date" id="issue-date">

        <label>Expiration Date</label>
        <input type="date" id="expiration-date">

        <label>Notes</label>
        <textarea id="notes" rows="3"></textarea>
    </div>

    <!-- Medications -->
    <div class="section-container">
        <h2>Medications</h2>
        <p style="font-size:0.9em;color:#555;margin-bottom:8px;">
            Each row represents a single medication entry.
        </p>

        <table class="table-base">
            <thead>
                <tr>
                    <th>Medication</th>
                    <th>Dosage</th>
                    <th>Frequency</th>
                    <th>Duration</th>
                    <th>Qty</th>
                    <th>Refill Date</th>
                    <th>Instructions</th>
                    <th></th>
                </tr>
            </thead>
            <tbody id="medications-body"></tbody>
        </table>

        <button id="add-med-row" class="btn">+ Add Medication</button>
    </div>

    <div class="section-container">
        <button id="save-prescription" class="btn btn-primary">
            Save Prescription
        </button>
    </div>

</div>

<script src="add_prescription_multi.js" defer></script>

<?php
$content = ob_get_clean();
include(__DIR__ . '/../doctor_standard.php');
?>