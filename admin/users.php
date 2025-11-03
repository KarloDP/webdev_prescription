<?php
require_once "../includes/db_connect.php";
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Accept doctor
if (isset($_GET['accept_id'])) {
    $id = intval($_GET['accept_id']);
    if ($id > 0) {
        $conn->query("UPDATE doctor SET status='active' WHERE doctorID = $id");
    }
    header("Location: users.php?filter=pending");
    exit;
}

// Delete doctor
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    $ref = $_GET['filter'] ?? 'active';
    if ($id > 0) {
        $conn->query("DELETE FROM doctor WHERE doctorID = $id");
    }
    header("Location: users.php?filter=" . urlencode($ref));
    exit;
}

// Update doctor
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_doctor'])) {
    $id       = intval($_POST['doctorID']);
    $first    = $conn->real_escape_string($_POST['firstName']);
    $last     = $conn->real_escape_string($_POST['lastName']);
    $spec     = $conn->real_escape_string($_POST['specialization']);
    $license  = $conn->real_escape_string($_POST['licenseNumber']);
    $email    = $conn->real_escape_string($_POST['email']);
    $clinic   = $conn->real_escape_string($_POST['clinicAddress']);

    $sql = "UPDATE doctor 
            SET firstName='$first', lastName='$last', specialization='$spec',
                licenseNumber='$license', email='$email', clinicAddress='$clinic'
            WHERE doctorID=$id";
    $conn->query($sql);

    header("Location: users.php?filter=" . urlencode($_GET['filter'] ?? 'active'));
    exit;
}

$filter = $_GET['filter'] ?? 'active';
$search = $_GET['search'] ?? '';
$edit_id = isset($_GET['edit_id']) ? intval($_GET['edit_id']) : null;

$title = ($filter === 'pending') ? "Pending Doctor Requests" : "Active Doctors";
$query = ($filter === 'pending')
    ? "SELECT * FROM doctor WHERE status='pending'"
    : "SELECT * FROM doctor WHERE status='active'";

