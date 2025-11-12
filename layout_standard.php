<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Determine user name from session (admin, doctor, etc.)
$user_name = $_SESSION['admin_name']
    ?? $_SESSION['doctor_name']
    ?? $_SESSION['pharmacist_name']
    ?? $_SESSION['patient_name']
    ?? "User";

// Ensure $activePage and $sidebarItems are set
$activePage = $activePage ?? 'dashboard';
$sidebarItems = $sidebarItems ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?php echo ucfirst($activePage); ?> | MediSync Wellness</title>
    <link rel="stylesheet" href="<?php echo dirname($_SERVER['PHP_SELF']); ?>/../assets/css/layout_standard.css" />
</head>
<body>
    <!-- TOP NAVBAR -->
    <header class="top-navbar">
        <div class="logo">
            <img src="<?php echo dirname($_SERVER['PHP_SELF']); ?>/../assets/images/orange_logo.png" alt="Logo" />
            <span>MediSync Wellness</span>
        </div>
        <div class="profile">
            <span><?php echo htmlspecialchars($user_name); ?></span>
            <img src="<?php echo dirname($_SERVER['PHP_SELF']); ?>/../assets/images/user.png" class="avatar" alt="Profile">
            <div class="menu-icon">⋮</div>
        </div>
    </header>

    <!-- SIDEBAR -->
    <aside class="sidebar">
        <ul>
            <?php foreach ($sidebarItems as $key => $label): ?>
                <li class="<?php echo ($activePage === $key) ? 'active' : ''; ?>">
                    <a href="<?php echo $key; ?>.php"><?php echo htmlspecialchars($label); ?></a>
                </li>
            <?php endforeach; ?>
            <li><a href="../logout.php">Logout</a></li>
        </ul>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="content">
        <?php echo isset($content) ? $content : "<h1>Welcome</h1><p>Select an option from the sidebar.</p>"; ?>
    </main>
</body>
</html>
