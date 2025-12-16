<?php
function renderPrescriptionTable($result, $edit_id = null) {
    ?>
    <table>
      <tr>
        <th>ID</th>
        <th>Medication</th>
        <th>Patient</th>
        <th>Issue Date</th>
        <th>Expiration Date</th>
        <th>Status</th>
        <th></th>
      </tr>

      <?php while ($row = $result->fetch_assoc()): ?>
        <?php $id = (int)$row['prescriptionID']; ?>
        <?php if ($edit_id === $id): ?>
          <form method="POST" action="prescriptions.php" id="form-<?= $id ?>">
            <input type="hidden" name="update_record" value="1">
            <input type="hidden" name="id" value="<?= $id ?>">
            <tr class="highlight">
              <td><?= $id ?></td>
              <td><input type="number" name="medicationID" value="<?= htmlspecialchars($row['medicationID']) ?>" min="1" required></td>
              <td><input type="number" name="patientID" value="<?= htmlspecialchars($row['patientID']) ?>" min="1" required></td>
              <td><input type="date" name="issueDate" value="<?= htmlspecialchars($row['issueDate']) ?>" required></td>
              <td><input type="date" name="expirationDate" value="<?= htmlspecialchars($row['expirationDate']) ?>" required></td>
              <td><input type="text" name="status" value="<?= htmlspecialchars($row['status']) ?>" placeholder="Status"></td>
              <td style="text-align:right;">
                <button type="submit" class="btn btn-warning">Save</button>
                <a href="prescriptions.php" class="btn btn-secondary">Cancel</a>
              </td>
            </tr>
          </form>
        <?php else: ?>
          <tr>
            <td><?= $id ?></td>
            <td><?= htmlspecialchars($row['medicationID']) ?></td>
            <td><?= htmlspecialchars($row['patientID']) ?></td>
            <td><?= htmlspecialchars($row['issueDate']) ?></td>
            <td><?= htmlspecialchars($row['expirationDate']) ?></td>
            <td><?= htmlspecialchars($row['status']) ?></td>
            <td style="text-align:right;">
              <a href="prescriptions.php?edit_id=<?= $id ?>" class="btn btn-warning">Edit</a>
              <a href="prescriptions.php?delete_id=<?= $id ?>" class="btn btn-danger">Delete</a>
            </td>
          </tr>
        <?php endif; ?>
      <?php endwhile; ?>
    </table>
    <?php
}
