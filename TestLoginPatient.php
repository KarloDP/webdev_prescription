<?php
// TestLoginPatient.php
session_start();
require_once __DIR__ . '/includes/db_connect.php';

// Simple POST-only login using email (adjust to add password later)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Show a minimal form if accessed directly (use your real login page instead)
    echo '<form method="post">
            <label>Email: <input type="email" name="email" required></label>
            <button type="submit">Sign in</button>
          </form>';
    exit;
}

$email = trim($_POST['email'] ?? '');
if ($email === '') {
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'index.php'));
    exit;
}

// Lookup patient by email
$stmt = $conn->prepare("SELECT patientID, firstName, lastName FROM patient WHERE email = ? LIMIT 1");
if (!$stmt) {
    // DB error — show simple message for dev
    http_response_code(500);
    echo 'Database error: failed to prepare statement';
    exit;
}
$stmt->bind_param('s', $email);
$stmt->execute();
$res = $stmt->get_result();

if ($res && $res->num_rows === 1) {
    $row = $res->fetch_assoc();
    // Set canonical session values used by pages
    $_SESSION['patientID'] = (int)$row['patientID'];
    $_SESSION['patient_name'] = trim($row['firstName'] . ' ' . $row['lastName']);
    // Optional: set email in session
    $_SESSION['patient_email'] = $email;

    // Redirect to patient dashboard
    header('Location: patient/dashboard.php');
    exit;
}

// No match — redirect back to login (or show error)
header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'index.php') . '?error=invalid_credentials');
exit;
