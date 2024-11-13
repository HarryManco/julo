<?php
include 'db_connect.php';

$query = "SELECT * FROM walk_in";
$result = mysqli_query($conn, $query);

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
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <tr>
                <td><?= $row['walk_in_id'] ?></td>
                <td><?= $row['customer_name'] ?></td>
                <td><?= $row['vehicle_type'] ?></td>
                <td><?= $row['walk_in_status'] ?></td>
                <td><?= $row['payment_status'] ?></td>
                <td>
                    <form class="update-form">
                        <input type="hidden" name="walk_in_id" value="<?= $row['walk_in_id'] ?>">
                        <div class="action-container">
                            <select name="walk_in_status" required>
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
        <?php endwhile; ?>
        </tbody>
    </table>
</div>
<script src="js/walk_in_manage.js"></script>
</body>
</html>