if ($search !== '') {
    $safe = $conn->real_escape_string($search);
    $query .= " AND (firstName LIKE '%$safe%' OR lastName LIKE '%$safe%' OR doctorID LIKE '%$safe%' 
                OR email LIKE '%$safe%' OR specialization LIKE '%$safe%' 
                OR licenseNumber LIKE '%$safe%' OR clinicAddress LIKE '%$safe%')";
}
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html>
<head>
  <title><?= htmlspecialchars($title) ?></title>
  <style>
    .btn { padding:6px 12px; margin:2px; border-radius:4px; cursor:pointer; text-decoration:none; display:inline-block; }
    .btn-warning { background:orange; color:white; }
    .btn-danger { background:red; color:white; }
    .btn-success { background:green; color:white; }
    .btn-secondary { background:gray; color:white; }
    .btn-primary { background:blue; color:white; }
    table { border-collapse: collapse; width: 100%; margin-top:10px; }
    th, td { border:1px solid #ddd; padding:8px; }
    th { background:#f9f9f9; }
    input[type=text], input[type=email] { width:100%; padding:4px; box-sizing:border-box; }
    .modal { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:1000; }
    .modal-content { background:#fff; margin:auto; margin-top:12%; padding:20px; border-radius:6px; width:340px; text-align:center; }
    .modal-buttons { margin-top:16px; display:flex; justify-content:center; gap:12px; }
    .highlight { background:#fff8dc; } /* highlight edit row */
  </style>
</head>
<body>
  <h2><?= htmlspecialchars($title) ?></h2>

  <div style="display:flex; justify-content:space-between; margin-bottom:20px;">
    <form method="GET" action="users.php" style="display:flex; gap:8px; align-items:center;">
      <input type="hidden" name="filter" value="<?= htmlspecialchars($filter) ?>">
      <input type="text" name="search" placeholder="Search for Doctor" value="<?= htmlspecialchars($search) ?>" style="padding:8px; width:260px;">
      <button type="submit" class="btn btn-primary">Search</button>
    </form>
    <div>
      <?php if ($filter === 'pending'): ?>
        <a href="users.php?filter=active" class="btn btn-success">Active Doctors</a>
      <?php else: ?>
        <a href="users.php?filter=pending" class="btn btn-danger">Pending Requests</a>
      <?php endif; ?>
    </div>
  </div>

  <table>
    <tr>
      <th>ID</th>
      <th>First Name</th>
      <th>Last Name</th>
      <th>Specialization</th>
      <th>License #</th>
      <th>Email</th>
      <th>Clinic Address</th>
      <th></th>
    </tr>
    <?php while($row = $result->fetch_assoc()): ?>
      <?php $id = (int)$row['doctorID']; ?>
      <?php if ($edit_id === $id): ?>
        <!-- Edit mode row -->
        <form method="POST" action="users.php?filter=<?= htmlspecialchars($filter) ?>" id="form-<?= $id ?>">
          <input type="hidden" name="doctorID" value="<?= $id ?>">
          <tr class="highlight">
            <td><?= $id ?></td>
            <td><input type="text" name="firstName" value="<?= htmlspecialchars($row['firstName']) ?>"></td>
            <td><input type="text" name="lastName" value="<?= htmlspecialchars($row['lastName']) ?>"></td>
            <td><input type="text" name="specialization" value="<?= htmlspecialchars($row['specialization']) ?>"></td>
            <td><input type="text" name="licenseNumber" value="<?= htmlspecialchars($row['licenseNumber']) ?>"></td>
            <td><input type="email" name="email" value="<?= htmlspecialchars($row['email']) ?>"></td>
            <td><input type="text" name="clinicAddress" value="<?= htmlspecialchars($row['clinicAddress']) ?>"></td>
            <td style="text-align:right;">
              <button type="button" class="btn btn-warning" onclick="return openConfirm('Save changes to this doctor?', document.getElementById('form-<?= $id ?>'))">Save</button>
              <a href="users.php?filter=<?= htmlspecialchars($filter) ?>" class="btn btn-secondary">Cancel</a>
            </td>
          </tr>
        </form>
      <?php else: ?>
        <!-- Read-only row -->
        <tr>
          <td><?= $id ?></td>
          <td><?= htmlspecialchars($row['firstName']) ?></td>
          <td><?= htmlspecialchars($row['lastName']) ?></td>
          <td><?= htmlspecialchars($row['specialization']) ?></td>
          <td><?= htmlspecialchars($row['licenseNumber']) ?></td>
          <td><?= htmlspecialchars($row['email']) ?></td>
          <td><?= htmlspecialchars($row['clinicAddress']) ?></td>
          <td style="text-align:right;">
            <?php if ($row['status'] === 'pending'): ?>
              <a href="users.php?accept_id=<?= $id ?>&filter=pending" class="btn btn-success" onclick="return openConfirm('Accept this doctor?', this.href)">Accept</a>
              <a href="users.php?delete_id=<?= $id ?>&filter=pending" class="btn btn-secondary" onclick="return openConfirm('Reject this doctor?', this.href)">Reject</a>
            <?php else: ?>
              <a href="users.php?edit_id=<?= $id ?>&filter=<?= htmlspecialchars($filter) ?>" class="btn btn-warning">Edit</a>
              <a href="users.php?delete_id=<?= $id ?>&filter=active" class="btn btn-danger" onclick="return openConfirm('Delete this doctor?', this.href)">Delete</a>
            <?php endif; ?>
          </td>
        </tr>
      <?php endif; ?>
    <?php endwhile; ?>
  </table>

  <!-- Confirmation Modal -->
  <div id="confirmModal" class="modal">
    <div class="modal-content">
      <div id="confirmText">Are you sure?</div>
      <div class="modal-buttons">
                <button class="btn btn-primary" id="confirmYes">Yes</button>
        <button class="btn btn-danger" id="confirmNo">No</button>
      </div>
    </div>
  </div>

  <script>
  let pendingHref = null;
  let pendingForm = null;

  function openConfirm(message, target) {
    document.getElementById('confirmText').textContent = message;
    document.getElementById('confirmModal').style.display = 'block';

    if (target && target.tagName === 'FORM') {
      pendingForm = target;
      pendingHref = null;
    } else {
      pendingHref = target;
      pendingForm = null;
    }
    return false;
  }

  document.getElementById('confirmYes').addEventListener('click', function() {
    document.getElementById('confirmModal').style.display = 'none';
    if (pendingHref) {
      window.location.href = pendingHref;
    } else if (pendingForm) {
      pendingForm.submit();
    }
  });

  document.getElementById('confirmNo').addEventListener('click', function() {
    document.getElementById('confirmModal').style.display = 'none';
    pendingHref = null;
    pendingForm = null;
  });
  </script>
</body>
</html>
