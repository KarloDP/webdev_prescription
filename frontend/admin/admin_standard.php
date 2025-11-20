<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$admin_name = $_SESSION['admin_name'] ?? "Admin Name";
$activePage = $activePage ?? 'dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin Dashboard</title>
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
            <span><?php echo htmlspecialchars($admin_name); ?></span>
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
            <li class="<?php echo ($activePage == 'users') ? 'active' : ''; ?>">
                <a href="manage_users.php">Manage Users</a>
            </li>
            <li class="<?php echo ($activePage == 'medicines') ? 'active' : ''; ?>">
                <a href="manage_medicines.php">Manage Medicines</a>
            </li>
            <li class="<?php echo ($activePage == 'prescriptions') ? 'active' : ''; ?>">
                <a href="manage_prescriptions.php">Manage Prescriptions</a>
            </li>
            <li class="<?php echo ($activePage == 'logs') ? 'active' : ''; ?>">
                <a href="system_logs.php">System Logs</a>
            </li>
            <li class="<?php echo ($activePage == 'settings') ? 'active' : ''; ?>">
                <a href="system_settings.php">System Settings</a>
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
