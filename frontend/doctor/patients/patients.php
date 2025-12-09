<?php
require_once(__DIR__ . '/../../../backend/includes/auth.php');
require_login('/webdev_prescription/login.php', ['doctor']);

$activePage = 'patients';
ob_start();
?>

<div class="patients-page">
    <div class="page-header">
        <h1 class="page-title">Patient List</h1>
        <div class="filter-controls">
            <input type="text" id="search-input" class="search-box" placeholder="Search by name or RX ID...">
            <button id="toggle-filter-btn" class="btn btn-outline">Filter</button>
        </div>
    </div>

    <!-- Filter Panel -->
    <div id="filter-panel" class="filter-panel hidden">
        <div class="filter-group">
            <label for="filter-gender">Gender</label>
            <select id="filter-gender" name="gender">
                <option value="">Any</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
                <option value="Other">Other</option>
            </select>
        </div>
        <div class="filter-group">
            <label>Last Visit Date</label>
            <div class="date-range-inputs">
                <input type="date" id="filter-date-from" name="date-from">
                <span>-</span>
                <input type="date" id="filter-date-to" name="date-to">
            </div>
        </div>
        <div class="filter-actions">
            <button id="apply-filters-btn" class="btn btn-primary">Apply</button>
            <button id="clear-filters-btn" class="btn btn-secondary">Clear</button>
        </div>
    </div>

    <div class="table-frame">
        <a href="add_patient.php" class="btn btn-primary" style="margin-bottom: 20px;">+ Add Patient</a>

        <table class="table-base">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Age</th>
                    <th>Gender</th>
                    <th>Last Visit</th>
                    <th>Last Prescription</th>
                </tr>
            </thead>
            <tbody id="patients-table-body">
                <tr>
                    <td colspan="5" class="loading-cell">Loading patient data...</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<script src="patients.js">
    const LOGGED_DOCTOR_ID = <?= $_SESSION['user']['doctorID'] ?? 'null' ?>;
    const LOGGED_DOCTOR_NAME = "<?= $_SESSION['user']['name'] ?? '' ?>";
</script>

<?php
$content = ob_get_clean(); 
include(__DIR__ . '/../doctor_standard.php');
?>