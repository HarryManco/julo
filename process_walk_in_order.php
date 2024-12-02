<?php
require 'db_connect.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_name = $_POST['customer_name'] ?? 'Walk-In Customer'; // Default if no name is provided
    $orderItems = $_POST['order_items'] ?? [];

    if (empty($orderItems)) {
        $_SESSION['error_message'] = "No items selected.";
        header("Location: walk_in_order.php");
        exit();
    }

    $conn->begin_transaction();

    try {
        // Insert order into the `orders` table
        $stmt = $conn->prepare("INSERT INTO orders (user_id, total, status, created_at, updated_at) VALUES (NULL, 0, 'Preparing', NOW(), NULL)");
        if (!$stmt->execute()) {
            throw new Exception("Failed to insert order: " . $stmt->error);
        }
        $orderId = $conn->insert_id;

        $total = 0;

        // Insert order items and update stock
        $stmt = $conn->prepare("INSERT INTO order_items (order_id, menu_item_id, quantity) VALUES (?, ?, ?)");
        foreach ($orderItems as $menuItemId => $quantity) {
            // Fetch item details
            $itemStmt = $conn->prepare("SELECT price, quantity FROM menu_items WHERE id = ?");
            $itemStmt->bind_param("i", $menuItemId);
            $itemStmt->execute();
            $item = $itemStmt->get_result()->fetch_assoc();
            $itemStmt->close();

            if (!$item || $quantity > $item['quantity']) {
                throw new Exception("Insufficient stock for item ID: $menuItemId");
            }

            $subtotal = $item['price'] * $quantity;
            $total += $subtotal;

            // Insert into order_items
            $stmt->bind_param("iii", $orderId, $menuItemId, $quantity);
            if (!$stmt->execute()) {
                throw new Exception("Failed to insert order item: " . $stmt->error);
            }

            // Update stock
            $stockStmt = $conn->prepare("UPDATE menu_items SET quantity = quantity - ? WHERE id = ?");
            $stockStmt->bind_param("ii", $quantity, $menuItemId);
            if (!$stockStmt->execute()) {
                throw new Exception("Failed to update stock: " . $stockStmt->error);
            }
            $stockStmt->close();
        }
        $stmt->close();

        // Update the total in the `orders` table
        $totalStmt = $conn->prepare("UPDATE orders SET total = ? WHERE id = ?");
        $totalStmt->bind_param("di", $total, $orderId);
        if (!$totalStmt->execute()) {
            throw new Exception("Failed to update order total: " . $totalStmt->error);
        }
        $totalStmt->close();

        // Store the customer name in the session
        $_SESSION['walk_in_customers'][$orderId] = $customer_name;

        $conn->commit();
        $_SESSION['success_message'] = "Order placed successfully!";
        header("Location: walk_in_order.php");
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Transaction failed: " . $e->getMessage());
        $_SESSION['error_message'] = "Error: " . $e->getMessage();
        header("Location: walk_in_order.php");
    }
}
