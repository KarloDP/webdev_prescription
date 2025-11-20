<?php
// includes/auth.php
// Small authentication/session helper for patient pages.
// Usage:
//   require_once __DIR__ . '/auth.php';
//   require_login();            // redirect to login if not authenticated
//   set_user_session($row);    // call after successful DB auth (row with patientID, firstName, lastName, email)
//   clear_user_session();      // logout helper
//
// This file intentionally does not create DB connections. Use includes/db_connect.php
// in scripts that need DB access.

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Set canonical session values for a logged-in patient.
 * @param array $row Associative array containing at minimum: patientID, firstName, lastName, email (optional)
 * @return void
 */
function set_user_session(array $row): void
{
    // regenerate session id to mitigate fixation (call this only at login)
    session_regenerate_id(true);

    $_SESSION['patientID'] = isset($row['patientID']) ? (int)$row['patientID'] : null;

    $first = $row['firstName'] ?? ($row['first_name'] ?? '');
    $last  = $row['lastName']  ?? ($row['last_name']  ?? '');
    $name  = trim($first . ' ' . $last);

    $_SESSION['patient_name']  = $name !== '' ? $name : ($_SESSION['patient_name'] ?? 'Patient');

    if (isset($row['email'])) {
        $_SESSION['patient_email'] = $row['email'];
    }
}

/**
 * Convenience: load patient by ID and populate session keys.
 * Requires an active $conn (mysqli).
 * Safe to call where you have a DB connection and want to canonicalize session.
 *
 * @param mysqli $conn
 * @param int $patientID
 * @return bool true on success (session set), false otherwise
 */
function set_user_session_from_db(mysqli $conn, int $patientID): bool
{
    if ($patientID <= 0) {
        return false;
    }

    $stmt = $conn->prepare('SELECT patientID, firstName, lastName, email FROM patient WHERE patientID = ? LIMIT 1');
    if ($stmt === false) {
        error_log('set_user_session_from_db prepare failed: ' . $conn->error);
        return false;
    }

    $stmt->bind_param('i', $patientID);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res && $res->num_rows === 1) {
        $row = $res->fetch_assoc();
        set_user_session($row);
        $stmt->close();
        return true;
    }

    $stmt->close();
    return false;
}

/**
 * Clear all application-specific session data for the authenticated user.
 * Does not destroy session entirely (keeps other session data intact).
 * @return void
 */
function clear_user_session(): void
{
    unset($_SESSION['patientID'], $_SESSION['patient_name'], $_SESSION['patient_email']);
}

/**
 * Returns true when a patient is logged in.
 * @return bool
 */
function is_logged_in(): bool
{
    return !empty($_SESSION['patientID']);
}

/**
 * Ensure the user is logged in as a patient. If not, redirect to login page and exit.
 * @param string $loginPath Relative path to login page (default ../TestLoginPatient.php)
 * @return void
 */
function require_login(string $loginPath = '../TestLoginPatient.php'): void
{
    if (!is_logged_in()) {
        header('Location: ' . $loginPath);
        exit;
    }
}

/**
 * Convenience: try to resolve session patient from email if patientID missing.
 * Requires an active $conn (mysqli) from includes/db_connect.php.
 * Safe to call only when $conn is available.
 *
 * Example:
 *   require_once __DIR__ . '/db_connect.php';
 *   resolve_session_from_email($conn);
 *
 * @param mysqli $conn
 * @return void
 */
function resolve_session_from_email(mysqli $conn): void
{
    if (!empty($_SESSION['patientID']) || empty($_SESSION['patient_email'])) {
        return;
    }

    $email = $_SESSION['patient_email'];
    $stmt = $conn->prepare('SELECT patientID, firstName, lastName, email FROM patient WHERE email = ? LIMIT 1');
    if ($stmt === false) {
        return;
    }

    $stmt->bind_param('s', $email);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res && $res->num_rows === 1) {
        $row = $res->fetch_assoc();
        set_user_session($row);
    }
    $stmt->close();
}

