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
}

// Active page used by standard layout for sidebar highlighting
$activePage = 'dashboard';

// --- CONTENT BUFFER (keeps layout_standard / patient_standard usage simple) ---
ob_start();
?>

<div class="patient-dashboard">
  <div class="welcome-row">
    <div class="welcome-card">
      <?php
        // Use the session-stored patient name (populated above)
        $patientName = $_SESSION['patient_name'];
      ?>
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
          <div class="stat-number" id="activeCount">4</div>
          <div class="stat-label">Active Prescriptions</div>
        </div>
        <div class="stat">
          <div class="stat-number" id="refillCount">2</div>
          <div class="stat-label">Upcoming Refills</div>
        </div>
        <div class="stat">
          <div class="stat-number" id="nearbyPharmacies">3</div>
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
      <?php
        /* Placeholder sample data.
           Replace with a DB query that selects active prescriptions for the logged-in patient.
           Example:
             $sql = "SELECT p.prescriptionID, m.genericName, d.firstName, d.lastName, p.issueDate, pi.dosage
                     FROM prescription p
                     JOIN prescriptionitem pi ON p.prescriptionID = pi.prescriptionID
                     JOIN medication m ON pi.medicationID = m.medicationID
                     JOIN doctor d ON p.doctorID = d.doctorID
                     WHERE p.patientID = ? AND p.status = 'Active'
                     ORDER BY p.issueDate DESC
                     LIMIT ? OFFSET ?";
        */

        $sample = [
          ['id'=>1,'medicine'=>'Ibuprofen','prescribed_at'=>'2025-01-05','doctor_name'=>'Dr Tolentino','notes'=>'First time use'],
          ['id'=>2,'medicine'=>'Amoxicillin','prescribed_at'=>'2025-03-12','doctor_name'=>'Dr Reyes','notes'=>'Take after meals'],
          ['id'=>3,'medicine'=>'Paracetamol','prescribed_at'=>'2025-06-01','doctor_name'=>'Dr Cruz','notes'=>'As needed'],
          ['id'=>4,'medicine'=>'Cetirizine','prescribed_at'=>'2025-08-21','doctor_name'=>'Dr Santos','notes'=>'Once daily'],
        ];

        $perPage = 8;
        $currentPage = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $offset = ($currentPage - 1) * $perPage;

        $totalItems = count($sample);
        $totalPages = (int)ceil($totalItems / $perPage);
        $display = array_slice($sample, $offset, $perPage);

        foreach ($display as $pres) :
          $dateDisplay = date("F j", strtotime($pres['prescribed_at']));
      ?>
        <article class="prescription-card">
          <div class="card-left">
            <h3 class="medicine"><?php echo htmlspecialchars($pres['medicine']); ?></h3>
            <p class="small muted">Prescribed by <strong><?php echo htmlspecialchars($pres['doctor_name']); ?></strong></p>
            <p class="small muted">First taken at <strong><?php echo htmlspecialchars($dateDisplay); ?></strong></p>
          </div>
          <div class="card-right">
            <p class="note"><?php echo htmlspecialchars($pres['notes']); ?></p>
            <a class="details-link" href="view_prescription.php?id=<?php echo (int)$pres['id']; ?>">Medicine Details &gt;&gt;</a>
          </div>
        </article>
      <?php endforeach; ?>
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