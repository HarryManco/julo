<?php
session_start();
require_once 'db_connect.php';

$order_id = session_id();
$cart_items = [];

// Fetch items from cart_orders with menu item details
$query = "
    SELECT co.id, co.menu_item_id, co.quantity, mi.itemname, mi.price 
    FROM cart_orders co
    JOIN menuitems mi ON co.menu_item_id = mi.menuitemid
    WHERE co.order_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $order_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $cart_items[] = $row;
}

$stmt->close();

$total_price = array_reduce($cart_items, function ($total, $item) {
    return $total + $item['price'] * $item['quantity'];
}, 0);

// Display notification if set
if (isset($_SESSION['notification'])) {
    $notification = $_SESSION['notification'];
    $notificationClass = $_SESSION['notification_class'];
    unset($_SESSION['notification'], $_SESSION['notification_class']);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your Cart</title>
    <link rel="stylesheet" href="css/cart.css">
    <script src="https://www.paypal.com/sdk/js?client-id=AZeoASur185mEoI_Ds1k9OET1cgdacu9_7yIlGqrSEFAgABJeYnBkRb5PIjJkIrl7gc0pIDr7qmwM09j&currency=PHP"></script>
</head>
<body>
<?php include 'header.php'; ?>
<div class="container mt-5">
    <h2 class="text-center">Your Cart</h2>

    <!-- Notification display -->
    <?php if (isset($notification)): ?>
        <div class="notification <?= htmlspecialchars($notificationClass) ?>">
            <?= htmlspecialchars($notification) ?>
        </div>
    <?php endif; ?>

    <?php if (empty($cart_items)): ?>
        <p class="text-center">Your cart is empty.</p>
    <?php else: ?>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Total</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="cartItems">
                <?php foreach ($cart_items as $item): ?>
                    <tr data-id="<?= $item['id'] ?>">
                        <td><?= htmlspecialchars($item['itemname']) ?></td>
                        <td>PHP <?= number_format($item['price'], 2) ?></td>
                        <td>
                            <input type="number" class="quantity-input form-control" 
                                   value="<?= $item['quantity'] ?>" min="1" data-price="<?= $item['price'] ?>">
                        </td>
                        <td class="item-total">PHP <?= number_format($item['price'] * $item['quantity'], 2) ?></td>
                        <td>
                            <form method="POST" action="remove_from_cart.php" style="display:inline;">
                                <input type="hidden" name="remove_id" value="<?= $item['id'] ?>">
                                <button type="submit" class="btn btn-danger">Remove</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <tr>
                    <td colspan="3" class="text-end"><strong>Total</strong></td>
                    <td id="totalPrice">PHP <?= number_format($total_price, 2) ?></td>
                    <td></td>
                </tr>
            </tbody>
        </table>
        <div class="button-container">
            <a href="cafemenu.php" class="btn btn-primary">Add More</a>
            <button id="checkoutButton" class="btn btn-checkout">Checkout</button>
        </div>
        <!-- PayPal SDK integration in a hidden container -->
        <div id="paypal-button-container" style="display: none;"></div>
    <?php endif; ?>
</div>

<script src="js/cart.js"></script>
</body>
</html>

