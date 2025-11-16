<?php
session_start();

/*
  Patient dashboard (patient/dashboard.php)
  - Scoped to .patient-dashboard to avoid global CSS conflicts
  - Includes placeholders and commented markers where your team should
    replace with real DB queries and dynamic values
  - Uses includes/db_connect.php for a database connection (confirmed)
  - Implements a simple pagination scaffold (commented) for later hookup
*/

// Active page used by standard layout for sidebar highlighting
$activePage = 'dashboard';

// --- CONTENT BUFFER (keeps layout_standard / patient_standard usage simple) ---
ob_start();
?>

<div class="patient-dashboard">
  <div class="welcome-row">
    <div class="welcome-card">
      <?php
        // Display patient name from session (placeholder fallback)
        $patientName = $_SESSION['patient_name'] ?? 'Patient';
      ?>
      <h1>Welcome <span class="name"><?php echo htmlspecialchars(strtoupper($patientName)); ?> !!</span></h1>
      <p class="subtitle">View Prescriptions. Manage medications and pharmacies.</p>
      <div class="welcome-actions">
        <a class="btn btn-primary" href="patient/medication.php">View Medications</a>
        <a class="btn btn-outline" href="patient/pharmacies.php">Find Pharmacies</a>
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
        <a class="link" href="patient/prescriptions.php">View Details</a>
      </div>
    </div>
  </div>

  <section class="prescriptions-section">
    <h2 class="section-title">Active Prescriptions</h2>

    <div class="cards-grid">
      <?php
        /*
          DATA SOURCE (placeholder)
          -------------------------------------------------------
          Replace the following placeholder block with a DB query.
          Example (pseudo-code):

          require_once __DIR__ . '/../includes/db_connect.php';
          $patientId = $_SESSION['patient_id']; // ensure this exists
          // Prepare query to fetch active prescriptions only (adjust columns)
          $sql = "SELECT id, medicine, prescribed_at, doctor_name, notes FROM prescription
                  WHERE patient_id = ? AND status = 'active'
                  ORDER BY prescribed_at DESC
                  LIMIT ? OFFSET ?";
          // Bind parameters and execute, then loop the results below.
          -------------------------------------------------------
        */

        // Placeholder sample data (remove after hooking DB)
        $sample = [
          ['id'=>1,'medicine'=>'Ibuprofen','prescribed_at'=>'2025-01-05','doctor_name'=>'Dr Tolentino','notes'=>'First time use'],
          ['id'=>2,'medicine'=>'Amoxicillin','prescribed_at'=>'2025-03-12','doctor_name'=>'Dr Reyes','notes'=>'Take after meals'],
          ['id'=>3,'medicine'=>'Paracetamol','prescribed_at'=>'2025-06-01','doctor_name'=>'Dr Cruz','notes'=>'As needed'],
          ['id'=>4,'medicine'=>'Cetirizine','prescribed_at'=>'2025-08-21','doctor_name'=>'Dr Santos','notes'=>'Once daily'],
        ];

        // Pagination scaffold (server-side)
        $perPage = 8; // adjust as desired
        $currentPage = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $offset = ($currentPage - 1) * $perPage;

        // If using DB, fetch total count and items using LIMIT/OFFSET using $perPage and $offset
        // $totalItems = ...; $totalPages = ceil($totalItems / $perPage);

        // For placeholder we paginate the sample array manually
        $totalItems = count($sample);
        $totalPages = (int)ceil($totalItems / $perPage);
        $display = array_slice($sample, $offset, $perPage);

        foreach ($display as $pres) :
          // Format date
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
            <!-- Placeholder detail link: replace with real page -->
            <a class="details-link" href="patient/view_prescription.php?id=<?php echo (int)$pres['id']; ?>">Medicine Details &gt;&gt;</a>
          </div>
        </article>
      <?php endforeach; ?>
    </div>

    <!-- Pagination controls (server-side) -->
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

// Include patient standard layout (this file should call $content in its main area)
// Try patient_standard.php first; if your app uses layout_standard.php instead, swap the include
include __DIR__ . '/patient_standard.php';
