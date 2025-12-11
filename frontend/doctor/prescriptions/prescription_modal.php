<?php
// filepath: c:\wamp64\www\WebDev_Prescription\frontend\doctor\prescriptions\prescription_modal.php
?>

<!-- Prescription Modal Popup -->
<div id="prescription-modal" class="modal-overlay" style="display:none;">
    <div class="modal-panel">
        <!-- Close Button -->
        <button class="modal-close-btn" title="Close">&times;</button>

        <!-- Title -->
        <h2 class="modal-title">Add Prescription</h2>

        <!-- Tabs -->
        <div class="modal-tabs">
            <button type="button" class="tab-btn active" data-tab="existing">Existing Patient</button>
            <button type="button" class="tab-btn" data-tab="new">New Patient</button>
        </div>

        <!-- Existing Patient Tab -->
        <div class="modal-tab-content" id="tab-existing">
            <label for="search-patient">Search Patient</label>
            <input type="text" id="search-patient" class="modal-input" placeholder="Type patient name...">
            <input type="hidden" id="selected-patient-id">
            <div id="patient-search-results" class="search-results"></div>
        </div>

        <!-- New Patient Tab -->
        <div class="modal-tab-content" id="tab-new" style="display:none;">
            <label for="new-patient-full-name">Patient Name</label>
            <input type="text" id="new-patient-full-name" class="modal-input" placeholder="Full name">

            <label for="new-patient-age">Age</label>
            <input type="number" id="new-patient-age" class="modal-input" min="0" max="120" placeholder="e.g. 30">

            <label for="new-patient-gender">Gender</label>
            <select id="new-patient-gender" class="modal-input">
                <option value="">Select</option>
                <option value="Female">Female</option>
                <option value="Male">Male</option>
                <option value="Other">Other</option>
            </select>

            <label for="new-patient-email">Email</label>
            <input type="email" id="new-patient-email" class="modal-input" placeholder="e.g. patient@example.com">

            <label for="new-patient-contact">Contact Number</label>
            <input type="tel" id="new-patient-contact" class="modal-input" placeholder="e.g. +1234567890">
        </div>

        <hr>

        <!-- Prescription Details -->
        <div class="modal-section">
            <label for="brandNameInput">Brand Name</label>
            <input type="text" id="brandNameInput" class="modal-input" placeholder="e.g. Paracetamol">

            <label for="genericNameInput">Generic Name</label>
            <input type="text" id="genericNameInput" class="modal-input" placeholder="e.g. Acetaminophen">

            <label for="prescription-dosage">Dosage</label>
            <input type="text" id="prescription-dosage" class="modal-input" placeholder="e.g. 500 mg">

            <label for="prescription-frequency">Frequency</label>
            <input type="text" id="prescription-frequency" class="modal-input" placeholder="e.g. Twice daily">

            <label for="prescription-start">Start Date</label>
            <input type="date" id="prescription-start" class="modal-input">

            <label for="prescription-notes">Notes</label>
            <textarea id="prescription-notes" class="modal-input" rows="3" placeholder="Additional instructions..."></textarea>
        </div>

        <!-- Actions -->
        <div class="modal-actions">
            <button type="button" class="modal-save-btn">Save</button>
            <button type="button" class="modal-cancel-btn">Cancel</button>
        </div>
    </div>
</div>