<?php
function renderDashboardCards($totals) {
    ?>
    <div class="dashboard">
      <div class="card">
        <h3>Active Doctors</h3>
        <p><?= $totals['doctors_active'] ?></p>
        <a href="users.php?filter=active">View Active Doctors</a>
      </div>
      <div class="card">
        <h3>Pending Doctors</h3>
        <p><?= $totals['doctors_pending'] ?></p>
        <a href="users.php?filter=pending">View Pending Requests</a>
      </div>
      <div class="card">
        <h3>Total Medicines</h3>
        <p><?= $totals['medicines'] ?></p>
        <a href="database.php?view=medicines">View Medicines</a>
      </div>
      <div class="card">
        <h3>Total Prescriptions</h3>
        <p><?= $totals['prescriptions'] ?></p>
        <a href="database.php?view=prescriptions">View Prescriptions</a>
      </div>
      <div class="card">
        <h3>Total Pharmacies</h3>
        <p><?= $totals['pharmacys'] ?></p>
        <a href="database.php?view=pharmacys">View Pharmacies</a>
      </div>
    </div>
    <?php
}
