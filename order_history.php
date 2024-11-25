<?php
session_start();
require 'db_connect.php'; // Ensure this file contains your DB connection settings

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); // Redirect to login if not logged in
    exit();
}

// Get user ID from session
$user_id = $_SESSION['user_id'];

// Fetch order history for the logged-in user
$stmt = $conn->prepare("SELECT o.id, o.total, o.created_at, o.status, GROUP_CONCAT(oi.menu_item_id) AS menu_item_ids, GROUP_CONCAT(oi.quantity) AS quantities 
                        FROM orders o 
                        JOIN order_items oi ON o.id = oi.order_id 
                        WHERE o.user_id = ? 
                        GROUP BY o.id 
                        ORDER BY o.created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$orders = [];
while ($row = $result->fetch_assoc()) {
    $orders[] = $row;
}

$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order History</title>
    <link rel="stylesheet" href="css/order.css">
</head>
<body>
<?php include 'header.php'; ?>  
<div class="container">
    <h2>Order History</h2>
    <?php if (!empty($orders)): ?>
        <table>
            <tr>
                <th>Order ID</th>
                <th>Total</th>
                <th>Date</th>
                <th>Status</th>
                <th>Details</th>
            </tr>
            <?php foreach ($orders as $order): ?>
                <tr>
                    <td><?= htmlspecialchars($order['id']) ?></td>
                    <td>P<?= number_format($order['total'], 2) ?></td>
                    <td><?= htmlspecialchars($order['created_at']) ?></td>
                    <td><?= htmlspecialchars($order['status']) ?></td>
                    <td>
                        <button onclick="showOrderDetails(<?= htmlspecialchars($order['id']) ?>)">View</button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p>You have no past orders.</p>
    <?php endif; ?>
</div>

<!-- Order Details Modal -->
<div id="orderDetailsModal" style="display: none;">
    <div class="modal-content">
        <span id="closeModal" onclick="closeModal()">&times;</span>
        <h2>Order Details</h2>
        <div id="orderDetailsContent"></div>
    </div>
</div>

<script>
function showOrderDetails(orderId) {
    fetch('order_details.php?order_id=' + orderId)
        .then(response => response.text())
        .then(data => {
            document.getElementById('orderDetailsContent').innerHTML = data;
            document.getElementById('orderDetailsModal').style.display = 'block';
        });
}

function closeModal() {
    document.getElementById('orderDetailsModal').style.display = 'none';
}
</script>
</body>
</html>
