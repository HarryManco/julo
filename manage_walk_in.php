<?php
include 'db_connect.php';

$query = "
SELECT w.walk_in_id, w.customer_name, w.vehicle_type, 
       COALESCE(cs.car_model, 'Unknown Vehicle') AS car_model, 
       w.walk_in_status, w.payment_status
FROM walk_in w 
LEFT JOIN car_sizes cs ON w.vehicle_type = cs.id";

$result = mysqli_query($conn, $query);

if (!$result) {
    die("Query Failed: " . mysqli_error($conn));
}

$rows = [];
while ($row = mysqli_fetch_assoc($result)) {
    $rows[] = $row;
}

?>
<?php include 'sidebar.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Walk-Ins</title>
    <link rel="stylesheet" href="css/manage_walk_in.css">
</head>
<body>
<div class="container">
    <h2>Manage Walk-Ins</h2>

    <!-- Notification message area -->
    <p id="notification" class="notification" style="display: none;"></p>

    <table>
        <thead>
        <tr>
            <th>Walk-In ID</th>
            <th>Customer Name</th>
            <th>Vehicle Type</th>
            <th>Status</th>
            <th>Payment Status</th>
            <th>Action</th>
        </tr>
        </thead>
        <tbody>
        <?php if (!empty($rows)): ?>
            <?php foreach ($rows as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['walk_in_id']) ?></td>
                    <td><?= htmlspecialchars($row['customer_name']) ?></td>
                    <td><?= htmlspecialchars($row['car_model']) ?></td>
                    <td><?= htmlspecialchars($row['walk_in_status']) ?></td>
                    <td><?= htmlspecialchars($row['payment_status']) ?></td>
                    <td>
                        <form class="update-form">
                            <input type="hidden" name="walk_in_id" value="<?= htmlspecialchars($row['walk_in_id']) ?>">
                            <div class="action-container">
                                <select name="walk_in_status" required>
                                    <option value="Waiting" <?= $row['walk_in_status'] == 'Waiting' ? 'selected' : '' ?>>Waiting</option>
                                    <option value="Serving" <?= $row['walk_in_status'] == 'Serving' ? 'selected' : '' ?>>Serving</option>
                                    <option value="Finished" <?= $row['walk_in_status'] == 'Finished' ? 'selected' : '' ?>>Finished</option>
                                    <option value="Completed" <?= $row['walk_in_status'] == 'Completed' ? 'selected' : '' ?>>Completed</option>
                                </select>
                                <select name="payment_status" required>
                                    <option value="Unpaid" <?= $row['payment_status'] == 'Unpaid' ? 'selected' : '' ?>>Unpaid</option>
                                    <option value="Fully Paid" <?= $row['payment_status'] == 'Fully Paid' ? 'selected' : '' ?>>Fully Paid</option>
                                </select>
                                <button type="button" class="update-button">Update</button>
                            </div>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="6">No walk-ins found.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
<script src="js/walk_in_manage.js"></script>
</body>
</html>
