<?php
// Start the session
session_start();

// Check if the user is logged in as admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Set the timezone to Asia/Manila
date_default_timezone_set('Asia/Manila');

// Include database connection
include 'db_connect.php';

// Handle form submission to update order status
$success = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['order_id'], $_POST['status'])) {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['status'];

    // Update order status in the database
    $update_query = "UPDATE orders SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("si", $new_status, $order_id);

    if ($stmt->execute()) {
        $success = "Order status updated successfully!";
    } else {
        $error = "Error updating order: " . $conn->error;
    }

    $stmt->close();
}

// Fetch today's orders
$current_date = date('Y-m-d');
$orders_today_query = "
    SELECT 
        orders.id, 
        orders.total, 
        orders.status, 
        orders.created_at, 
        users.username AS username
    FROM orders
    LEFT JOIN users ON orders.user_id = users.id
    WHERE DATE(orders.created_at) = ?
";

$stmt = $conn->prepare($orders_today_query);
$stmt->bind_param("s", $current_date);
$stmt->execute();
$orders_today = $stmt->get_result();
$stmt->close();
?>
<?php include 'sidebar.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders</title>
    <link rel="stylesheet" href="css/manage_orders.css">
</head>
<body>
<div class="container">
    <h2>Manage Orders (Daily Orders)</h2>

    <!-- Success or Error Messages -->
    <?php if (!empty($success)): ?>
        <p class="success"><?= htmlspecialchars($success) ?></p>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <!-- Orders Table -->
    <table>
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Customer</th>
                <th>Total</th>
                <th>Date</th>
                <th>Status</th>
                <th>Update Status</th>
                <th>Details</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($orders_today && mysqli_num_rows($orders_today) > 0): ?>
            <?php while ($order = mysqli_fetch_assoc($orders_today)): ?>
                <?php
                // Determine customer name
                $customer_name = $order['username'] ?: ($_SESSION['walk_in_customers'][$order['id']] ?? 'Walk-In Customer');
                ?>
                <tr>
                    <td><?= $order['id'] ?></td>
                    <td><?= htmlspecialchars($customer_name) ?></td>
                    <td>P<?= number_format($order['total'], 2) ?></td>
                    <td><?= htmlspecialchars($order['created_at']) ?></td>
                    <td><?= ucfirst(htmlspecialchars($order['status'])) ?></td>
                    <td>
                        <form method="POST" action="">
                            <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                            <select name="status" required>
                                <option value="preparing" <?= $order['status'] == 'preparing' ? 'selected' : '' ?>>Preparing</option>
                                <option value="ready to pick-up" <?= $order['status'] == 'ready to pick-up' ? 'selected' : '' ?>>Ready to Pick-up</option>
                                <option value="completed" <?= $order['status'] == 'completed' ? 'selected' : '' ?>>Completed</option>
                                <option value="rejected" <?= $order['status'] == 'rejected' ? 'selected' : '' ?>>Rejected</option>
                            </select>
                            <button type="submit">Update</button>
                        </form>
                    </td>
                    <td>
                        <button class="view-details-btn" data-order-id="<?= $order['id'] ?>">View</button>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="7">No orders found for today.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Modal for order details -->
<div id="order-modal" class="modal">
    <div class="modal-content">
        <span class="close-btn">&times;</span>
        <h2>Order Details</h2>
        <div id="order-details">
            <!-- Details will be loaded dynamically via JavaScript -->
        </div>
    </div>
</div>

<script src="js/manage_orders.js"></script>
</body>
</html>
