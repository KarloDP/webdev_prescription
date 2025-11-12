<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$doctor_name = $_SESSION['doctor_name'] ?? "Doctor Name";
$activePage = $activePage ?? 'dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Doctor Dashboard</title>
    <link rel="stylesheet" href="../assets/css/layout_standard.css" />
</head>
<body>
    <!-- TOP NAVBAR -->
    <header class="top-navbar">
        <div class="logo">
            <img src="../assets/images/orange_logo.png" alt="Logo" />
            <span>MediSync Wellness</span>
        </div>
        <div class="profile">
            <span><?php echo htmlspecialchars($doctor_name); ?></span>
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
            <li class="<?php echo ($activePage == 'patients') ? 'active' : ''; ?>">
                <a href="manage_patient.php">Manage Patient</a>
            </li>
            <li class="<?php echo ($activePage == 'prescriptions') ? 'active' : ''; ?>">
                <a href="manage_prescription.php">Manage Prescription</a>
            </li>
            <li class="<?php echo ($activePage == 'profile') ? 'active' : ''; ?>">
                <a href="profile.php">Profile</a>
            </li>
            <li>
                <a href="../logout.php">Logout</a>
            </li>
        </ul>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="content">
        <?php echo isset($content) ? $content : "<h1>Welcome</h1><p>Select an option from the sidebar.</p>"; ?>
    </main>
</body>
</html>
