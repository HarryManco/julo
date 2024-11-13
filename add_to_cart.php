<?php
session_start();
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $item_id = $_POST['item_id'];
    $item_name = $_POST['item_name'];
    $item_price = $_POST['item_price'];
    $item_quantity = $_POST['item_quantity'];
    $order_id = session_id(); // Use session ID as the order ID

    // Check if item already exists in cart_orders for this session
    $query = "SELECT * FROM cart_orders WHERE order_id = ? AND menu_item_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $order_id, $item_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Update quantity if item already exists
        $update_query = "UPDATE cart_orders SET quantity = quantity + ? WHERE order_id = ? AND menu_item_id = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("isi", $item_quantity, $order_id, $item_id);
        $update_stmt->execute();
        $update_stmt->close();
    } else {
        // Insert new item if it doesn't exist
        $insert_query = "INSERT INTO cart_orders (order_id, menu_item_id, quantity) VALUES (?, ?, ?)";
        $insert_stmt = $conn->prepare($insert_query);
        $insert_stmt->bind_param("sii", $order_id, $item_id, $item_quantity);
        $insert_stmt->execute();
        $insert_stmt->close();
    }

    $stmt->close();

    // Set success notification
    $_SESSION['notification'] = 'Item added to cart successfully!';
    $_SESSION['notification_class'] = 'success';

    header("Location: cart.php");
    exit();
}
