<?php
session_start();

if (!isset($_SESSION['cart'])) {
    $_SESSION['error_message'] = "Your cart is empty.";
    header("Location: cart1.php");
    exit();
}

$menu_item_id = isset($_POST['menu_item_id']) ? (int)$_POST['menu_item_id'] : 0;
$action = isset($_POST['action']) ? $_POST['action'] : '';

if ($menu_item_id <= 0 || !in_array($action, ['increase', 'decrease'])) {
    $_SESSION['error_message'] = "Invalid request.";
    header("Location: cart1.php");
    exit();
}

if (!isset($_SESSION['cart'][$menu_item_id])) {
    $_SESSION['error_message'] = "Item not found in cart.";
    header("Location: cart1.php");
    exit();
}

// Update the quantity based on the action
if ($action === 'increase') {
    $_SESSION['cart'][$menu_item_id]++;
} elseif ($action === 'decrease') {
    if ($_SESSION['cart'][$menu_item_id] > 1) {
        $_SESSION['cart'][$menu_item_id]--;
    } else {
        unset($_SESSION['cart'][$menu_item_id]); // Remove item if quantity reaches 0
    }
}

$_SESSION['success_message'] = "Cart updated successfully.";
header("Location: cart1.php");
exit();
?>
