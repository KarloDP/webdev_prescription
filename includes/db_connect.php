<?php
// includes/patient_bootstrap.php
// Standard patient bootstrap to use on every protected patient page.
// - Starts session if needed
// - Loads auth and DB connect
// - Enforces login via require_login()
// - Ensures patient_name is present in session (fallback to DB)
// - Exposes $patientID and $patientName for page templates

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db_connect.php';

// Ensure the user is logged in; require_login redirects if not
require_login();

// If session lacks patient_name but has patientID, populate it from DB
if (empty($_SESSION['patient_name']) && !empty($_SESSION['patientID'])) {
    // set_user_session_from_db will set session values if successful
    if (function_exists('set_user_session_from_db')) {
        set_user_session_from_db($conn, (int) $_SESSION['patientID']);
    } else {
        // fallback manual resolve if helper not present
        $pid = (int) $_SESSION['patientID'];
        if ($pid > 0) {
            $s = $conn->prepare('SELECT firstName, lastName FROM patient WHERE patientID = ? LIMIT 1');
            if ($s) {
                $s->bind_param('i', $pid);
                $s->execute();
                $r = $s->get_result();
                if ($r && $r->num_rows === 1) {
                    $row = $r->fetch_assoc();
                    $_SESSION['patient_name'] = trim(($row['firstName'] ?? '') . ' ' . ($row['lastName'] ?? ''));
                }
                $s->close();
            }
        }
    }
}

// Expose local variables pages expect
$patientID = isset($_SESSION['patientID']) ? (int) $_SESSION['patientID'] : 0;
$patientName = $_SESSION['patient_name'] ?? 'Patient';
