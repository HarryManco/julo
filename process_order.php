<?php
session_start();
require 'db_connect.php';

// Get JSON input
$data = json_decode(file_get_contents('php://input'), true);

// Log received data for debugging purposes
error_log("Received Data: " . print_r($data, true)); // Log to error log

// Validate the incoming data
if (!isset($data['cart']) || !isset($data['total'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid data received.']);
    exit();
}

$orderDetails = $data;

// Default order status
$status = 'Approved'; // Default status for new orders

// Begin a database transaction to ensure data consistency
$conn->begin_transaction();

try {
    // Insert into `orders` table
    $stmt = $conn->prepare("INSERT INTO orders (user_id, total, status, created_at) VALUES (?, ?, ?, NOW())");
    $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null; // Use session user_id if available
    $stmt->bind_param("ids", $userId, $orderDetails['total'], $status);
    $stmt->execute();
    $orderId = $stmt->insert_id; // Get the last inserted order ID
    $stmt->close();

    // Insert into `order_items` table and deduct quantity from `menu_items`
    $stmt = $conn->prepare("INSERT INTO order_items (order_id, menu_item_id, quantity) VALUES (?, ?, ?)");
    
    foreach ($orderDetails['cart'] as $menuItemId => $quantity) {
        // Insert order items into order_items table
        $stmt->bind_param("iii", $orderId, $menuItemId, $quantity);
        $stmt->execute();

        // Deduct the quantity from `menu_items` table
        $updateStmt = $conn->prepare("UPDATE menu_items SET quantity = quantity - ? WHERE id = ?");
        $updateStmt->bind_param("ii", $quantity, $menuItemId);
        $updateStmt->execute();
        $updateStmt->close();
    }
    $stmt->close();

    // Insert into `cafe_transactions` table
    $stmt = $conn->prepare("INSERT INTO cafe_transactions (user_id, order_id, transaction_type, amount, payment_method, created_at) 
                            VALUES (?, ?, 'Online Order', ?, 'PayPal', NOW())");
    $stmt->bind_param("iid", $userId, $orderId, $orderDetails['total']);
    $stmt->execute();
    $stmt->close();

    // Commit the transaction
    $conn->commit();

    // Clear the cart after successful payment
    unset($_SESSION['cart']);

    // Set success message in the session for display on cart1.php
    $_SESSION['success_message'] = 'Order successfully placed! Please check your order history.';

    // Return success response with notification message
    echo json_encode(['success' => true, 'message' => 'Order successfully placed! Please check your order history.', 'order_id' => $orderId]);

} catch (Exception $e) {
    // Rollback the transaction in case of error
    $conn->rollback();
    error_log("Order Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred. Please try again.']);
    exit();
}
?>
