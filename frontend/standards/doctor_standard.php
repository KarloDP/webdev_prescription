<?php
// doctor_standard.php - layout wrapper
if (!isset($activePage)) $activePage = '';
if (!isset($content)) $content = '';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>MediSync Wellness - Doctor</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <style>
        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            background:#f6f6f6;
        }

        /* SIDEBAR */
        .sidebar {
            width: 230px;
            background: #16482f;
            color: #fff;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            padding: 20px 10px;
            box-sizing:border-box;
        }

        .sidebar h3 { margin-left: 8px; margin-bottom:20px; }

        .sidebar a {
            color: #fff;
            text-decoration: none;
            padding: 10px;
            display: block;
            border-radius: 6px;
            margin-bottom: 5px;
        }
        .sidebar a.active {
            background: #2a6b48;
            font-weight: bold;
        }

        /* TOPBAR */
        .topbar {
            height: 60px;
            background: #214d39;
            color: #fff;
            padding: 15px 20px;
            margin-left: 230px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-sizing:border-box;
        }

        /* PAGE CONTENT */
        .content {
            margin-left: 230px;
            padding: 25px;
            padding-top: 20px;
            min-height: calc(100vh - 60px);
            box-sizing: border-box;
        }

        .card {
            background: #fff;
            border-radius: 8px;
            padding: 18px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.08);
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 10px;
            border-bottom: 1px solid #eee;
            text-align: left;
        }
        th { background: #f3f3f3; }

        .btn {
            background: #28a745;
            padding: 8px 12px;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            margin-right: 6px;
            display:inline-block;
        }

        .btn-danger {
            background: #dc3545;
        }
    </style>
</head>

<body>

<div class="sidebar">
    <h3>MediSync Wellness</h3>

    <a href="dashboard.php" class="<?= $activePage === 'dashboard' ? 'active' : '' ?>">Dashboard</a>
    <a href="patients.php" class="<?= $activePage === 'patients' ? 'active' : '' ?>">Patients</a>
    <a href="view_prescription.php" class="<?= $activePage === 'prescriptions' ? 'active' : '' ?>">Prescriptions</a>
    <a href="profile.php" class="<?= $activePage === 'profile' ? 'active' : '' ?>">Profile</a>
    <a href="../logout.php" style="margin-top: 20px;">Logout</a>
</div>

<div class="topbar">
    <strong>Doctor Dashboard</strong>
    <div style="display:flex;align-items:center;">
        <span style="margin-right:10px;">Doctor</span>
        <div style="width:36px;height:36px;border-radius:50%;background:#e74c3c;"></div>
    </div>
</div>

<div class="content">
    <?= $content ?>
</div>

</body>
</html>