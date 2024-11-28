<?php
require 'db_connect.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_name = $_POST['customer_name'] ?? 'Walk-In Customer';
    $menu_item_ids = $_POST['menu_item_ids'] ?? [];
    $quantities = $_POST['quantities'] ?? [];

    if (empty($menu_item_ids)) {
        $_SESSION['error_message'] = "No items selected for the order.";
        header("Location: walk_in_order.php");
        exit();
    }

    $conn->begin_transaction();

    try {
        // Insert order
        $stmt = $conn->prepare("INSERT INTO orders (user_id, total, status) VALUES (NULL, 0, 'Preparing')");
        if (!$stmt->execute()) {
            throw new Exception("Failed to insert order: " . $stmt->error);
        }
        $order_id = $conn->insert_id;
        $stmt->close();

        $total = 0;

        // Insert each order item
        $stmt = $conn->prepare("INSERT INTO order_items (order_id, menu_item_id, quantity) VALUES (?, ?, ?)");
        foreach ($menu_item_ids as $menu_item_id) {
            $quantity = $quantities[$menu_item_id] ?? 0;

            // Fetch item details
            $item_stmt = $conn->prepare("SELECT price, quantity FROM menu_items WHERE id = ?");
            $item_stmt->bind_param("i", $menu_item_id);
            $item_stmt->execute();
            $item = $item_stmt->get_result()->fetch_assoc();
            $item_stmt->close();

            if (!$item || $quantity > $item['quantity']) {
                throw new Exception("Invalid item or insufficient stock for item ID: $menu_item_id");
            }

            $subtotal = $item['price'] * $quantity;
            $total += $subtotal;

            $stmt->bind_param("iii", $order_id, $menu_item_id, $quantity);
            if (!$stmt->execute()) {
                throw new Exception("Failed to insert order item: " . $stmt->error);
            }

            // Update menu stock
            $update_stock_stmt = $conn->prepare("UPDATE menu_items SET quantity = quantity - ? WHERE id = ?");
            $update_stock_stmt->bind_param("ii", $quantity, $menu_item_id);
            if (!$update_stock_stmt->execute()) {
                throw new Exception("Failed to update stock: " . $update_stock_stmt->error);
            }
            $update_stock_stmt->close();
        }
        $stmt->close();

        // Update total in orders
        $update_total_stmt = $conn->prepare("UPDATE orders SET total = ? WHERE id = ?");
        $update_total_stmt->bind_param("di", $total, $order_id);
        if (!$update_total_stmt->execute()) {
            throw new Exception("Failed to update order total: " . $update_total_stmt->error);
        }
        $update_total_stmt->close();

        $conn->commit();
        $_SESSION['success_message'] = "Order placed successfully!";
        header("Location: walk_in_order.php");
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Transaction failed: " . $e->getMessage());
        $_SESSION['error_message'] = "Error processing order: " . $e->getMessage();
        header("Location: walk_in_order.php");
        exit();
    }
}
