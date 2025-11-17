<?php
session_start();
include __DIR__ . '/../includes/auth.php';
include __DIR__ . '/../includes/db_connect.php';

if (!isset($_SESSION['patientID'])) {
    header('Location: ../TestLoginPatient.php');
    exit;
}

$activePage = 'dashboard';
$patientID = (int) $_SESSION['patientID'];
$patientName = $_SESSION['patient_name'] ?? 'Patient';

// Pagination setup
$perPage = 8;
$currentPage = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($currentPage - 1) * $perPage;

// Count total active prescriptions
$countStmt = $conn->prepare("
  SELECT COUNT(DISTINCT p.prescriptionID) AS cnt
  FROM prescription p
  WHERE p.patientID = ? AND LOWER(p.status) = 'active'
");
$countStmt->bind_param("i", $patientID);
$countStmt->execute();
$countRes = $countStmt->get_result();
$totalItems = ($countRes && $countRes->num_rows) ? (int)$countRes->fetch_assoc()['cnt'] : 0;
$countStmt->close();
$totalPages = (int)ceil($totalItems / $perPage);

// Fetch active prescriptions
$prescriptions = [];
if ($totalItems > 0) {
    $stmt = $conn->prepare("
      SELECT
        p.prescriptionID,
        m.genericName AS medicine,
        CONCAT('Dr ', d.firstName, ' ', d.lastName) AS doctor_name,
        pi.dosage AS notes,
        p.issueDate AS prescribed_at
      FROM prescription p
      JOIN prescriptionitem pi ON p.prescriptionID = pi.prescriptionID
      JOIN medication m ON pi.medicationID = m.medicationID
      JOIN doctor d ON p.doctorID = d.doctorID
      WHERE p.patientID = ? AND LOWER(p.status) = 'active'
      GROUP BY p.prescriptionID
      ORDER BY p.issueDate DESC
      LIMIT ? OFFSET ?
    ");
    $stmt->bind_param("iii", $patientID, $perPage, $offset);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $prescriptions[] = $row;
    }
    $stmt->close();
}

// --- CONTENT BUFFER ---
ob_start();
?>

<div class="patient-dashboard">
  <div class="welcome-row">
    <div class="welcome-card">
      <h1>Welcome <span class="name"><?php echo htmlspecialchars(strtoupper($patientName)); ?> !!</span></h1>
      <p class="subtitle">View Prescriptions. Manage medications and pharmacies.</p>
      <div class="welcome-actions">
        <a class="btn btn-primary" href="medication.php">View Medications</a>
        <a class="btn btn-outline" href="pharmacies.php">Find Pharmacies</a>
      </div>
    </div>

    <div class="stats-card">
      <div class="stats-row">
        <div class="stat">
          <div class="stat-number" id="activeCount"><?php echo $totalItems; ?></div>
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
          <?php $dateDisplay = date("F j, Y", strtotime($pres['prescribed_at'])); ?>
          <article class="prescription-card">
            <div class="card-left">
              <h3 class="medicine"><?php echo htmlspecialchars($pres['medicine']); ?></h3>
              <p class="small muted">Prescribed by <strong><?php echo htmlspecialchars($pres['doctor_name']); ?></strong></p>
              <p class="small muted">First taken at <strong><?php echo htmlspecialchars($dateDisplay); ?></strong></p>
            </div>
            <div class="card-right">
              <p class="note"><?php echo htmlspecialchars($pres['notes']); ?></p>
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

