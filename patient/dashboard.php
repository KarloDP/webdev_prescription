<?php
session_start();
include __DIR__ . '/../includes/auth.php';
include __DIR__ . '/../includes/db_connect.php';

// Redirect if not logged in as patient
if (!isset($_SESSION['patientID'])) {
    header('Location: ../TestLoginPatient.php');
    exit;
}

// Ensure patient name is loaded into session for consistent display across pages
if (empty($_SESSION['patient_name'])) {
    $patientID = (int) $_SESSION['patientID'];
    $stmt = $conn->prepare("SELECT firstName, lastName FROM patient WHERE patientID = ?");
    $stmt->bind_param("i", $patientID);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res && $res->num_rows > 0) {
        $row = $res->fetch_assoc();
        $_SESSION['patient_name'] = trim($row['firstName'] . ' ' . $row['lastName']);
    } else {
        $_SESSION['patient_name'] = 'Patient';
    }
    $stmt->close();
}

// Active page used by standard layout for sidebar highlighting
$activePage = 'dashboard';

// Prepare stats and active prescriptions data
$pid = (int) $_SESSION['patientID'];

// Active prescriptions count
$activeCount = 0;
$countStmt = $conn->prepare("
  SELECT COUNT(DISTINCT p.prescriptionID) AS cnt
  FROM prescription p
  WHERE p.patientID = ? AND LOWER(p.status) = 'active'
");
if ($countStmt) {
    $countStmt->bind_param("i", $pid);
    $countStmt->execute();
    $countRes = $countStmt->get_result();
    $activeCount = ($countRes && $countRes->num_rows) ? (int)$countRes->fetch_assoc()['cnt'] : 0;
    $countStmt->close();
}

// Upcoming refills (example: expirationDate within next 14 days)
$refillCount = 0;
$refillStmt = $conn->prepare("
  SELECT COUNT(DISTINCT prescriptionID) AS cnt
  FROM prescription
  WHERE patientID = ? AND expirationDate BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 14 DAY)
");
if ($refillStmt) {
    $refillStmt->bind_param("i", $pid);
    $refillStmt->execute();
    $refillRes = $refillStmt->get_result();
    $refillCount = ($refillRes && $refillRes->num_rows) ? (int)$refillRes->fetch_assoc()['cnt'] : 0;
    $refillStmt->close();
}

// Nearby pharmacies (simple total; replace with geolocation logic if available)
$nearbyPharmacies = 0;
$pharmStmt = $conn->prepare("SELECT COUNT(*) AS cnt FROM pharmacy");
if ($pharmStmt) {
    $pharmStmt->execute();
    $pharmRes = $pharmStmt->get_result();
    $nearbyPharmacies = ($pharmRes && $pharmRes->num_rows) ? (int)$pharmRes->fetch_assoc()['cnt'] : 0;
    $pharmStmt->close();
}

// Pagination settings
$perPage = 8;
$currentPage = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($currentPage - 1) * $perPage;

// Fetch active prescriptions (one representative prescriptionitem per prescription)
$prescriptions = [];
$totalPages = 0;
if ($activeCount > 0) {
    $totalPages = (int)ceil($activeCount / $perPage);

    $sql = "
      SELECT
        p.prescriptionID,
        MIN(pi.prescriptionItemID) AS sampleItemID,
        m.genericName AS medicine,
        CONCAT('Dr ', d.firstName, ' ', d.lastName) AS doctorName,
        pi.dosage AS dosage,
        p.issueDate
      FROM prescription p
      JOIN prescriptionitem pi ON p.prescriptionID = pi.prescriptionID
      JOIN medication m ON pi.medicationID = m.medicationID
      JOIN doctor d ON p.doctorID = d.doctorID
      WHERE p.patientID = ? AND LOWER(p.status) = 'active'
      GROUP BY p.prescriptionID
      ORDER BY p.issueDate DESC
      LIMIT ? OFFSET ?
    ";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("iii", $pid, $perPage, $offset);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $prescriptions[] = $row;
        }
        $stmt->close();
    }
}

// --- CONTENT BUFFER (keeps layout_standard / patient_standard usage simple) ---
ob_start();
?>

<div class="patient-dashboard">
  <div class="welcome-row">
    <div class="welcome-card">
      <?php $patientName = $_SESSION['patient_name']; ?>
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
          <div class="stat-number" id="activeCount"><?php echo $activeCount; ?></div>
          <div class="stat-label">Active Prescriptions</div>
        </div>
        <div class="stat">
          <div class="stat-number" id="refillCount"><?php echo $refillCount; ?></div>
          <div class="stat-label">Upcoming Refills</div>
        </div>
        <div class="stat">
          <div class="stat-number" id="nearbyPharmacies"><?php echo $nearbyPharmacies; ?></div>
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
        <?php foreach ($prescriptions as $row): ?>
          <?php
            $presID = (int)$row['prescriptionID'];
            $medicine = htmlspecialchars($row['medicine'] ?? '-', ENT_QUOTES, 'UTF-8');
            $doctor = htmlspecialchars($row['doctorName'] ?? '-', ENT_QUOTES, 'UTF-8');
            $dosage = htmlspecialchars($row['dosage'] ?? '-', ENT_QUOTES, 'UTF-8');
            $issueDate = !empty($row['issueDate']) ? date("F j, Y", strtotime($row['issueDate'])) : '-';
          ?>
          <article class="prescription-card">
            <div class="card-left">
              <h3 class="medicine"><?php echo $medicine; ?></h3>
              <p class="small muted">Prescribed by <strong><?php echo $doctor; ?></strong></p>
              <p class="small muted">First taken at <strong><?php echo $issueDate; ?></strong></p>
            </div>
            <div class="card-right">
              <p class="note"><?php echo $dosage; ?></p>
              <a class="details-link" href="view_prescription.php?id=<?php echo $presID; ?>">Medicine Details &gt;&gt;</a>
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

// Include patient standard layout (this file should echo $content in its main area)
include __DIR__ . '/patient_standard.php';
