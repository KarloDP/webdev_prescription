<?php
//code below is chatGPT generated, based on script made by alwin and christian
//needs to be review for issues***********



// includes/auth.php
//
// Central authentication/session helper for all user types:
//   - patient
//   - admin
//   - doctor
//   - pharmacist
//
// Session shape (canonical):
//   $_SESSION['user'] = [
//       'role'  => 'patient' | 'admin' | 'doctor' | 'pharmacist',
//       'id'    => 123,
//       'name'  => 'Alice Johnson',
//       'email' => 'alice@example.com' | null
//   ];
//
// For backwards compatibility with older patient-only code, we still set:
//   $_SESSION['patientID'], $_SESSION['patient_name'], $_SESSION['patient_email']
// when the role is 'patient'.
//
// Typical usage in API scripts:
//   require_once __DIR__ . '/auth.php';
//   $user  = require_user();
//   $role  = $user['role'];
//   $userID = (int)$user['id'];
//
// Typical usage in normal page scripts:
//   require_once __DIR__ . '/auth.php';
//   require_login('../TestLoginPatient.php', ['patient']); // optional role restriction
//
// This file does NOT create DB connections. Use includes/db_connect.php where needed.

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Map a role name to its primary ID field in the corresponding table.
 * E.g. 'patient' => 'patientID', 'admin' => 'adminID', etc.
 */
function auth_id_field_for_role(string $role): ?string
{
    static $map = [
        'patient'    => 'patientID',
        'admin'      => 'adminID',
        'doctor'     => 'doctorID',
        'pharmacist' => 'pharmacistID',
    ];

    return $map[$role] ?? null;
}

function redirect_based_on_role(string $role): void {
    switch ($role) {
        case 'patient':
            header("Location: ../patient/dashboard/dashboard.php");
            break;
        case 'doctor':
            header("Location: ../doctor/dashboard.php");
            break;
        case 'admin':
            header("Location: ../admin/dashboard.php");
            break;
        case 'pharmacist':
            header("Location: ../pharmacist/dashboard.php");
            break;
    }
    exit;
}

/**
 * Set canonical session values for a logged-in user of any role.
 *
 * @param string $role One of: 'patient', 'admin', 'doctor', 'pharmacist'
 * @param array  $row  DB row with at least the role's ID column
 *                     (patientID/adminID/doctorID/pharmacistID),
 *                     and ideally firstName/lastName/email.
 */
function set_user_session(string $role, array $row): void
{
    // Normalize role
    $role = strtolower($role);

    $idField = auth_id_field_for_role($role);
    $id = ($idField !== null && isset($row[$idField])) ? (int)$row[$idField] : 0;

    // Try a few possible column names for first/last
    $first = $row['firstName']  ?? $row['first_name']  ?? '';
    $last  = $row['lastName']   ?? $row['last_name']   ?? '';
    $name  = trim($first . ' ' . $last);

    $email = $row['email'] ?? null;

    // Regenerate session ID on login to mitigate fixation
    session_regenerate_id(true);

    // Canonical structure
    $_SESSION['user'] = [
        'role'  => $role,
        'id'    => $id,
        'name'  => $name !== '' ? $name : ucfirst($role),
        'email' => $email,
    ];

    // Backwards compatibility for existing patient-only code
    if ($role === 'patient') {
        $_SESSION['patientID']    = $id;
        $_SESSION['patient_name'] = $_SESSION['user']['name'];
        if ($email !== null) {
            $_SESSION['patient_email'] = $email;
        }
    }
}

/**
 * Clear all application-specific session data for the authenticated user.
 * Does NOT destroy the entire PHP session.
 */
function clear_user_session(): void
{
    unset($_SESSION['user'], $_SESSION['patientID'], $_SESSION['patient_name'], $_SESSION['patient_email']);
}

/**
 * Returns true if any user (of any role) is logged in.
 */
function is_logged_in(): bool
{
    return !empty($_SESSION['user']['id']) && !empty($_SESSION['user']['role']);
}

/**
 * Ensure that some user is logged in (any role).
 * For API endpoints that return JSON-style errors.
 *
 * @return array The $_SESSION['user'] array.
 */
function require_user(): array
{
    if (!is_logged_in()) {
        http_response_code(403);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['error' => 'Not logged in']);
        exit;
    }

    // Ensure basic shape
    $user = $_SESSION['user'];
    $user['role'] = $user['role'] ?? '';
    $user['id']   = (int)($user['id'] ?? 0);

    return $user;
}

/**
 * Ensure that a user is logged in and has one of the allowed roles.
 * For API endpoints that should be restricted (e.g. ['admin', 'doctor']).
 *
 * @param array $allowedRoles e.g. ['admin', 'doctor']
 * @return array The $_SESSION['user'] array.
 */
function require_role(array $allowedRoles): array
{
    $user = require_user();
    $role = strtolower((string)$user['role']);

    $allowedRolesLower = array_map('strtolower', $allowedRoles);
    if (!in_array($role, $allowedRolesLower, true)) {
        http_response_code(403);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['error' => 'Access denied for role: ' . $role]);
        exit;
    }

    return $user;
}

/**
 * Redirect-style login guard for normal page requests (non-API).
 * If not logged in, or (optionally) not in allowed roles, redirect to login page.
 *
 * @param string    $loginPath   Relative path/URL to login page.
 * @param array|null $allowedRoles Optional array of allowed roles; if null, any logged-in user is allowed.
 */
function require_login(string $loginPath = '/webdev_prescription/login.php', ?array $allowedRoles = null): void
{
    if (!is_logged_in()) {
        header('Location: ' . $loginPath);
        exit;
    }

    if ($allowedRoles !== null) {
        $userRole = strtolower($_SESSION['user']['role'] ?? '');
        $allowedLower = array_map('strtolower', $allowedRoles);
        if (!in_array($userRole, $allowedLower, true)) {
            header('Location: ' . $loginPath);
            exit;
        }
    }
}

/**
 * (Optional / legacy) Convenience: resolve patient session from email if patientID missing.
 * Only applies to patient role and uses the `patient` table.
 *
 * Requires an active $conn (mysqli) from includes/db_connect.php.
 */
function resolve_session_from_email(mysqli $conn): void
{
    // If we already have a user with an ID, nothing to do.
    if (!empty($_SESSION['user']['id'])) {
        return;
    }

    // Prefer canonical user email if present.
    $email = $_SESSION['user']['email'] ?? ($_SESSION['patient_email'] ?? null);
    if ($email === null) {
        return;
    }

    // Only try to resolve for patient role (to avoid guessing other tables).
    $role = $_SESSION['user']['role'] ?? 'patient';
    if ($role !== 'patient') {
        return;
    }

    $stmt = $conn->prepare('SELECT patientID, firstName, lastName, email FROM patient WHERE email = ? LIMIT 1');
    if ($stmt === false) {
        return;
    }

    $stmt->bind_param('s', $email);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res && $res->num_rows === 1) {
        $row = $res->fetch_assoc();
        set_user_session('patient', $row);
    }

    $stmt->close();
}
?>