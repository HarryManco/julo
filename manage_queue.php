<?php
include 'db_connect.php';

// Initialize notification message
$notification = "";

// Set the default timezone
date_default_timezone_set('Asia/Manila');
$current_date = date('Y-m-d');

// Log script initialization
error_log("manage_queue.php initialized. Current date: $current_date");

// Query to display today's queue
$query = "SELECT * FROM queue WHERE queue_date = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $current_date);
$stmt->execute();
$result = $stmt->get_result();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['update_queue'])) {
        $queue_id = $_POST['queue_id'];
        $status = $_POST['queue_status'];

        // Prevent duplicate form submissions
        session_start();
        if (isset($_SESSION['last_queue_update']) && $_SESSION['last_queue_update'] == "$queue_id-$status") {
            error_log("Duplicate form submission prevented for queue ID: $queue_id");
            exit();
        }
        $_SESSION['last_queue_update'] = "$queue_id-$status";

        // Update queue status
        $update_query = "UPDATE queue SET queue_status = ? WHERE queue_id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("si", $status, $queue_id);

        if ($stmt->execute()) {
            error_log("Queue status updated: queue_id=$queue_id, status=$status");

            // Fetch customer_id and customer_type
            $queue_result = $conn->query("SELECT customer_id, customer_type FROM queue WHERE queue_id = $queue_id");
            $queue_data = $queue_result->fetch_assoc();

            if ($queue_data) {
                $customer_id = $queue_data['customer_id'];
                $customer_type = $queue_data['customer_type'];

                error_log("Queue data fetched: Customer ID=$customer_id, Customer Type=$customer_type");

                // Update the corresponding status in the respective table
                if ($customer_type === 'Walk-in') {
                    $update_walkin_query = "UPDATE walk_in SET walk_in_status = ? WHERE walk_in_id = ?";
                    $stmt = $conn->prepare($update_walkin_query);
                    $stmt->bind_param("si", $status, $customer_id);
                    $stmt->execute();
                } elseif ($customer_type === 'Reservation') {
                    $update_reservation_query = "UPDATE reservations SET reservation_status = ? WHERE reservation_id = ?";
                    $stmt = $conn->prepare($update_reservation_query);
                    $stmt->bind_param("si", $status, $customer_id);
                    $stmt->execute();
                }

                // Add notification for the customer
                $notification_message = "";
                if ($status === 'Serving') {
                    $notification_message = "Your vehicle is now being served.";
                } elseif ($status === 'Finished') {
                    $notification_message = "Your vehicle service has been completed.";
                }

                if (!empty($notification_message)) {
                    $notification_sql = "INSERT INTO notifications (user_id, message, type) VALUES (?, ?, 'queue_update')";
                    $stmt = $conn->prepare($notification_sql);
                    $stmt->bind_param("is", $customer_id, $notification_message);
                    $stmt->execute();
                }

                // Notify the admin if the status is "Finished"
                if ($status === 'Finished') {
                    $admin_message = "Service for queue ID $queue_id has been marked as Finished. Please process remaining fees.";
                    $admin_sql = "INSERT INTO notifications (admin_id, message, type) VALUES (1, ?, 'queue_update')";
                    $stmt = $conn->prepare($admin_sql);
                    $stmt->bind_param("s", $admin_message);
                    $stmt->execute();
                }
            }
        }

        // Set the notification message for the staff
        $notification = "Queue status successfully updated!";
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

    <!-- Notification Message -->
    <?php if (!empty($notification)): ?>
        <div class="notification success">
            <?= htmlspecialchars($notification) ?>
        </div>
    <?php endif; ?>

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
        <tbody id="queueTable">
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
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
