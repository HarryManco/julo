<?php
session_start();
require 'db_connect.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['orderDetails']) || !isset($data['paymentId'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid data received.']);
    exit();
}

$orderDetails = $data['orderDetails'];
$paymentId = $data['paymentId'];

$conn->begin_transaction();

try {
    // Insert order into `orders` table
    $stmt = $conn->prepare("INSERT INTO orders (payment_id, total, status, created_at) VALUES (?, ?, ?, NOW())");
    $status = 'Paid';
    $stmt->bind_param("sds", $paymentId, $orderDetails['total'], $status);
    $stmt->execute();
    $orderId = $conn->insert_id; // Get the last inserted order ID
    $stmt->close();

    // Insert order items into `order_items` table
    $stmt = $conn->prepare("INSERT INTO order_items (order_id, menu_item_id, quantity, price) VALUES (?, ?, ?, ?)");
    foreach ($orderDetails['cart'] as $menuItemId => $quantity) {
        $stmt->bind_param("iiid", $orderId, $menuItemId, $quantity, $price);

        // Get price of the menu item
        $priceQuery = $conn->prepare("SELECT price FROM menu_items WHERE id = ?");
        $priceQuery->bind_param("i", $menuItemId);
        $priceQuery->execute();
        $priceResult = $priceQuery->get_result()->fetch_assoc();
        $price = $priceResult['price'];
        $priceQuery->close();

        $stmt->execute();
    }
    $stmt->close();

    $conn->commit();
    echo json_encode(['success' => true, 'order_id' => $orderId]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    exit();
}
?>
