<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$doctor_name = $_SESSION['doctor_name'] ?? "Doctor";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediSync Doctor Panel</title>
    <link rel="stylesheet" href="../assets/css/layout_standard.css">
</head>
<body>
<header class="top-navbar">
    <div class="logo">
        <img src="../assets/images/orange_logo.png" alt="Logo" style="height:40px;">
        <span>MediSync Wellness</span>
    </div>
    <div class="profile">
        <span><?php echo htmlspecialchars($doctor_name); ?></span>
        <img src="../assets/images/user.png" class="avatar" alt="Profile" style="height:40px;">
        <div class="menu-icon">⋮</div>
    </div>
</header>
