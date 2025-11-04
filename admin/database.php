<?php
require_once "../includes/db_connect.php";
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Delete actions
if (isset($_GET['delete_id']) && isset($_GET['view'])) {
    $id = intval($_GET['delete_id']);
    $view = $_GET['view'];

    if ($view === 'medicines') {
        $conn->query("DELETE FROM medication WHERE medicationID = $id");
    } elseif ($view === 'prescriptions') {
        $conn->query("DELETE FROM prescription WHERE prescriptionID = $id");
    } elseif ($view === 'pharmacys') {
        $conn->query("DELETE FROM pharmacy WHERE pharmacyID = $id");
    }

    header("Location: database.php?view=" . urlencode($view));
    exit;
}

// Update / Insert actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_record'])) {
    $view = $_POST['view'] ?? '';

    if ($view === 'medicines') {
        // Insert if id empty, else update
        $id      = isset($_POST['id']) && $_POST['id'] !== '' ? intval($_POST['id']) : null;
        $generic = $conn->real_escape_string($_POST['genericName'] ?? '');
        $brand   = $conn->real_escape_string($_POST['brandName'] ?? '');
        $form    = $conn->real_escape_string($_POST['form'] ?? '');
        $strength= $conn->real_escape_string($_POST['strength'] ?? '');
        $manuf   = $conn->real_escape_string($_POST['manufacturer'] ?? '');
        $stock   = intval($_POST['stock'] ?? 0);

        if ($id) {
            $conn->query("UPDATE medication 
                          SET genericName='$generic', brandName='$brand', form='$form', 
                              strength='$strength', manufacturer='$manuf', stock=$stock
                          WHERE medicationID=$id");
        } else {
            $conn->query("INSERT INTO medication (genericName, brandName, form, strength, manufacturer, stock)
                          VALUES ('$generic', '$brand', '$form', '$strength', '$manuf', $stock)");
        }
    } elseif ($view === 'prescriptions') {
        // Prescriptions only update existing rows
        $id        = intval($_POST['id'] ?? 0);
        $med       = intval($_POST['medicationID'] ?? 0);
        $patient   = intval($_POST['patientID'] ?? 0);
        $issueDate = $conn->real_escape_string($_POST['issueDate'] ?? '');
        $expDate   = $conn->real_escape_string($_POST['expirationDate'] ?? '');
        $status    = $conn->real_escape_string($_POST['status'] ?? '');

        $conn->query("UPDATE prescription 
                      SET medicationID=$med, patientID=$patient, issueDate='$issueDate', 
                          expirationDate='$expDate', status='$status'
                      WHERE prescriptionID=$id");
    } elseif ($view === 'pharmacys') {
        // Insert if id empty, else update
        $id      = isset($_POST['id']) && $_POST['id'] !== '' ? intval($_POST['id']) : null;
        $name    = $conn->real_escape_string($_POST['name'] ?? '');
        $address = $conn->real_escape_string($_POST['address'] ?? '');
        $contact = $conn->real_escape_string($_POST['contactNumber'] ?? '');
        $email   = $conn->real_escape_string($_POST['email'] ?? '');
        $clinic  = $conn->real_escape_string($_POST['clinicAddress'] ?? '');

        if ($id) {
            $conn->query("UPDATE pharmacy 
                          SET name='$name', address='$address', contactNumber='$contact', 
                              email='$email', clinicAddress='$clinic'
                          WHERE pharmacyID=$id");
        } else {
            $conn->query("INSERT INTO pharmacy (name, address, contactNumber, email, clinicAddress)
                          VALUES ('$name', '$address', '$contact', '$email', '$clinic')");
        }
    }

    header("Location: database.php?view=" . urlencode($view));
    exit;
}

// View & state
$view = $_GET['view'] ?? 'medicines';
$search = $_GET['search'] ?? '';
$edit_id = isset($_GET['edit_id']) ? intval($_GET['edit_id']) : null;
$add_mode = isset($_GET['add']) ? true : false;

// Query builder
switch ($view) {
    case 'prescriptions':
        $title = "Prescriptions";
        $query = "SELECT * FROM prescription WHERE 1";
        break;
    case 'pharmacys':
        $title = "Pharmacys";
        $query = "SELECT * FROM pharmacy WHERE 1";
        break;
    default:
        $title = "Medicines";
        $view = 'medicines';
        $query = "SELECT * FROM medication WHERE 1";
}
if ($search !== '') {
    $safe = $conn->real_escape_string($search);
    if ($view === 'medicines') {
        $query .= " AND (medicationID LIKE '%$safe%' OR genericName LIKE '%$safe%' OR brandName LIKE '%$safe%' 
                    OR form LIKE '%$safe%' OR strength LIKE '%$safe%' OR manufacturer LIKE '%$safe%')";
    } elseif ($view === 'prescriptions') {
        $query .= " AND (prescriptionID LIKE '%$safe%' OR medicationID LIKE '%$safe%' OR patientID LIKE '%$safe%' 
                    OR issueDate LIKE '%$safe%' OR expirationDate LIKE '%$safe%' OR status LIKE '%$safe%')";
    } elseif ($view === 'pharmacys') {
        $query .= " AND (pharmacyID LIKE '%$safe%' OR name LIKE '%$safe%' OR address LIKE '%$safe%' 
                    OR contactNumber LIKE '%$safe%' OR email LIKE '%$safe%' OR clinicAddress LIKE '%$safe%')";
    }
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
    input[type=text], input[type=number], input[type=date], input[type=email] { width:100%; padding:4px; box-sizing:border-box; }
    .modal { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:1000; }
    .modal-content { background:#fff; margin:auto; margin-top:12%; padding:20px; border-radius:6px; width:340px; text-align:center; }
    .modal-buttons { margin-top:16px; display:flex; justify-content:center; gap:12px; }
    .highlight { background:#fff8dc; }
  </style>
</head>
<body>
  <h2><?= htmlspecialchars($title) ?></h2>

  <div style="display:flex; justify-content:space-between; margin-bottom:20px;">
    <form method="GET" action="database.php" style="display:flex; gap:8px; align-items:center;">
      <input type="hidden" name="view" value="<?= htmlspecialchars($view) ?>">
      <input type="text" name="search" placeholder="Search <?= htmlspecialchars($title) ?>" value="<?= htmlspecialchars($search) ?>" style="padding:8px; width:260px;">
      <button type="submit" class="btn btn-primary">Search</button>
    </form>

    <div>
      <?php if ($view === 'medicines'): ?>
        <a href="database.php?view=medicines&add=1" class="btn btn-success">Add Medicine</a>
      <?php elseif ($view === 'pharmacys'): ?>
        <a href="database.php?view=pharmacys&add=1" class="btn btn-success">Add Pharmacy</a>
      <?php endif; ?>
    </div>
  </div>

  <table>
    <?php if ($view === 'medicines'): ?>
      <tr>
        <th>ID</th>
        <th>Generic Name</th>
        <th>Brand Name</th>
        <th>Form</th>
        <th>Strength</th>
        <th>Manufacturer</th>
        <th>Stock</th>
        <th></th>
      </tr>

      <?php if ($add_mode && $view === 'medicines'): ?>
        <!-- Inline add new medicine row -->
        <form method="POST" action="database.php?view=medicines" id="form-add-medicine">
          <input type="hidden" name="view" value="medicines">
          <input type="hidden" name="id" value="">
          <input type="hidden" name="update_record" value="1">
          <tr class="highlight">
            <td>New</td>
            <td><input type="text" name="genericName" placeholder="Generic Name"></td>
            <td><input type="text" name="brandName" placeholder="Brand Name"></td>
            <td><input type="text" name="form" placeholder="Form"></td>
            <td><input type="text" name="strength" placeholder="Strength"></td>
            <td><input type="text" name="manufacturer" placeholder="Manufacturer"></td>
            <td><input type="number" name="stock" placeholder="Stock"></td>
            <td style="text-align:right;">
              <button type="button" class="btn btn-success"
                onclick="return openConfirm('Add this new medicine?', document.getElementById('form-add-medicine'))">Save</button>
              <a href="database.php?view=medicines" class="btn btn-secondary">Cancel</a>
            </td>
          </tr>
        </form>
      <?php endif; ?>

      <?php while($row = $result->fetch_assoc()): $id = $row['medicationID']; ?>
        <!-- Read-only medicine row -->
        <tr>
          <td><?= $id ?></td>
          <td><?= htmlspecialchars($row['genericName']) ?></td>
          <td><?= htmlspecialchars($row['brandName']) ?></td>
          <td><?= htmlspecialchars($row['form']) ?></td>
          <td><?= htmlspecialchars($row['strength']) ?></td>
          <td><?= htmlspecialchars($row['manufacturer']) ?></td>
          <td><?= htmlspecialchars($row['stock']) ?></td>
          <td style="text-align:right;">
            <a href="database.php?view=medicines&delete_id=<?= $id ?>" class="btn btn-danger"
               onclick="return openConfirm('Delete this medicine?', this.href)">Delete</a>
          </td>
        </tr>
      <?php endwhile; ?>
    <?php elseif ($view === 'prescriptions'): ?>
      <tr>
        <th>ID</th>
        <th>Medication</th>
        <th>Patient</th>
        <th>Issue Date</th>
        <th>Expiration Date</th>
        <th>Status</th>
        <th></th>
      </tr>

      <?php while($row = $result->fetch_assoc()): $id = $row['prescriptionID']; ?>
        <?php if ($edit_id === (int)$id): ?>
          <!-- Edit-mode prescription row -->
          <form method="POST" action="database.php?view=prescriptions" id="form-<?= $id ?>">
            <input type="hidden" name="view" value="prescriptions">
            <input type="hidden" name="id" value="<?= $id ?>">
            <input type="hidden" name="update_record" value="1">
            <tr class="highlight">
              <td><?= $id ?></td>
              <td><input type="number" name="medicationID" value="<?= htmlspecialchars($row['medicationID']) ?>"></td>
              <td><input type="number" name="patientID" value="<?= htmlspecialchars($row['patientID']) ?>"></td>
              <td><input type="date" name="issueDate" value="<?= htmlspecialchars($row['issueDate']) ?>"></td>
              <td><input type="date" name="expirationDate" value="<?= htmlspecialchars($row['expirationDate']) ?>"></td>
              <td><input type="text" name="status" value="<?= htmlspecialchars($row['status']) ?>"></td>
              <td style="text-align:right;">
                <button type="button" class="btn btn-warning"
                  onclick="return openConfirm('Save changes to this prescription?', document.getElementById('form-<?= $id ?>'))">Save</button>
                <a href="database.php?view=prescriptions" class="btn btn-secondary">Cancel</a>
              </td>
            </tr>
          </form>
        <?php else: ?>
          <!-- Read-only prescription row -->
          <tr>
            <td><?= $id ?></td>
            <td><?= htmlspecialchars($row['medicationID']) ?></td>
            <td><?= htmlspecialchars($row['patientID']) ?></td>
            <td><?= htmlspecialchars($row['issueDate']) ?></td>
            <td><?= htmlspecialchars($row['expirationDate']) ?></td>
            <td><?= htmlspecialchars($row['status']) ?></td>
            <td style="text-align:right;">
              <a href="database.php?view=prescriptions&edit_id=<?= $id ?>" class="btn btn-warning">Edit</a>
              <a href="database.php?view=prescriptions&delete_id=<?= $id ?>" class="btn btn-danger"
                 onclick="return openConfirm('Delete this prescription?', this.href)">Delete</a>
            </td>
          </tr>
        <?php endif; ?>
      <?php endwhile; ?>
    <?php elseif ($view === 'pharmacys'): ?>
      <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Address</th>
        <th>Contact Number</th>
        <th>Email</th>
        <th>Clinic Address</th>
        <th></th>
      </tr>

      <?php if ($add_mode && $view === 'pharmacys'): ?>
        <!-- Inline add new pharmacy row -->
        <form method="POST" action="database.php?view=pharmacys" id="form-add-pharmacy">
          <input type="hidden" name="view" value="pharmacys">
          <input type="hidden" name="id" value="">
          <input type="hidden" name="update_record" value="1">
          <tr class="highlight">
            <td>New</td>
            <td><input type="text" name="name" placeholder="Pharmacy Name"></td>
            <td><input type="text" name="address" placeholder="Address"></td>
            <td><input type="text" name="contactNumber" placeholder="Contact Number"></td>
            <td><input type="email" name="email" placeholder="Email"></td>
            <td><input type="text" name="clinicAddress" placeholder="Clinic Address"></td>
            <td style="text-align:right;">
              <button type="button" class="btn btn-success"
                onclick="return openConfirm('Add this new pharmacy?', document.getElementById('form-add-pharmacy'))">Save</button>
              <a href="database.php?view=pharmacys" class="btn btn-secondary">Cancel</a>
            </td>
          </tr>
        </form>
      <?php endif; ?>

      <?php while($row = $result->fetch_assoc()): $id = $row['pharmacyID']; ?>
        <?php if ($edit_id === (int)$id): ?>
          <!-- Edit-mode pharmacy row -->
          <form method="POST" action="database.php?view=pharmacys" id="form-<?= $id ?>">
            <input type="hidden" name="view" value="pharmacys">
            <input type="hidden" name="id" value="<?= $id ?>">
            <input type="hidden" name="update_record" value="1">
            <tr class="highlight">
              <td><?= $id ?></td>
              <td><input type="text" name="name" value="<?= htmlspecialchars($row['name']) ?>"></td>
              <td><input type="text" name="address" value="<?= htmlspecialchars($row['address']) ?>"></td>
              <td><input type="text" name="contactNumber" value="<?= htmlspecialchars($row['contactNumber']) ?>"></td>
              <td><input type="email" name="email" value="<?= htmlspecialchars($row['email']) ?>"></td>
              <td><input type="text" name="clinicAddress" value="<?= htmlspecialchars($row['clinicAddress']) ?>"></td>
              <td style="text-align:right;">
                <button type="button" class="btn btn-warning"
                  onclick="return openConfirm('Save changes to this pharmacy?', document.getElementById('form-<?= $id ?>'))">Save</button>
                <a href="database.php?view=pharmacys" class="btn btn-secondary">Cancel</a>
              </td>
            </tr>
          </form>
        <?php else: ?>
          <!-- Read-only pharmacy row -->
          <tr>
            <td><?= $id ?></td>
            <td><?= htmlspecialchars($row['name']) ?></td>
            <td><?= htmlspecialchars($row['address']) ?></td>
            <td><?= htmlspecialchars($row['contactNumber']) ?></td>
            <td><?= htmlspecialchars($row['email']) ?></td>
            <td><?= htmlspecialchars($row['clinicAddress']) ?></td>
            <td style="text-align:right;">
              <a href="database.php?view=pharmacys&edit_id=<?= $id ?>" class="btn btn-warning">Edit</a>
              <a href="database.php?view=pharmacys&delete_id=<?= $id ?>" class="btn btn-danger"
                 onclick="return openConfirm('Delete this pharmacy?', this.href)">Delete</a>
            </td>
          </tr>
        <?php endif; ?>
      <?php endwhile; ?>
    <?php endif; ?>
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