<?php
<?php
// Set the header first to ensure JSON output, even on error
header('Content-Type: application/json');

// Centralized response function
function respond($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data);
    exit;
}

// Wrap all logic in a try-catch block to handle any PHP errors
try {
    // Include dependencies inside the try block
    require_once __DIR__ . '/../includes/db_connect.php';
    require_once __DIR__ . '/../includes/auth.php';

    $user = require_role(['pharmacist']);

    // --- 1. Fetch Prescription Summary List ---
    $sqlSummary = "
        SELECT p.prescriptionID, p.issueDate, pat.firstName, pat.lastName
        FROM prescription AS p
        JOIN patient AS pat ON p.patientID = pat.patientID
        WHERE p.status = 'Active'
        ORDER BY p.issueDate DESC";
    
    $resultSummary = $conn->query($sqlSummary);
    if (!$resultSummary) {
        throw new Exception("Failed to execute summary query: " . $conn->error);
    }
    $prescriptionSummary = $resultSummary->fetch_all(MYSQLI_ASSOC);

    // --- 2. Fetch Details for a Specific Prescription if ID is provided ---
    $selectedPrescriptionDetails = null;
    if (isset($_GET['prescription_id']) && !empty($_GET['prescription_id'])) {
        $selectedPrescriptionID = (int)$_GET['prescription_id'];
        
        $sqlDetails = "
            SELECT
                p.prescriptionID, p.status,
                DATE_FORMAT(p.issueDate, '%M %e, %Y') AS formattedIssueDate,
                pat.firstName AS patientFirstName, pat.lastName AS patientLastName,
                pat.address, pat.birthdate, pat.gender,
                doc.lastName AS doctorLastName,
                pi.prescriptionItemID, pi.dosage, pi.prescribed_amount,
                pi.instructions AS medicationInstructions,
                med.genericName AS medicationName, med.medicationID, med.strength
            FROM prescription AS p
            JOIN patient AS pat ON p.patientID = pat.patientID
            JOIN doctor AS doc ON p.doctorID = doc.doctorID
            LEFT JOIN prescriptionitem AS pi ON p.prescriptionID = pi.prescriptionID
            LEFT JOIN medication AS med ON pi.medicationID = med.medicationID
            WHERE p.prescriptionID = ?
            ORDER BY pi.prescriptionItemID ASC;
        ";
        $stmtDetails = $conn->prepare($sqlDetails);
        if (!$stmtDetails) {
            throw new Exception("Failed to prepare details statement: " . $conn->error);
        }
        $stmtDetails->bind_param('i', $selectedPrescriptionID);
        $stmtDetails->execute();
        $resultDetails = $stmtDetails->get_result();
        $rawDetails = $resultDetails->fetch_all(MYSQLI_ASSOC);

        if (!empty($rawDetails)) {
            $firstRow = $rawDetails[0];
            $dob = new DateTime($firstRow['birthdate']);
            $age = $dob->diff(new DateTime())->y;

            $selectedPrescriptionDetails = [
                'prescriptionID' => $firstRow['prescriptionID'],
                'status' => $firstRow['status'],
                'formattedIssueDate' => $firstRow['formattedIssueDate'],
                'patientFirstName' => $firstRow['patientFirstName'],
                'patientLastName' => $firstRow['patientLastName'],
                'patientAddress' => $firstRow['address'],
                'patientAge' => $age,
                'patientGender' => $firstRow['gender'],
                'doctorLastName' => $firstRow['doctorLastName'],
                'medications' => []
            ];

            foreach ($rawDetails as $item) {
                if ($item['prescriptionItemID']) {
                    $item['dosage'] = $item['strength']; // Alias strength to dosage for consistency
                    $selectedPrescriptionDetails['medications'][] = $item;
                }
            }
        }
    }

    // --- 3. Respond with all data in one JSON object ---
    respond([
        'summary' => $prescriptionSummary,
        'details' => $selectedPrescriptionDetails
    ]);

} catch (Throwable $e) {
    // Catch any error (including fatal ones in PHP 7+) and respond with a JSON error message
    respond(['error' => 'Server Error', 'details' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()], 500);
}
?>