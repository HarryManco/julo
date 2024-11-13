<?php
session_start();
include 'db_connect.php';

// Fetch all queue items for admin management
$query = "SELECT * FROM daily_queue ORDER BY queue_date, slot_number, queue_number";
$result = $conn->query($query);

if (isset($_SESSION['success_message'])) {
    echo "<div class='success-message'>" . $_SESSION['success_message'] . "</div>";
    unset($_SESSION['success_message']);
}
if (isset($_SESSION['error_message'])) {
    echo "<div class='error-message'>" . $_SESSION['error_message'] . "</div>";
    unset($_SESSION['error_message']);
}
?>
<?php include 'sidebar.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Queue Management</title>
    <link rel="stylesheet" href="css/managequeue.css">
</head>
<body>
    <h2>Admin Queue Management</h2>
    <table>
        <tr>
            <th>Customer</th>
            <th>Car Model</th>
            <th>Service</th>
            <th>Slot</th>
            <th>Queue Date</th>
            <th>Status</th>
            <th>Paid Fee</th>
            <th>Remaining Fee</th>
            <th>Payment Status</th>
            <th>Actions</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()) { ?>
            <tr>
                <td><?= $row['customer_name'] ?></td>
                <td><?= $row['car_model'] ?></td>
                <td><?= $row['service_id'] ?></td>
                <td><?= $row['slot_number'] ?></td>
                <td><?= $row['queue_date'] ?></td>
                <td><?= $row['status'] ?></td>
                <td><?= number_format($row['paid_fee'], 2) ?></td>
                <td><?= number_format($row['remaining_fee'], 2) ?></td>
                <td><?= $row['payment_status'] ?></td>
                <td>
                    <!-- Status Form -->
                    <form method="POST" action="queue_admin.php" style="display:inline-block;">
                        <input type="hidden" name="queue_id" value="<?= $row['id'] ?>">
                        <label>Status:</label>
                        <select name="status">
                            <option value="Completed" 
                                <?= $row['status'] == 'Completed' ? 'selected' : '' ?>
                                <?= $row['payment_status'] == 'Unpaid' ? 'disabled' : '' ?>
                            >Completed</option>
                            <option value="Cancelled" <?= $row['status'] == 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                        </select>
                        <button type="submit">Update</button>
                    </form>

                    <!-- Payment Status Form -->
                    <form method="POST" action="queue_admin.php" style="display:inline-block;">
                        <input type="hidden" name="queue_id" value="<?= $row['id'] ?>">
                        <label>Payment Status:</label>
                        <select name="payment_status">
                            <option value="Unpaid" <?= $row['payment_status'] == 'Unpaid' ? 'selected' : '' ?>>Unpaid</option>
                            <option value="Fully Paid" <?= $row['payment_status'] == 'Fully Paid' ? 'selected' : '' ?>>Fully Paid</option>
                        </select>
                        <button type="submit">Update Payment</button>
                    </form>
                </td>
            </tr>
        <?php } ?>
    </table>
</body>
</html>
