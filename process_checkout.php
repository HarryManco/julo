<?php
session_start();
require_once 'db_connect.php';

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['orderId']) && isset($data['amount'])) {
    $orderId = $data['orderId'];
    $amount = $data['amount'];
    $sessionId = session_id();

    // Save transaction to the database
    $query = "INSERT INTO transactions (order_id, session_id, amount, payment_method) VALUES (?, ?, ?, 'PayPal')";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssd", $orderId, $sessionId, $amount);
    $success = $stmt->execute();
    $stmt->close();

    if ($success) {
        // Clear the cart
        $deleteQuery = "DELETE FROM cart_orders WHERE order_id = ?";
        $stmt = $conn->prepare($deleteQuery);
        $stmt->bind_param("s", $sessionId);
        $stmt->execute();
        $stmt->close();

        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to save transaction.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request data.']);
}
?>
