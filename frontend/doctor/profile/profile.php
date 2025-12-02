<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../login.php");
    exit();
}

include "../doctor_standard.php";
?>

<div class="content">
    <h2>Profile</h2>

    <p><strong>Name:</strong> <span id="doctor_name"></span></p>

    <button id="logoutBtn">Logout</button>
</div>

<script src="profile.js"></script>
