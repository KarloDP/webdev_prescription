<?php
session_start();
include(__DIR__ . '/../includes/auth.php');
include(__DIR__ . '/../includes/db_connect.php');

// Redirect if not logged in as patient
if (!isset($_SESSION['patientID'])) {
    header("Location: ../TestLoginPatient.php");
    exit;
}

$patientID = (int) $_SESSION['patientID'];
$activePage = 'medications';

// Fetch patient name (for consistency in the layout header)
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

// ----------------------
// BEGIN PAGE CONTENT
// Wrap everything in output buffer
// ----------------------
ob_start();
?>

    <link rel="stylesheet" href="../assets/css/table.css">

    <div class="medication-page">
        <h2>Original Prescriptions</h2>
        <p>These are the original prescriptions issued to <?php echo htmlspecialchars($patientName, ENT_QUOTES, 'UTF-8'); ?>.</p>

        <!-- Back Button -->
        <div style="margin-bottom: 20px;">
            <a href="medication.php" class="btn-view"
               style="display:inline-block;padding:10px 15px;background:#1e3d2f;color:#fff;border-radius:4px;text-decoration:none;">
                ← Back to Medications
            </a>
        </div>

        <?php
        // Query original prescription details
        $sql = "
        SELECT
            p.prescriptionID,
            p.issueDate,
            p.expirationDate,
            p.refillInterval,
            m.genericName,
            m.brandName,
            m.form,
            m.strength,
            d.firstName,
            d.lastName,
            pi.dosage,
            pi.frequency,
            pi.duration,
            pi.instructions
        FROM prescription p
        JOIN prescriptionitem pi ON p.prescriptionID = pi.prescriptionID
        JOIN medication m ON pi.medicationID = m.medicationID
        JOIN doctor d ON p.doctorID = d.doctorID
        WHERE p.patientID = ?
        ORDER BY p.issueDate DESC
    ";

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            echo "<p>Unable to load original prescriptions.</p>";
        } else {
            $stmt->bind_param("i", $patientID);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result && $result->num_rows > 0) {

                echo '<div class="table-frame">';
                echo '<table class="table-base">';
                echo '<thead>
                    <tr>
                        <th>Prescription ID</th>
                        <th>Medicine</th>
                        <th>Brand</th>
                        <th>Form</th>
                        <th>Strength</th>
                        <th>Doctor</th>
                        <th>Dosage</th>
                        <th>Frequency</th>
                        <th>Duration</th>
                        <th>Instructions</th>
                        <th>Refill Interval</th>
                        <th>Issued</th>
                        <th>Expires</th>
                    </tr>
                  </thead>';
                echo '<tbody>';

                while ($row = $result->fetch_assoc()) {

                    $rxID = 'RX-' . str_pad($row['prescriptionID'], 2, '0', STR_PAD_LEFT);

                    $doctorFull = "Dr. " . htmlspecialchars($row['firstName'], ENT_QUOTES, 'UTF-8')
                        . " " . htmlspecialchars($row['lastName'], ENT_QUOTES, 'UTF-8');

                    $issued = $row['issueDate'] ? date("F j, Y", strtotime($row['issueDate'])) : '-';
                    $expires = $row['expirationDate'] ? date("F j, Y", strtotime($row['expirationDate'])) : '-';

                    echo "<tr>
                        <td>{$rxID}</td>
                        <td>" . htmlspecialchars($row['genericName'], ENT_QUOTES, 'UTF-8') . "</td>
                        <td>" . htmlspecialchars($row['brandName'], ENT_QUOTES, 'UTF-8') . "</td>
                        <td>" . htmlspecialchars($row['form'], ENT_QUOTES, 'UTF-8') . "</td>
                        <td>" . htmlspecialchars($row['strength'], ENT_QUOTES, 'UTF-8') . "</td>
                        <td>{$doctorFull}</td>
                        <td>" . htmlspecialchars($row['dosage'], ENT_QUOTES, 'UTF-8') . "</td>
                        <td>" . htmlspecialchars($row['frequency'], ENT_QUOTES, 'UTF-8') . "</td>
                        <td>" . htmlspecialchars($row['duration'], ENT_QUOTES, 'UTF-8') . "</td>
                        <td>" . htmlspecialchars($row['instructions'], ENT_QUOTES, 'UTF-8') . "</td>
                        <td>" . htmlspecialchars($row['refillInterval'], ENT_QUOTES, 'UTF-8') . "</td>
                        <td>{$issued}</td>
                        <td>{$expires}</td>
                      </tr>";
                }

                echo '</tbody>';
                echo '</table>';
                echo '</div>';

            } else {
                echo '<p>No original prescriptions found.</p>';
            }

            $stmt->close();
        }
        ?>
    </div>

<?php
// END PAGE CONTENT
$content = ob_get_clean();

// Render using standard patient layout
include(__DIR__ . '/patient_standard.php');
