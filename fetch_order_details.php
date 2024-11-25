<?php
session_start();
require 'db_connect.php';

// Validate the order ID
if (!isset($_GET['order_id']) || !is_numeric($_GET['order_id'])) {
    echo "<p>Error: Invalid order ID.</p>";
    exit();
}

$order_id = intval($_GET['order_id']);

// Fetch order items from the database
$stmt = $conn->prepare("
    SELECT oi.quantity, mi.name, mi.price 
    FROM order_items oi 
    JOIN menu_items mi ON oi.menu_item_id = mi.id 
    WHERE oi.order_id = ?
");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();

// Check if any items exist
if ($result->num_rows > 0): ?>
    <h3>Items:</h3>
    <ul>
        <?php while ($item = $result->fetch_assoc()): ?>
            <li>
                <?= htmlspecialchars($item['name']) ?> - Quantity: <?= htmlspecialchars($item['quantity']) ?> - Price: P<?= number_format($item['price'], 2) ?>
            </li>
        <?php endwhile; ?>
    </ul>
<?php else: ?>
    <p>No items found for this order.</p>
<?php endif; ?>
