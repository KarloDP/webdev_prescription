<?php
session_start();
$admin_name = $_SESSION['admin_name'] ?? "Admin Name";
$activePage = 'dashboard';

// Robust components include
$componentsFile = __DIR__ . '/../includes/components.php';
if (!file_exists($componentsFile)) {
    die('Missing includes/components.php at: ' . $componentsFile);
}
require_once $componentsFile;

// Diagnostic if function still missing
if (!function_exists('renderStatBox')) {
    $info = [
        'path' => realpath($componentsFile) ?: $componentsFile,
        'size' => file_exists($componentsFile) ? filesize($componentsFile) : 'n/a',
    ];
    die('renderStatBox() not defined after including components.php. Info: ' . json_encode($info));
}

// Placeholder stats — replace with DB queries
$totalDoctors = 4;
$totalHospitals = 4;
$totalMedicines = 4;
$totalApplications = 4;
$totalReports = 4;
$totalRequests = 4;

ob_start();
?>
<h1>Welcome, <?php echo htmlspecialchars($admin_name); ?>!!</h1>
<p>Here’s an overview of your system activity:</p>

<div class="stats-container">
    <?php
    // use project-root or admin-relative links as needed
    renderStatBox($totalDoctors, "Total Doctors", "/WebDev_Prescription/admin/manage_users.php");
    renderStatBox($totalHospitals, "Total Hospitals", "/WebDev_Prescription/admin/manage_hospitals.php");
    renderStatBox($totalMedicines, "Total Medicines", "/WebDev_Prescription/admin/manage_medicines.php");
    renderStatBox($totalApplications, "Doctor Applications", "/WebDev_Prescription/admin/manage_applications.php");
    renderStatBox($totalReports, "System Reports", "/WebDev_Prescription/admin/system_logs.php");
    renderStatBox($totalRequests, "Doctor Requests", "/WebDev_Prescription/admin/manage_prescriptions.php");
    ?>
</div>
<?php
$content = ob_get_clean();

// Include layout (ensure this file exists)
$layoutFile = __DIR__ . '/../layout_standard.php';
if (!file_exists($layoutFile)) {
    die('Missing layout_standard.php at: ' . $layoutFile);
}
require_once $layoutFile;
?>