<?php

$activePage = 'patients';
session_start();


if (!isset($_SESSION['user'])) {
    header("Location: ../../../login.php");
    exit();
}

include "../doctor_standard.php";
?>

<div class="content">
    <h2>Add Patient</h2>

    <form id="addPatientForm">
        <label>Full Name:</label>
        <input type="text" id="full_name" required>

        <label>Age:</label>
        <input type="number" id="age" required>

        <label>Gender:</label>
        <select id="gender" required>
            <option value="">Select</option>
            <option value="Male">Male</option>
            <option value="Female">Female</option>
        </select>

        <label>Address:</label>
        <input type="text" id="address" required>

        <label>Contact:</label>
        <input type="text" id="contact" required>

        <button type="submit">Add Patient</button>
    </form>

    <p id="message"></p>
</div>

<script src="add_patient.js">
    const LOGGED_DOCTOR_ID = <?= $_SESSION['user']['doctorID'] ?? 'null' ?>;
    const LOGGED_DOCTOR_NAME = "<?= $_SESSION['user']['name'] ?? '' ?>";
</script>
