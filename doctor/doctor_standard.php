<?php
// doctor_standard.php - simple wrapper layout for doctor pages
// Expects: $activePage (string) and $content (HTML string)

if (!isset($activePage)) $activePage = '';
if (!isset($content)) $content = '';

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>MediSync Wellness - Doctor</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <style>
        body { margin:0; font-family: Arial, Helvetica, sans-serif; }
        .sidebar {
            width:220px; background:#16482f; color:#fff; height:100vh; position:fixed; padding:20px 10px;
        }
        .sidebar a { color:#fff; text-decoration:none; display:block; padding:10px 12px; border-radius:6px; margin-bottom:6px;}
        .sidebar a.active { background:#2a6b48; font-weight:bold; }
        .topbar { height:60px; background:#214d39; color:#fff; padding:10px 20px; margin-left:220px; display:flex; align-items:center; justify-content:space-between; }
        .content { margin-left:220px; padding:30px; background:#f6f6f6; min-height:calc(100vh - 60px); }
        .card { background:#fff; padding:18px; border-radius:8px; box-shadow:0 1px 3px rgba(0,0,0,0.05); margin-bottom:18px; }
        .btn { display:inline-block; padding:8px 12px; background:#28a745; color:#fff; text-decoration:none; border-radius:6px; }
        table { width:100%; border-collapse:collapse; }
        th, td { padding:8px; border-bottom:1px solid #eee; text-align:left; }
        .danger { background:#dc3545; color:#fff; padding:6px 10px; border-radius:5px; text-decoration:none;}
    </style>
</head>
<body>

<div class="sidebar">
    <h3 style="margin:0 0 12px 6px;">MediSync Wellness</h3>
    <a href="dashboard.php" class="<?= $activePage==='dashboard' ? 'active' : '' ?>">Dashboard</a>
    <a href="patients.php" class="<?= $activePage==='patients' ? 'active' : '' ?>">Patients</a>
    <a href="view_prescription.php" class="<?= $activePage==='prescriptions' ? 'active' : '' ?>">Prescriptions</a>
    <a href="profile.php" class="<?= $activePage==='profile' ? 'active' : '' ?>">Profile</a>
    <a href="../logout.php" style="margin-top:20px;">Logout</a>
</div>

<div class="topbar">
    <div style="font-weight:bold;">Doctor Dashboard</div>
    <div style="display:flex;align-items:center;">
        <div style="margin-right:12px;">Doctor</div>
        <div style="width:36px;height:36px;border-radius:50%;background:#e74c3c;"></div>
    </div>
</div>

<div class="content">
    <?= $content ?>
</div>

</body>
</html>
