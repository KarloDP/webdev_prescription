<?php
require_once __DIR__ . '/../includes/db_connect.php';
header('Content-Type: application/json');

function respond(array $payload, int $status = 200): void {
    http_response_code($status);
    echo json_encode($payload);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true) ?? [];
$role  = $input['role'] ?? '';
$email = trim($input['email'] ?? '');
$pass  = $input['password'] ?? '';

if (!in_array($role, ['doctor', 'pharmacist', 'patient'], true) || $email === '' || $pass === '') {
    respond(['error' => 'Invalid data supplied.'], 400);
}

try {
    switch ($role) {
        case 'doctor':
            $required = ['firstName', 'lastName', 'specialization', 'licenseNumber', 'clinicAddress'];
            foreach ($required as $field) {
                if (empty(trim((string)($input[$field] ?? '')))) {
                    respond(['error' => ucfirst($field) . ' is required.'], 422);
                }
            }
            $sql = "
                INSERT INTO doctor (firstName, lastName, email, password, specialization, licenseNumber, clinicAddress, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')
            ";
            $params = [
                trim($input['firstName']),
                trim($input['lastName']),
                $email,
                $pass,
                trim($input['specialization']),
                trim($input['licenseNumber']),
                trim($input['clinicAddress'])
            ];
            $types = 'sssssss';
            break;

        case 'pharmacist':
            $required = ['name', 'address', 'contactNumber', 'clinicAddress'];
            foreach ($required as $field) {
                if (empty(trim((string)($input[$field] ?? '')))) {
                    respond(['error' => ucfirst($field) . ' is required.'], 422);
                }
            }
            $sql = "
                INSERT INTO pharmacy (name, email, password, address, contactNumber, clinicAddress, status)
                VALUES (?, ?, ?, ?, ?, ?, 'pending')
            ";
            $params = [
                trim($input['name']),
                $email,
                $pass,
                trim($input['address']),
                trim($input['contactNumber']),
                trim($input['clinicAddress'])
            ];
            $types = 'ssssss';
            break;

        default: // patient
            $required = ['firstName', 'lastName', 'birthDate', 'gender', 'contactNumber', 'address'];
            foreach ($required as $field) {
                if (empty(trim((string)($input[$field] ?? '')))) {
                    respond(['error' => ucfirst($field) . ' is required.'], 422);
                }
            }

            $sql = "
                INSERT INTO patient (firstName, lastName, email, password, birthDate, gender, contactNumber, address)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ";
            $params = [
                trim($input['firstName']),
                trim($input['lastName']),
                $email,
                $pass,
                $input['birthDate'],
                trim($input['gender']),
                trim($input['contactNumber']),
                trim($input['address'])
            ];
            $types = 'ssssssss';
            break;
    }

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();

    respond(['success' => true, 'id' => $stmt->insert_id], 201);
} catch (mysqli_sql_exception $e) {
    respond(['error' => 'Registration failed.', 'details' => $e->getMessage()], 500);
}

// ...existing code...
      } else {
        input = document.createElement("input");
        input.type = field.type;
        if (field.type === "number") input.min = "0";
      }
      if (field.name === "contactNumber") {
        input.inputMode = "numeric";
        input.pattern = "\\d+";
      }
      if (field.name === "email") {
        input.type = "email";
      }
// ...existing code...