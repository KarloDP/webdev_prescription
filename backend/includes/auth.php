<?php
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
        'doctor'     => 'doctorID',
        'pharmacist' => 'pharmacyID', // Corrected: Match the likely 'pharmacy' table PK
        'admin'      => 'adminID',
    ];

    return $map[$role] ?? null;
}

function redirect_based_on_role(string $role): void {


    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    ? "https://" : "http://";

    $host = $_SERVER['HTTP_HOST']; // e.g. localhost OR yourdomain.com

    // Detect if inside a folder (like /webdev_prescription) or inside root
    $projectFolder = dirname($_SERVER['SCRIPT_NAME']); // e.g. /webdev_prescription/frontend/patient
    $projectFolder = explode('/frontend', $projectFolder)[0]; // keep only root folder

    $basePath = rtrim($protocol . $host . $projectFolder, '/');

    switch ($role) {
        case 'patient':
            header("Location: {$basePath}/frontend/patient/dashboard/dashboard.php");
            break;
        case 'doctor':
            header("Location: {$basePath}/frontend/doctor/dashboard/dashboard.php");
            break;
        case 'pharmacist': // Added: Redirect for pharmacist role
            header("Location: {$basePath}/frontend/pharmacist/index.php");
            break;
        // Add other roles as needed
        default:
            // Fallback to the main login page
            header("Location: {$basePath}/login.php");
            break;
    }
    exit;
}

/**
 * Authenticates a user against the database.
 *
 * @param mysqli $conn The database connection.
 * @param string $role The user's role (e.g., 'patient').
 * @param string $identifier The user's identifier (e.g., email).
 * @param string $password The plaintext password to verify.
 * @return array|null The user's database row on success, null on failure.
 */
function authenticate_user(mysqli $conn, string $role, string $identifier, string $password): ?array
{
    // Corrected: Use the role to determine the table name, with a special case for pharmacist
    $table = $role;
    if ($role === 'pharmacist') {
        $table = 'pharmacy'; // Use the 'pharmacy' table for the 'pharmacist' role
    }
    $identifierColumn = 'email'; // Assumes login is via email

    $sql = "SELECT * FROM `{$table}` WHERE `{$identifierColumn}` = ? LIMIT 1";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return null; // DB error
    }

    $stmt->bind_param('s', $identifier);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows === 1) {
        $userRow = $result->fetch_assoc();
        $stmt->close();

        // !! INSECURE - FOR TESTING ONLY !!
        // Compare password as plaintext.
        if (isset($userRow['password']) && $password === $userRow['password']) {
            unset($userRow['password']);
            return $userRow;
        }

        /*
        // SECURE HASHED PASSWORD CHECK (use this for production)
        if (isset($userRow['password']) && password_verify($password, $userRow['password'])) {
            unset($userRow['password']); // Don't keep the hash in the returned array
            return $userRow;
        }
        */
    }

    return null; // User not found or password incorrect
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

    // Canonical structure for new code
    // ***** THE FIX IS HERE: Add firstName and lastName to the session array *****
    $_SESSION['user'] = [
        'id'        => $id,
        'role'      => $role,
        'name'      => $name,
        'firstName' => $first, // <-- ADD THIS LINE
        'lastName'  => $last,  // <-- ADD THIS LINE
        'email'     => $email,
    ];
    // Backwards compatibility for existing patient-only code
    if ($role === 'patient') {
        $_SESSION['patientID'] = $id;
        $_SESSION['patient_name'] = $name;
    }
    
    // Backwards compatibility for pharmacist if needed
    if ($role === 'pharmacist') {
        // Add any old session variables your pharmacist pages might use
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