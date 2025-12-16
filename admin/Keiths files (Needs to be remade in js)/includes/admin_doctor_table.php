<?php
function renderDoctorTable($result, $filter, $edit_id) {
    ?>
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
                <button type="submit" name="update_doctor" class="btn btn-warning">Save</button>
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
                <a href="users.php?accept_id=<?= $id ?>&filter=pending" class="btn btn-success">Accept</a>
                <a href="users.php?delete_id=<?= $id ?>&filter=pending" class="btn btn-secondary">Reject</a>
              <?php else: ?>
                <a href="users.php?edit_id=<?= $id ?>&filter=<?= htmlspecialchars($filter) ?>" class="btn btn-warning">Edit</a>
                <a href="users.php?delete_id=<?= $id ?>&filter=active" class="btn btn-danger">Delete</a>
              <?php endif; ?>
            </td>
          </tr>
        <?php endif; ?>
      <?php endwhile; ?>
    </table>
    <?php
}
