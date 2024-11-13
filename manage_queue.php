<?php
include 'db_connect.php';

// Get the current date
$current_date = date('Y-m-d');

// Modify the query to display only today's queue
$query = "SELECT * FROM queue WHERE queue_date = '$current_date'";
$result = mysqli_query($conn, $query);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['update_queue'])) {
        $queue_id = $_POST['queue_id'];
        $status = $_POST['queue_status'];

        // Update the queue status
        $update_query = "UPDATE queue SET queue_status = '$status' WHERE queue_id = $queue_id";
        mysqli_query($conn, $update_query);

        // Fetch customer_type and customer_id to determine which table to update
        $queue_result = mysqli_query($conn, "SELECT customer_id, customer_type FROM queue WHERE queue_id = $queue_id");
        $queue_data = mysqli_fetch_assoc($queue_result);

        if ($queue_data) {
            $customer_id = $queue_data['customer_id'];
            $customer_type = $queue_data['customer_type'];

            // Update the status in the corresponding table based on customer_type
            if ($customer_type === 'Walk-in') {
                $update_walkin_query = "UPDATE walk_in SET walk_in_status = '$status' WHERE walk_in_id = $customer_id";
                mysqli_query($conn, $update_walkin_query);
            } elseif ($customer_type === 'Reservation') {
                $update_reservation_query = "UPDATE reservations SET reservation_status = '$status' WHERE reservation_id = $customer_id";
                mysqli_query($conn, $update_reservation_query);
            }
        }

        header("Location: manage_queue.php");
        exit();
    }
}
?>
<?php include 'sidebar.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Queue</title>
    <link rel="stylesheet" href="css/queue_manage.css">
</head>
<body>
<div class="container">
    <h2>Manage Queue - <?php echo date('F j, Y'); ?></h2>
    <table>
        <thead>
        <tr>
            <th>Queue ID</th>
            <th>Customer Type</th>
            <th>Status</th>
            <th>Slot</th>
            <th>Action</th>
        </tr>
        </thead>
        <tbody>
        <?php if (mysqli_num_rows($result) > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?= $row['queue_id'] ?></td>
                    <td><?= $row['customer_type'] ?></td>
                    <td><?= $row['queue_status'] ?></td>
                    <td><?= $row['assigned_slot'] ?></td>
                    <td>
                        <form method="POST">
                            <input type="hidden" name="queue_id" value="<?= $row['queue_id'] ?>">
                            <select name="queue_status" required>
                                <option value="Waiting" <?= $row['queue_status'] === 'Waiting' ? 'selected' : '' ?>>Waiting</option>
                                <option value="Serving" <?= $row['queue_status'] === 'Serving' ? 'selected' : '' ?>>Serving</option>
                                <option value="Finished" <?= $row['queue_status'] === 'Finished' ? 'selected' : '' ?>>Finished</option>
                            </select>
                            <button type="submit" name="update_queue">Update</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="5">No queues for today.</td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
<script src="js/queue_manage.js"></script>
</body>
</html>
