<?php
require_once "../includes/db_connect.php";

$filter = $_GET['filter'] ?? 'all';

if ($filter === 'active') {
    $query = "SELECT * FROM doctor WHERE status='active'";
    $title = "Active Doctors";
} elseif ($filter === 'pending') {
    $query = "SELECT * FROM doctor WHERE status='pending'";
    $title = "Pending Doctor Requests";
} else {
    $query = "SELECT * FROM doctor";
    $title = "All Doctors";
}

$result = $conn->query($query);
?>
<!DOCTYPE html>
<html>
<head><title><?= $title ?></title></head>
<body>
  <h2><?= $title ?></h2>
  <table border="1" cellpadding="8">
    <tr>
      <th>ID</th><th>Name</th><th>Specialization</th><th>Email</th><th>Status</th>
    </tr>
    <?php while($row = $result->fetch_assoc()): ?>
      <tr>
        <td>
          <button id="arrow-<?= $row['doctorID'] ?>" onclick="toggleActions('<?= $row['doctorID'] ?>')">▶</button>
          <?= $row['doctorID'] ?>
        </td>
        <td><?= $row['firstName'] . " " . $row['lastName'] ?></td>
        <td><?= $row['specialization'] ?></td>
        <td><?= $row['email'] ?></td>
        <td><?= $row['status'] ?></td>
      </tr>
      <tr id="actions-<?= $row['doctorID'] ?>" style="display:none;">
        <td colspan="5">
          <a href="edit_doctor.php?id=<?= $row['doctorID'] ?>" class="btn btn-warning">Edit</a>
          <a href="delete_doctor.php?id=<?= $row['doctorID'] ?>" onclick="return confirm('Delete this doctor?');" class="btn btn-danger">Delete</a>
          <?php if ($row['status'] === 'pending'): ?>
            <a href="approve_doctor.php?id=<?= $row['doctorID'] ?>" class="btn btn-success">Accept</a>
            <a href="reject_doctor.php?id=<?= $row['doctorID'] ?>" class="btn btn-secondary">Reject</a>
          <?php endif; ?>
        </td>
      </tr>
    <?php endwhile; ?>
  </table>

  <script>
  function toggleActions(id) {
    const row = document.getElementById('actions-' + id);
    const arrow = document.getElementById('arrow-' + id);
    if (row.style.display === 'none') {
      row.style.display = '';
      arrow.textContent = '▼';
    } else {
      row.style.display = 'none';
      arrow.textContent = '▶';
    }
  }
  </script>
</body>
</html>
