<?php
// filepath: c:\wamp64\www\WebDev_Prescription\frontend\pharmacist\audit_logs.php
$activePage = 'logs';
$pageStyles  = '<link rel="stylesheet" href="/frontend/css/pharmacist/audit_logs.css">';
$pageScripts = '<script src="/frontend/pharmacist/js/audit_logs.js" defer></script>';

ob_start();
?>
<div class="audit-logs-container">
    <div class="audit-header">
        <h1><i class="fas fa-history"></i> Audit Logs</h1>
        <p class="subtitle">Track all system activities and user actions</p>
    </div>

    <div class="audit-filters">
        <input type="text" id="filterRole" class="filter-input" placeholder="Filter by role...">
        <input type="text" id="filterAction" class="filter-input" placeholder="Filter by action...">
        <input type="text" id="filterUser" class="filter-input" placeholder="Filter by user ID...">
        <button id="clearFilters" class="btn-clear">Clear Filters</button>
    </div>

    <div id="audit-log-table" class="table-frame">
        <p class="loading"><i class="fas fa-spinner fa-spin"></i> Loading audit logs...</p>
    </div>

    <div id="pagination" class="pagination-container"></div>
</div>
<?php
$pageContent = ob_get_clean();
require_once __DIR__ . '/pharmacy_standard.php';
?>

