<?php
function renderPharmacyTable($result, $add_mode, $edit_id = null) {
    ?>
    <table>
      <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Address</th>
        <th>Contact Number</th>
        <th>Email</th>
        <th>Clinic Address</th>
        <th></th>
      </tr>

      <?php if ($add_mode): ?>
        <form method="POST" action="pharmacies.php" id="form-add-pharmacy">
          <input type="hidden" name="update_record" value="1">
          <tr class="highlight">
            <td>New</td>
            <td><input type="text" name="name" placeholder="Pharmacy Name" required></td>
            <td><input type="text" name="address" placeholder="Address"></td>
            <td><input type="text" name="contactNumber" placeholder="Contact Number"></td>
            <td><input type="email" name="email" placeholder="Email"></td>
            <td><input type="text" name="clinicAddress" placeholder="Clinic Address"></td>
            <td style="text-align:right;">
              <button type="submit" class="btn btn-success">Save</button>
              <a href="pharmacies.php" class="btn btn-secondary">Cancel</a>
            </td>
          </tr>
        </form>
      <?php endif; ?>

      <?php while ($row = $result->fetch_assoc()): ?>
        <?php $id = (int)$row['pharmacyID']; ?>
        <?php if ($edit_id === $id): ?>
          <form method="POST" action="pharmacies.php" id="form-<?= $id ?>">
            <input type="hidden" name="update_record" value="1">
            <input type="hidden" name="id" value="<?= $id ?>">
            <tr class="highlight">
              <td><?= $id ?></td>
              <td><input type="text" name="name" value="<?= htmlspecialchars($row['name']) ?>" required></td>
              <td><input type="text" name="address" value="<?= htmlspecialchars($row['address']) ?>"></td>
              <td><input type="text" name="contactNumber" value="<?= htmlspecialchars($row['contactNumber']) ?>"></td>
              <td><input type="email" name="email" value="<?= htmlspecialchars($row['email']) ?>"></td>
              <td><input type="text" name="clinicAddress" value="<?= htmlspecialchars($row['clinicAddress']) ?>"></td>
              <td style="text-align:right;">
                <button type="submit" class="btn btn-warning">Save</button>
                <a href="pharmacies.php" class="btn btn-secondary">Cancel</a>
              </td>
            </tr>
          </form>
        <?php else: ?>
          <tr>
            <td><?= $id ?></td>
            <td><?= htmlspecialchars($row['name']) ?></td>
            <td><?= htmlspecialchars($row['address']) ?></td>
            <td><?= htmlspecialchars($row['contactNumber']) ?></td>
            <td><?= htmlspecialchars($row['email']) ?></td>
            <td><?= htmlspecialchars($row['clinicAddress']) ?></td>
            <td style="text-align:right;">
              <a href="pharmacies.php?edit_id=<?= $id ?>" class="btn btn-warning">Edit</a>
              <a href="pharmacies.php?delete_id=<?= $id ?>" class="btn btn-danger">Delete</a>
            </td>
          </tr>
        <?php endif; ?>
      <?php endwhile; ?>
    </table>
    <?php
}
