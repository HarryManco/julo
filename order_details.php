<?php
session_start();
require 'db_connect.php'; // Ensure this file contains your DB connection settings

// Check if order_id is provided
if (!isset($_GET['order_id']) || !is_numeric($_GET['order_id'])) {
    echo "Invalid order ID.";
    exit();
}

$order_id = $_GET['order_id'];

// Fetch order details
$stmt = $conn->prepare("SELECT o.id, o.total, o.created_at, o.status, u.username 
                        FROM orders o 
                        JOIN users u ON o.user_id = u.id 
                        WHERE o.id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order_result = $stmt->get_result();
$order = $order_result->fetch_assoc();

if (!$order) {
    echo "Order not found.";
    exit();
}

$stmt->close();

// Fetch order items
$stmt = $conn->prepare("SELECT oi.quantity, mi.name, mi.price 
                        FROM order_items oi 
                        JOIN menu_items mi ON oi.menu_item_id = mi.id 
                        WHERE oi.order_id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$items_result = $stmt->get_result();

$items = [];
while ($item = $items_result->fetch_assoc()) {
    $items[] = $item;
}

$stmt->close();
?>

<div>
    <p><strong>Order ID:</strong> <?= htmlspecialchars($order['id']) ?></p>
    <p><strong>User:</strong> <?= htmlspecialchars($order['username']) ?></p>
    <p><strong>Total:</strong> P<?= number_format($order['total'], 2) ?></p>
    <p><strong>Date:</strong> <?= htmlspecialchars($order['created_at']) ?></p>
    <p><strong>Status:</strong> <?= htmlspecialchars($order['status']) ?></p>

    <h3>Items:</h3>
    <ul>
        <?php foreach ($items as $item): ?>
            <li><?= htmlspecialchars($item['name']) ?> - Quantity: <?= htmlspecialchars($item['quantity']) ?> - Price: P<?= number_format($item['price'], 2) ?></li>
        <?php endforeach; ?>
    </ul>
</div>
