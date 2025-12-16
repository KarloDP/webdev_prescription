<?php
function renderMedicineTable($result, $add_mode, $edit_id = null) {
    ?>
    <table>
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

      <?php if ($add_mode): ?>
        <form method="POST" action="medicines.php" id="form-add-medicine">
          <input type="hidden" name="update_record" value="1">
          <tr class="highlight">
            <td>New</td>
            <td><input type="text" name="genericName" placeholder="Generic Name" required></td>
            <td><input type="text" name="brandName" placeholder="Brand Name"></td>
            <td><input type="text" name="form" placeholder="Form"></td>
            <td><input type="text" name="strength" placeholder="Strength"></td>
            <td><input type="text" name="manufacturer" placeholder="Manufacturer"></td>
            <td><input type="number" name="stock" placeholder="Stock" min="0"></td>
            <td style="text-align:right;">
              <button type="submit" class="btn btn-success">Save</button>
              <a href="medicines.php" class="btn btn-secondary">Cancel</a>
            </td>
          </tr>
        </form>
      <?php endif; ?>

      <?php while ($row = $result->fetch_assoc()): ?>
        <?php $id = (int)$row['medicationID']; ?>
        <tr>
          <td><?= $id ?></td>
          <td><?= htmlspecialchars($row['genericName']) ?></td>
          <td><?= htmlspecialchars($row['brandName']) ?></td>
          <td><?= htmlspecialchars($row['form']) ?></td>
          <td><?= htmlspecialchars($row['strength']) ?></td>
          <td><?= htmlspecialchars($row['manufacturer']) ?></td>
          <td><?= htmlspecialchars($row['stock']) ?></td>
          <td style="text-align:right;">
            <a href="medicines.php?delete_id=<?= $id ?>" class="btn btn-danger">Delete</a>
          </td>
        </tr>
      <?php endwhile; ?>
    </table>
    <?php
}
