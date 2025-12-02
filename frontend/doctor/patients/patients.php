<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../login.php");
    exit();
}

include "../doctor_standard.php";
?>

<div class="content">
    <h2>Patients List</h2>

    <table id="patientsTable">
        <thead>
        <tr>
            <th>Patient ID</th>
            <th>Full Name</th>
            <th>Age</th>
            <th>Gender</th>
            <th>Action</th>
        </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>

<script src="patients.js"></script>
