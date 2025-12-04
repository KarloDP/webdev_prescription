<?php

if (!isset($_SESSION['user'])) {
    header("Location: ../../../login.php");
    exit();
}

include "../doctor_standard.php";

$patient_id = $_GET['id'] ?? null;
?>

<div class="content">
    <h2>Patient Details</h2>
    <div id="details"></div>
</div>

<script>
    const patientId = "<?php echo $patient_id; ?>";
</script>
<script src="patients_details.js"></script>
