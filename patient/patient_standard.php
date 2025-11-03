<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$patient_name = $_SESSION['patient_name'] ?? "Patient Name";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Patient Dashboard</title>
    <link rel="stylesheet" href="../assets/css/patient_standard.css" />
</head>
<body>
    <!-- TOP NAVBAR -->
    <header class="top-navbar">
        <div class="logo">
            <img src="../assets/images/orange_logo.png" alt="Logo" />
            <span>MediSync Wellness</span>
        </div>
        <div class="profile">
            <span><?php echo htmlspecialchars($patient_name); ?></span>
            <img src="../assets/images/user.png" class="avatar" alt="Profile">
            <div class="menu-icon">⋮</div>
        </div>
    </header>

    <!-- SIDEBAR -->
    <aside class="sidebar">
        <ul>
            <li class="<?php echo ($activePage == 'dashboard') ? 'active' : ''; ?>">
                <a href="dashboard.php">Dashboard</a>
            </li>
            <li class="<?php echo ($activePage == 'history') ? 'active' : ''; ?>">
                <a href="history.php">History</a>
            </li>
            <li class="<?php echo ($activePage == 'medications') ? 'active' : ''; ?>">
                <a href="medications.php">Medications</a>
            </li>
            <li class="<?php echo ($activePage == 'pharmacies') ? 'active' : ''; ?>">
                <a href="pharmacies.php">Pharmacies</a>
            </li>
            <li>
                <a href="../logout.php">Logout</a>
            </li>
        </ul>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="content">
        <?php echo $content; ?>
    </main>
</body>
</html>
