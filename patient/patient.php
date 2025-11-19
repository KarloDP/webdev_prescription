<?php
session_start();
include(__DIR__ . '/../includes/auth.php');
include(__DIR__ . '/../includes/db_connect.php');

if (!isset($_SESSION['patientID'])) {
    header("Location: ../TestLoginPatient.php");
    exit;
}

$patientID = (int) $_SESSION['patientID'];
$activePage = 'history';

// Fetch patient name once (same logic as medication.php)
$patientName = $_SESSION['patient_name'] ?? 'Patient';
if (empty($_SESSION['patient_name'])) {
    $s = $conn->prepare("SELECT firstName, lastName FROM patient WHERE patientID = ? LIMIT 1");
    if ($s) {
        $s->bind_param("i", $patientID);
        $s->execute();
        $r = $s->get_result();
        if ($r && $r->num_rows === 1) {
            $row = $r->fetch_assoc();
            $patientName = trim($row['firstName'] . ' ' . $row['lastName']);
            $_SESSION['patient_name'] = $patientName;
        }
        $s->close();
    }
}

// Capture page content
ob_start();
?>

<link rel="stylesheet" href="../assets/css/table.css">

<div class="history-page">

    <h2>Prescription History</h2>
    <p>Complete record of prescriptions issued to 
        <?php echo htmlspecialchars($patientName, ENT_QUOTES, 'UTF-8'); ?>.
    </p>

    <?php
    // ---- HISTORY QUERY (unchanged logic) ----
    $query = "
        SELECT 
            p.prescriptionID,
            p.issueDate,
            p.expirationDate,
            p.refillInterval,
            p.status AS prescriptionStatus,

            m.genericName,
            m.brandName,
            m.form,
            m.strength,

            d.firstName AS doctorFirst,
            d.lastName AS doctorLast,

            pi.dosage,
            pi.frequency,
            pi.duration,
            pi.instructions,
            pi.refill_count,

            COALESCE(dr.totalDispensed, 0) AS totalDispensed,
            dr.nextRefillDate

        FROM prescription p
        INNER JOIN prescriptionitem pi ON p.prescriptionID = pi.prescriptionID
        INNER JOIN medication m ON pi.medicationID = m.medicationID
        INNER JOIN doctor d ON p.doctorID = d.doctorID

        LEFT JOIN (
            SELECT 
                prescriptionItemID,
                SUM(quantityDispensed) AS totalDispensed,
                MAX(nextAvailableDates) AS nextRefillDate
            FROM dispenserecord
            GROUP BY prescriptionItemID
        ) dr ON pi.prescriptionItemID = dr.prescriptionItemID

        WHERE p.patientID = ?
        ORDER BY p.issueDate DESC, p.prescriptionID DESC
    ";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $patientID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {

        echo '<div class="table-frame">';
        echo "<table class='table-base history-table'>";
        echo "
            <thead>
                <tr>
                    <th>Prescription #</th>
                    <th>Medicine</th>
                    <th>Brand</th>
                    <th>Form</th>
                    <th>Strength</th>
                    <th>Doctor</th>
                    <th>Dosage</th>
                    <th>Frequency</th>
                    <th>Duration</th>
                    <th>Instructions</th>
                    <th>Status</th>
                    <th>Total Dispensed</th>
                    <th>Next Refill</th>
                    <th>Issued</th>
                    <th>Expires</th>
                </tr>
            </thead>
            <tbody>
        ";

        while ($row = $result->fetch_assoc()) {

            echo "<tr>
                    <td>RX-" . str_pad($row['prescriptionID'], 2, '0', STR_PAD_LEFT) . "</td>
                    <td>{$row['genericName']}</td>
                    <td>{$row['brandName']}</td>
                    <td>{$row['form']}</td>
                    <td>{$row['strength']}</td>

                    <td>Dr. {$row['doctorFirst']} {$row['doctorLast']}</td>

                    <td>{$row['dosage']}</td>
                    <td>{$row['frequency']}</td>
                    <td class='col-wide'>{$row['duration']}</td>
                    <td class='col-xwide'>{$row['instructions']}</td>

                    <td>{$row['prescriptionStatus']}</td>
                    <td>{$row['totalDispensed']} unit(s)</td>

                    <td>" . ($row['nextRefillDate'] ? date("F j, Y", strtotime($row['nextRefillDate'])) : "N/A") . "</td>

                    <td>" . date("F j, Y", strtotime($row['issueDate'])) . "</td>
                    <td>" . date("F j, Y", strtotime($row['expirationDate'])) . "</td>
                </tr>";
        }

        echo "</tbody></table></div>";

    } else {
        echo '<p>No prescription history found.</p>';
    }

    $stmt->close();
    ?>

    <!-- Bottom Button -->
    <div style="margin-top: 20px;">
        <a href="dashboard.php" class="btn-view"
           style="display:inline-block;padding:10px 15px;background:#1e3d2f;color:#fff;border-radius:4px;text-decoration:none;">
            ← Back to Dashboard
        </a>
    </div>

</div>

<?php
// Send to layout
$content = ob_get_clean();
include(__DIR__ . '/patient_standard.php');
?>
