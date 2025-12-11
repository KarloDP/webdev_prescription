<?php
require_once __DIR__ . '/../../backend/includes/auth.php';
$user = require_role(['pharmacist']);

$activePage = 'patients';
$pageStyles = '<link rel="stylesheet" href="../css/pharmacist/patients.css">';

ob_start();
?>
<div class="patients-container">
  <header class="patients-header">
    <h1>Patients</h1>
    <div class="search-bar">
      <input id="patient-search" type="text" placeholder="Search for anything here...">
      <button id="patient-search-btn" type="button"><i class="fas fa-search"></i></button>
    </div>
  </header>

  <div class="patients-table-container">
    <table class="patients-table">
      <thead>
        <tr>
          <th>Patient</th>
          <th>Contact Info</th>
          <th>Prescription Id</th>
          <th>Address</th>
          <th>Doctor</th>
        </tr>
      </thead>
      <tbody id="patients-table-body">
        <tr><td colspan="5">Loading...</td></tr>
      </tbody>
    </table>
  </div>
</div>

<script src="js/patients.js"></script>
<?php
$pageContent = ob_get_clean();
require_once 'pharmacy_standard.php';
?>