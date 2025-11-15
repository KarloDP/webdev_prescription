<?php
$activePage = $activePage ?? '';
?>
<aside class="sidebar">
    <ul>
        <li class="<?php echo ($activePage == 'dashboard') ? 'active' : ''; ?>">
            <a href="dashboard.php">Dashboard</a>
        </li>
        <li class="<?php echo ($activePage == 'patients') ? 'active' : ''; ?>">
            <a href="patients.php">Patients</a>
        </li>
        <li class="<?php echo ($activePage == 'prescriptions') ? 'active' : ''; ?>">
            <a href="view_prescription.php">Prescriptions</a>
        </li>
        <li class="<?php echo ($activePage == 'profile') ? 'active' : ''; ?>">
            <a href="profile.php">Profile</a>
        </li>
        <li>
            <a href="../logout.php">Logout</a>
        </li>
    </ul>
</aside>
<main class="content">
