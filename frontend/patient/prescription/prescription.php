<?php
session_start();
require_once __DIR__ . '/../../../backend/includes/auth.php';
//include(__DIR__ . '/../includes/db_connect.php');

// Redirect if not logged in as patient
if (!isset($_SESSION['patientID'])) {
    header("Location: ../TestLoginPatient.php");
    exit;
}

$patientID   = (int) $_SESSION['patientID'];
$activePage  = 'medications';

// Fetch patient name (for header)
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
// ----------------------
ob_start();
?>

    <div class="medication-page">
        <h2>Original Prescriptions</h2>
        <p>
            These are the original prescriptions issued to
            <?php echo htmlspecialchars($patientName, ENT_QUOTES, 'UTF-8'); ?>.
        </p>

        <!-- Back Button -->
        <div style="margin-bottom: 20px;">
            <a href="../medication/medication.php" class="btn-view"
               style="display:inline-block;padding:10px 15px;background:#1e3d2f;color:#fff;border-radius:4px;text-decoration:none;">
                ← Back to Medications
            </a>
        </div>

        <div class="table-frame">
            <table class="table-base" id="original-prescriptions-table">
                <thead>
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
                </thead>
                <tbody id="original-prescriptions-body">
                    <tr>
                        <td colspan="13">Loading prescriptions...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Expose patient info to JS -->
    <script>
        window.currentPatient = {
            id: <?php echo (int)$patientID; ?>,
            name: <?php echo json_encode($patientName, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>
        };
    </script>

    <!-- Page-specific JS -->
    <script src="prescription.js"></script>

<?php
// END PAGE CONTENT
$content = ob_get_clean();

// Render using standard patient layout
include __DIR__ . '/../patient_standard.php';
?>