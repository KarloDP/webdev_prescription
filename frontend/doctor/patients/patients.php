<?php
// frontend/doctor/patients/patients.php
session_start();
require_once __DIR__ . '/../../../backend/includes/auth.php';
require_login('/webdev_prescription/login.php', ['doctor']);

// active page variable used by doctor_standard.php for nav highlighting
$activePage = 'patients';

// Resolve an integer doctor id from session (fallback 0)
$loggedDoctorId = (int)($_SESSION['user']['id'] ?? $_SESSION['doctorID'] ?? 0);

ob_start();
?>
    <div class="page-header">
        <h1>Patient List</h1>
    </div>

    <div class="patients-frame">
        <div class="controls">
            <div class="search-area">
                <input id="search-input" placeholder="Search by name or RX ID..." />
                <button id="filter-btn">Filter</button>
            </div>
            <!-- Add Patient button intentionally removed (you requested) -->
        </div>

        <div class="table-frame">
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
                <tr><td colspan="5">Loading patient data...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        // expose a doctor id constant for the JS to use
        const LOGGED_DOCTOR_ID = <?= $loggedDoctorId ?>;
    </script>

    <script src="patients.js"></script>

<?php
$content = ob_get_clean();
include(__DIR__ . '/../doctor_standard.php');
