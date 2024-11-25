<?php
session_start();
require 'db_connect.php';

if (!isset($_GET['order_id'])) {
    die("Order ID not specified.");
}

$order_id = (int)$_GET['order_id'];
$user_id = $_SESSION['user_id'];

// Retrieve order details
$stmt = $conn->prepare("SELECT o.total, o.created_at, u.username, o.status FROM orders o JOIN users u ON o.user_id = u.id WHERE o.id = ? AND o.user_id = ?");
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();
$stmt->close();

if (!$order) {
    die("Order not found or you do not have permission to view this order.");
}

// Retrieve order items
$stmt = $conn->prepare("SELECT m.name, oi.quantity, m.price FROM order_items oi JOIN menu_items m ON oi.menu_item_id = m.id WHERE oi.order_id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$items = $stmt->get_result();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt</title>
    <link rel="stylesheet" href="cart.css">
</head>
<body>
<?php include 'header.php'; ?>
<div class="container">
    <h2>Receipt</h2>
    <p>Order ID: <?php echo htmlspecialchars($order_id); ?></p>
    <p>User: <?php echo htmlspecialchars($order['username']); ?></p>
    <p>Total: P<?php echo number_format($order['total'], 2); ?></p>
    <p>Date: <?php echo htmlspecialchars($order['created_at']); ?></p>
    <p>Status: <?php echo htmlspecialchars($order['status']); ?></p>
    
    <h3>Items:</h3>
    <ul>
        <?php while ($item = $items->fetch_assoc()): ?>
            <li><?php echo htmlspecialchars($item['name']); ?> - Quantity: <?php echo htmlspecialchars($item['quantity']); ?> - Price: P<?php echo number_format($item['price'], 2); ?></li>
        <?php endwhile; ?>
    </ul>
    
    <a href="order_history.php">View Order History</a>
</div>
</body>
</html>
