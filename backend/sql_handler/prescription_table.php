<?php
include(__DIR__ . '/../includes/db_connect.php');
include(__DIR__ . '/../includes/auth.php');

header('Content-Type: application/json');
$input = json_decode(file_get_contents("php://input"), true);

if (!$input || !isset($input['action'])) {
    echo json_encode(["success" => false, "message" => "Invalid request"]);
    exit;
}

$action = $input['action'];

if ($action === "add_prescription") {

    $doctorID   = intval($input["doctorID"]);
    $patientID  = intval($input["patientID"]);
    $startDate  = $input["startDate"];
    $medicines  = $input["medicines"];

    // Insert prescription header
    $stmt = $conn->prepare("
        INSERT INTO prescription (doctorID, patientID, issueDate, status)
        VALUES (?, ?, ?, 'active')
    ");
    $stmt->bind_param("iis", $doctorID, $patientID, $startDate);

    if (!$stmt->execute()) {
        echo json_encode(["success" => false, "message" => "Failed to create prescription"]);
        exit;
    }

    $prescriptionID = $stmt->insert_id;

    // Insert medicine rows
    foreach ($medicines as $med) {
        $stmt2 = $conn->prepare("
            INSERT INTO prescriptionitem (prescriptionID, medicineName, dosage, frequency, notes)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt2->bind_param(
            "issss",
            $prescriptionID,
            $med["medicine"],
            $med["dosage"],
            $med["frequency"],
            $med["notes"]
        );
        $stmt2->execute();
    }

    echo json_encode(["success" => true, "message" => "Prescription added"]);
    exit;
}


if ($action === "invalidate_prescription") {

    $prescriptionID = intval($input["prescriptionID"]);

    $stmt = $conn->prepare("
        UPDATE prescription 
        SET status = 'invalid'
        WHERE prescriptionID = ?
    ");
    $stmt->bind_param("i", $prescriptionID);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Prescription invalidated"]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to invalidate"]);
    }

    exit;
}

echo json_encode(["success" => false, "message" => "Unknown action"]);
?>
