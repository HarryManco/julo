<?php
session_start();
require 'db_connect.php';

// Validate and sanitize inputs
$menu_item_id = isset($_POST['menu_item_id']) ? (int)$_POST['menu_item_id'] : 0;
$quantity = 1; // Default quantity to 1

if ($menu_item_id <= 0) {
    $_SESSION['error_message'] = "Invalid input. Menu item ID must be a positive integer.";
    header('Location: cart1.php');
    exit();
}

// Check if the menu item exists
$stmt = $conn->prepare("SELECT id FROM menu_items WHERE id = ?");
$stmt->bind_param("i", $menu_item_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error_message'] = "Menu item does not exist.";
    $stmt->close();
    header('Location: cart1.php');
    exit();
}

$stmt->close();

// Initialize the cart session if it does not exist
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Add or update the item quantity in the cart
if (isset($_SESSION['cart'][$menu_item_id])) {
    $_SESSION['cart'][$menu_item_id] += $quantity;
} else {
    $_SESSION['cart'][$menu_item_id] = $quantity;
}

$_SESSION['success_message'] = "Item added to cart successfully.";
header('Location: cart1.php');
exit();
?>
