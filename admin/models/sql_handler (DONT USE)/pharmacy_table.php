<?php
//CODE BELOW IS CHATGPT GENERATED. NEEDS TO BE REVIEWED AND REFINED

// handles pharmacy data retrieval and manipulation
include(__DIR__ . '/../includes/db_connect.php');
include(__DIR__ . '/../includes/auth.php');

function respond($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

$user   = require_user();
$role   = $user['role'];
$userID = (int)$user['id']; // for reference if needed later

/**
 * GET
 * - Any authenticated user can:
 *   - List all pharmacies
 *   - Get a specific pharmacy via ?pharmacyID=
 */
if ($method === 'GET') {

    if (isset($_GET['pharmacyID'])) {
        $pharmacyID = (int)$_GET['pharmacyID'];

        if ($role === 'admin') {
            $stmt = $conn->prepare('SELECT * FROM pharmacy WHERE pharmacyID = ?');
            $stmt->bind_param('i', $pharmacyID);
        } else {
            $stmt = $conn->prepare('SELECT * FROM pharmacy WHERE pharmacyID = ? AND status = "active"');
            $stmt->bind_param('i', $pharmacyID);
        }

        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            respond(['error' => 'Pharmacy not found'], 404);
        }

        respond($result->fetch_assoc());
    }

    // LIST ALL
    if ($role === 'admin') {
        $stmt = $conn->prepare('SELECT * FROM pharmacy ORDER BY name');
    } else {
        $stmt = $conn->prepare('SELECT * FROM pharmacy WHERE status = "active" ORDER BY name');
    }

    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        respond(['error' => 'No pharmacies found'], 404);
    }

    $pharmacies = [];
    while ($row = $result->fetch_assoc()) {
        $pharmacies[] = $row;
    }

    respond($pharmacies);
}


/**
 * POST
 * - Only admins can add pharmacies.
 * - This assumes the request body is JSON.
 * - NOTE: pharmacyID is NOT AUTO_INCREMENT in your schema,
 *   so it must be provided and unique.
 */
else if ($method === 'POST') {

    if ($role !== 'admin') {
        respond(['error' => 'Forbidden: only admins can add pharmacies'], 403);
    }

    $input = json_decode(file_get_contents('php://input'), true);
    if (!is_array($input)) {
        respond(['error' => 'Invalid JSON body'], 400);
    }

    // Required fields (pharmacyID must be provided because no AUTO_INCREMENT)
    $required = ['pharmacyID', 'name', 'address', 'contactNumber', 'email', 'clinicAddress'];
    foreach ($required as $field) {
        if (!isset($input[$field]) || $input[$field] === '') {
            respond(['error' => "Missing required field: {$field}"], 400);
        }
    }

    $pharmacyID    = (int)$input['pharmacyID'];
    $name          = $input['name'];
    $address       = $input['address'];
    $contactNumber = $input['contactNumber']; // varchar(20)
    $email         = $input['email'];
    $clinicAddress = $input['clinicAddress'];

    $stmt = $conn->prepare(
        'INSERT INTO pharmacy
            (pharmacyID, name, address, contactNumber, email, clinicAddress)
         VALUES (?, ?, ?, ?, ?, ?)'
    );

    $stmt->bind_param(
        'isssss',
        $pharmacyID,
        $name,
        $address,
        $contactNumber,
        $email,
        $clinicAddress
    );

    if (!$stmt->execute()) {
        respond(['error' => 'Failed to create pharmacy', 'details' => $stmt->error], 500);
    }

    // Return the newly created pharmacy
    $stmt = $conn->prepare('SELECT * FROM pharmacy WHERE pharmacyID = ?');
    $stmt->bind_param('i', $pharmacyID);
    $stmt->execute();
    $result   = $stmt->get_result();
    $pharmacy = $result->fetch_assoc();

    respond($pharmacy, 201);
}

/**
 * DELETE
 * - Only admins can delete pharmacy records.
 * - Target via ?pharmacyID=
 */
else if ($method === 'DELETE') {

    if ($role !== 'admin') {
        respond(['error' => 'Forbidden: only admins can delete pharmacies'], 403);
    }

    if (!isset($_GET['pharmacyID'])) {
        respond(['error' => 'pharmacyID is required'], 400);
    }

    $pharmacyID = (int)$_GET['pharmacyID'];

    $stmt = $conn->prepare('DELETE FROM pharmacy WHERE pharmacyID = ?');
    $stmt->bind_param('i', $pharmacyID);

    if (!$stmt->execute()) {
        // Could be foreign key issues if you later link pharmacy somewhere
        respond(['error' => 'Failed to delete pharmacy', 'details' => $stmt->error], 500);
    }

    if ($stmt->affected_rows === 0) {
        respond(['error' => 'Pharmacy not found or already deleted'], 404);
    }

    respond(['message' => 'Pharmacy deleted successfully']);
}

else {
    respond(['error' => 'Method not allowed'], 405);
}
