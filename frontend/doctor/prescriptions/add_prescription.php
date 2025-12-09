<?php
$activePage = 'prescriptions';
ob_start();
?>

<h1>Add Prescription</h1>

<div class="form-container">
    <form id="addPrescriptionForm">

        <label>Patient ID:</label>
        <input type="number" id="patientID" required>

        <label>Start Date:</label>
        <input type="date" id="startDate" required>

        <h3>Medicines</h3>

        <div id="medicineList">
            <div class="medicine-row">
                <input type="text" class="medicine" placeholder="Medicine" required>
                <input type="text" class="dosage" placeholder="Dosage" required>
                <input type="text" class="frequency" placeholder="Frequency" required>
                <input type="text" class="notes" placeholder="Notes">
                <button type="button" class="removeRow">X</button>
            </div>
        </div>

        <button type="button" id="addRowBtn">+ Add Medicine</button>

        <button type="submit">Save Prescription</button>
    </form>

    <p id="result"></p>
</div>

<script>
    const LOGGED_DOCTOR_ID = <?= $_SESSION['user']['doctorID'] ?>;
</script>


<script src="add_prescription.js">
    const LOGGED_DOCTOR_ID = <?= $_SESSION['user']['doctorID'] ?? 'null' ?>;
    const LOGGED_DOCTOR_NAME = "<?= $_SESSION['user']['name'] ?? '' ?>";
</script>

<?php
$content = ob_get_clean();
include(__DIR__ . '/../doctor_standard.php');
?>
