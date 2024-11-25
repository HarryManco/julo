<?php
session_start();

if (isset($_POST['menu_item_id'])) {
    $menu_item_id = (int)$_POST['menu_item_id'];

    // Check if item exists in the cart and remove it
    if (isset($_SESSION['cart'][$menu_item_id])) {
        unset($_SESSION['cart'][$menu_item_id]);
        $_SESSION['success_message'] = "Item removed from cart.";
    } else {
        $_SESSION['error_message'] = "Item not found in cart.";
    }
} else {
    $_SESSION['error_message'] = "Invalid item ID.";
}

header('Location: cart1.php');
exit();
?>
