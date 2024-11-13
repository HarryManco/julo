<?php
session_start();
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $item_id = $_POST['remove_id'];

    $query = "DELETE FROM cart_orders WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $item_id);
    $stmt->execute();
    $stmt->close();

    $_SESSION['notification'] = 'Item removed from cart successfully!';
    $_SESSION['notification_class'] = 'error';

    header("Location: cart.php");
    exit();
}
