<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    die("You need to be logged in to checkout.");
}

if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    die("Your cart is empty.");
}

// Calculate total and check stock availability
$total = 0;
$errors = [];

foreach ($_SESSION['cart'] as $menu_item_id => $quantity) {
    // Fetch the price and stock from the database
    $stmt = $conn->prepare("SELECT price, quantity FROM menu_items WHERE id = ?");
    $stmt->bind_param("i", $menu_item_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $item = $result->fetch_assoc();
    $stmt->close();

    if ($item) {
        // Check if enough stock is available
        if ($item['quantity'] < $quantity) {
            $errors[] = "Not enough stock for item ID: $menu_item_id. Only {$item['quantity']} left.";
        } else {
            $total += $item['price'] * $quantity;
        }
    } else {
        $errors[] = "An item in your cart no longer exists.";
    }
}

// If there are errors, show them to the user and exit
if (!empty($errors)) {
    $_SESSION['error_message'] = implode('<br>', $errors);
    header('Location: cart.php');
    exit();
}

// Proceed if no errors
$conn->begin_transaction();

try {
    // Create the order
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("INSERT INTO orders (user_id, total) VALUES (?, ?)");
    $stmt->bind_param("id", $user_id, $total);
    $stmt->execute();
    $order_id = $stmt->insert_id;
    $stmt->close();

    // Add order items and update inventory
    foreach ($_SESSION['cart'] as $menu_item_id => $quantity) {
        // Add to order_items
        $stmt = $conn->prepare("INSERT INTO order_items (order_id, menu_item_id, quantity) VALUES (?, ?, ?)");
        $stmt->bind_param("iii", $order_id, $menu_item_id, $quantity);
        $stmt->execute();
        $stmt->close();

        // Update the stock in menu_items
        $stmt = $conn->prepare("UPDATE menu_items SET quantity = quantity - ? WHERE id = ?");
        $stmt->bind_param("ii", $quantity, $menu_item_id);
        $stmt->execute();
        $stmt->close();
    }

    // Commit the transaction
    $conn->commit();

    // Clear the cart
    unset($_SESSION['cart']);

    // Redirect to receipt page
    header("Location: receipt.php?order_id=$order_id");
    exit();
    
} catch (Exception $e) {
    // Rollback transaction if any errors occur
    $conn->rollback();
    die("An error occurred while processing your order. Please try again.");
}
?>
