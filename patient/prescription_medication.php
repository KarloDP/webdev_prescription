<?php
session_start();
include(__DIR__ . '/../includes/auth.php');
include(__DIR__ . '/../includes/db_connect.php');

if (!isset($_SESSION['patientID'])) {
    header("Location: ../TestLoginPatient.php");
    exit;
}

$patientID = $_SESSION['patientID'];
$activePage = 'prescription_medication';

// Fetch patient name
$patientName = "Patient";
$stmt = $conn->prepare("SELECT firstName, lastName FROM patient WHERE patientID = ?");
$stmt->bind_param("i", $patientID);
$stmt->execute();
$result = $stmt->get_result();
if ($result && $result->num_rows > 0) {
    $patient = $result->fetch_assoc();
    $patientName = $patient['firstName'] . ' ' . $patient['lastName'];
}

// --- Capture page-specific content ---
ob_start();
?>
<link rel="stylesheet" href="<?php echo dirname($_SERVER['PHP_SELF']); ?>/../assets/css/table.css">
<link rel="stylesheet" href="<?php echo dirname($_SERVER['PHP_SELF']); ?>/../assets/css/medication_patient.css">

<style>
  .prescription-group {
    margin-bottom: 30px; /* adds space between groups */
  }
</style>

<div class="medication-page">
    <h2>Grouped Prescriptions</h2>
    <p>Medications per prescription for <?php echo htmlspecialchars($patientName, ENT_QUOTES, 'UTF-8'); ?>.</p>

    <?php
    // Query all prescriptions for this patient
    $stmt = $conn->prepare("
        SELECT
            p.prescriptionID,
            p.status,
            p.issueDate,
            CONCAT('Dr ', d.firstName, ' ', d.lastName) AS doctorName,
            m.genericName AS medicine,
            m.brandName AS brand,
            pi.dosage,
            pi.frequency,
            pi.duration,
            pi.prescribed_amount,
            pi.refill_count,
            pi.instructions
        FROM prescription p
        JOIN prescriptionitem pi ON p.prescriptionID = pi.prescriptionID
        JOIN medication m ON pi.medicationID = m.medicationID
        JOIN doctor d ON p.doctorID = d.doctorID
        WHERE p.patientID = ?
        ORDER BY p.prescriptionID DESC, pi.prescriptionItemID ASC
    ");
    $stmt->bind_param("i", $patientID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $currentRx = null;
        while ($row = $result->fetch_assoc()) {
            // New prescription header
            if ($currentRx !== $row['prescriptionID']) {
                if ($currentRx !== null) {
                    echo "</tbody></table></div>"; // close previous group
                }

                $rxID = 'RX-' . str_pad($row['prescriptionID'], 2, '0', STR_PAD_LEFT);
                $statusBadge = $row['status'] === 'Active'
                    ? "<span style='color:#155724;background:#d4edda;padding:1px 4px;border-radius:4px;'>Active</span>"
                    : "<span style='color:#721c24;background:#f8d7da;padding:1px 4px;border-radius:4px;'>Expired</span>";

                echo "<div class='prescription-group'>";
                echo "<h3>Prescription {$rxID} ({$statusBadge})</h3>";
                echo "<p>Doctor: {$row['doctorName']} | Issued: " . date("F j, Y", strtotime($row['issueDate'])) . "</p>";
                echo "<table class='table-base'>";
                echo "<thead>
                        <tr>
                            <th>Medicine</th>
                            <th>Brand</th>
                            <th>Dosage</th>
                            <th>Frequency</th>
                            <th>Duration</th>
                            <th>Amount</th>
                            <th>Refills</th>
                            <th>Instructions</th>
                        </tr>
                      </thead>";
                echo "<tbody>";
                $currentRx = $row['prescriptionID'];
            }

            // Prescription item row
            echo "<tr>
                    <td>{$row['medicine']}</td>
                    <td>{$row['brand']}</td>
                    <td>{$row['dosage']}</td>
                    <td>{$row['frequency']}</td>
                    <td>{$row['duration']}</td>
                    <td>{$row['prescribed_amount']}</td>
                    <td>{$row['refill_count']}</td>
                    <td>{$row['instructions']}</td>
                  </tr>";
        }
        // Close last group
        echo "</tbody></table></div>";
    } else {
        echo '<div class="empty-state"><p>No prescriptions found.</p></div>';
    }
    ?>

    <!-- Back button -->
    <div style="margin-top:20px;">
        <a href="medication.php" class="btn-view" style="display:inline-block;padding:10px 15px;background:#6c757d;color:#fff;border-radius:4px;text-decoration:none;">
             Back
        </a>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/patient_standard.php';
?>
