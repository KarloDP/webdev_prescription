<?php
// patient/dashboard.php
// Standardized patient dashboard:
// - starts session if needed
// - enforces login via includes/auth.php
// - ensures patient_name is present (fallback to DB)
// - safely counts and fetches active prescriptions (prepared stmts, closed)

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db_connect.php';

// Enforce login (redirects if not authenticated)
require_login();

// Ensure patient_name exists in session; fallback to DB if missing
if (empty($_SESSION['patient_name']) && !empty($_SESSION['patientID'])) {
    if (function_exists('set_user_session_from_db')) {
        set_user_session_from_db($conn, (int) $_SESSION['patientID']);
    } else {
        $pid = (int) ($_SESSION['patientID'] ?? 0);
        if ($pid > 0) {
            $s = $conn->prepare('SELECT firstName, lastName FROM patient WHERE patientID = ? LIMIT 1');
            if ($s) {
                $s->bind_param('i', $pid);
                $s->execute();
                $r = $s->get_result();
                if ($r && $r->num_rows === 1) {
                    $row = $r->fetch_assoc();
                    $_SESSION['patient_name'] = trim(($row['firstName'] ?? '') . ' ' . ($row['lastName'] ?? ''));
                }
                $s->close();
            }
        }
    }
}

// Local variables pages expect
$activePage  = 'dashboard';
$patientID   = (int) ($_SESSION['patientID'] ?? 0);
$patientName = $_SESSION['patient_name'] ?? 'Patient';

// Pagination defaults
$perPage     = 8;
$currentPage = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$offset      = ($currentPage - 1) * $perPage;

// Count total active prescriptions
$totalItems = 0;
$countStmt = $conn->prepare("
  SELECT COUNT(DISTINCT p.prescriptionID) AS cnt
  FROM prescription p
  WHERE p.patientID = ? AND LOWER(p.status) = 'active'
");
if ($countStmt) {
    $countStmt->bind_param("i", $patientID);
    $countStmt->execute();
    $countRes = $countStmt->get_result();
    $totalItems = ($countRes && $countRes->num_rows) ? (int)$countRes->fetch_assoc()['cnt'] : 0;
    $countStmt->close();
}
$totalPages = (int)ceil(max(0, $totalItems) / $perPage);

// Fetch active prescriptions (one row per prescription)
// Use ANY_VALUE for non-aggregated columns to be compatible with ONLY_FULL_GROUP_BY
$prescriptions = [];
if ($totalItems > 0) {
    $sql = "
      SELECT
        p.prescriptionID,
        ANY_VALUE(m.genericName) AS medicine,
        ANY_VALUE(CONCAT('Dr ', d.firstName, ' ', d.lastName)) AS doctor_name,
        ANY_VALUE(pi.dosage) AS notes,
        MAX(p.issueDate) AS prescribed_at
      FROM prescription p
      JOIN prescriptionitem pi ON p.prescriptionID = pi.prescriptionID
      JOIN medication m ON pi.medicationID = m.medicationID
      JOIN doctor d ON p.doctorID = d.doctorID
      WHERE p.patientID = ? AND LOWER(p.status) = 'active'
      GROUP BY p.prescriptionID
      ORDER BY prescribed_at DESC
      LIMIT ? OFFSET ?
    ";

    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("iii", $patientID, $perPage, $offset);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            // normalize date field
            $row['prescribed_at'] = !empty($row['prescribed_at']) ? $row['prescribed_at'] : null;
            $prescriptions[] = $row;
        }
        $stmt->close();
    } else {
        error_log('Prepare failed (active prescriptions): ' . $conn->error);
    }
}

// --- CONTENT BUFFER ---
ob_start();
?>

<div class="patient-dashboard">
  <div class="welcome-row">
    <div class="welcome-card">
      <h1>Welcome <span class="name"><?php echo htmlspecialchars(strtoupper($patientName), ENT_QUOTES, 'UTF-8'); ?> !!</span></h1>
      <p class="subtitle">View Prescriptions. Manage medications and pharmacies.</p>
      <div class="welcome-actions">
        <a class="btn btn-primary" href="medication.php">View Medications</a>
        <a class="btn btn-outline" href="pharmacies.php">Find Pharmacies</a>
      </div>
    </div>

    <div class="stats-card">
      <div class="stats-row">
        <div class="stat">
          <div class="stat-number" id="activeCount"><?php echo (int)$totalItems; ?></div>
          <div class="stat-label">Active Prescriptions</div>
        </div>
        <div class="stat">
          <div class="stat-number" id="refillCount">0</div>
          <div class="stat-label">Upcoming Refills</div>
        </div>
        <div class="stat">
          <div class="stat-number" id="nearbyPharmacies">0</div>
          <div class="stat-label">Nearby Pharmacies</div>
        </div>
      </div>
      <div class="stats-cta">
        <a class="link" href="prescriptions.php">View Details</a>
      </div>
    </div>
  </div>

  <section class="prescriptions-section">
    <h2 class="section-title">Active Prescriptions</h2>

    <div class="cards-grid">
      <?php if (!empty($prescriptions)): ?>
        <?php foreach ($prescriptions as $pres): ?>
          <?php $dateDisplay = !empty($pres['prescribed_at']) ? date("F j, Y", strtotime($pres['prescribed_at'])) : '-'; ?>
          <article class="prescription-card">
            <div class="card-left">
              <h3 class="medicine"><?php echo htmlspecialchars($pres['medicine'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></h3>
              <p class="small muted">Prescribed by <strong><?php echo htmlspecialchars($pres['doctor_name'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></strong></p>
              <p class="small muted">First taken at <strong><?php echo htmlspecialchars($dateDisplay, ENT_QUOTES, 'UTF-8'); ?></strong></p>
            </div>
            <div class="card-right">
              <p class="note"><?php echo htmlspecialchars($pres['notes'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></p>
              <a class="details-link" href="view_prescription.php?id=<?php echo (int)$pres['prescriptionID']; ?>">Medicine Details &gt;&gt;</a>
            </div>
          </article>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="empty-state"><p>No active prescriptions found.</p></div>
      <?php endif; ?>
    </div>

    <?php if ($totalPages > 1): ?>
      <nav class="pagination">
        <?php if ($currentPage > 1): ?>
          <a class="page-link" href="?page=<?php echo $currentPage - 1; ?>">&laquo; Prev</a>
        <?php else: ?>
          <span class="page-link disabled">&laquo; Prev</span>
        <?php endif; ?>

        <span class="page-info">Page <?php echo $currentPage; ?> of <?php echo $totalPages; ?></span>

        <?php if ($currentPage < $totalPages): ?>
          <a class="page-link" href="?page=<?php echo $currentPage + 1; ?>">Next &raquo;</a>
        <?php else: ?>
          <span class="page-link disabled">Next &raquo;</span>
        <?php endif; ?>
      </nav>
    <?php endif; ?>
  </section>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/patient_standard.php';
