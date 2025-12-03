<?php
include('../../../backend/includes/db_connect.php');
$activePage = 'dashboard';

/* counts */
$totPatients = (int) ($conn->query("SELECT COUNT(*) as c FROM patient")->fetch_assoc()['c'] ?? 0);
$totPrescriptions = (int) ($conn->query("SELECT COUNT(*) as c FROM prescription")->fetch_assoc()['c'] ?? 0);

ob_start();
?>
    <div class="card">
        <h2>Doctor Dashboard</h2>
    </div>

    <div style="display:flex; gap:18px;">
        <div class="card" style="flex:1;">
            <h3>Total Patients</h3>
            <p style="font-size:22px; font-weight:bold;"><?= $totPatients ?></p>
            <a href="patients.php">Manage patients →</a>
        </div>

        <div class="card" style="flex:1;">
            <h3>Total Prescriptions</h3>
            <p style="font-size:22px; font-weight:bold;"><?= $totPrescriptions ?></p>
            <a href="view_prescription.php">Manage prescriptions →</a>
        </div>
    </div>
<?php
$content = ob_get_clean();
include('../doctor_standard.php');
