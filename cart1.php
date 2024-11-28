<?php
session_start();
require 'db_connect.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Cart</title>
    <link rel="stylesheet" href="css/cart1.css">
    <!-- Include PayPal SDK -->
    <script src="https://www.paypal.com/sdk/js?client-id=AZeoASur185mEoI_Ds1k9OET1cgdacu9_7yIlGqrSEFAgABJeYnBkRb5PIjJkIrl7gc0pIDr7qmwM09j&currency=PHP"></script>
</head>
<body>
<?php include 'header.php'; ?>
<div class="container">
    <h2>Your Cart</h2>

    <!-- Notification Messages -->
    <?php if (!empty($_SESSION['error_message'])): ?>
        <p class="message error"><?= htmlspecialchars($_SESSION['error_message']); ?></p>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['success_message'])): ?>
        <p class="message success"><?= htmlspecialchars($_SESSION['success_message']); ?></p>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <!-- Cart Table -->
    <?php if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])): ?>
        <?php
        $valid_cart_items = [];
        $total = 0;
        ?>
        <table>
            <tr>
                <th>Item</th>
                <th>Quantity</th>
                <th>Price</th>
                <th>Subtotal</th>
                <th>Actions</th>
            </tr>
            <?php foreach ($_SESSION['cart'] as $menu_item_id => $quantity): ?>
                <?php
                // Query the database to verify the item exists
                $stmt = $conn->prepare("SELECT id, name, price FROM menu_items WHERE id = ?");
                $stmt->bind_param("i", $menu_item_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $item = $result->fetch_assoc();
                $stmt->close();
                ?>
                <?php if ($item): ?>
                    <?php
                    $subtotal = $item['price'] * $quantity;
                    $total += $subtotal;
                    $valid_cart_items[$menu_item_id] = $quantity;
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($item['name']); ?></td>
                        <td>
                            <form method="post" action="update_cart_quantity1.php" class="quantity-form">
                                <input type="hidden" name="menu_item_id" value="<?= $menu_item_id; ?>">
                                <button type="submit" name="action" value="decrease">-</button>
                                <span><?= htmlspecialchars($quantity); ?></span>
                                <button type="submit" name="action" value="increase">+</button>
                            </form>
                        </td>
                        <td>P<?= number_format($item['price'], 2); ?></td>
                        <td>P<?= number_format($subtotal, 2); ?></td>
                        <td>
                            <form method="post" action="remove_from_cart1.php">
                                <input type="hidden" name="menu_item_id" value="<?= $menu_item_id; ?>">
                                <button type="submit" name="remove" value="1">Remove</button>
                            </form>
                        </td>
                    </tr>
                <?php else: ?>
                    <tr>
                        <td colspan="5">Item with ID <?= $menu_item_id; ?> not found. It has been removed from your cart.</td>
                    </tr>
                <?php endif; ?>
            <?php endforeach; ?>
            <?php $_SESSION['cart'] = $valid_cart_items; ?>
        </table>
        <h3>Total: P<?= number_format($total, 2); ?></h3>

        <!-- Checkout and Add More Buttons -->
        <div class="checkout-btn">
            <button id="checkout-button">Checkout</button>
            <form method="post" action="cafe.php" style="display: inline;">
                <button type="submit">Add More</button>
            </form>
        </div>

        <!-- PayPal Button Container -->
        <div id="paypal-button-container" style="margin-top: 20px;"></div>
    <?php else: ?>
        <p>Your cart is empty</p>
        <form method="post" action="cafe.php">
            <button type="submit">Add Order</button>
        </form>
    <?php endif; ?>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const checkoutButton = document.getElementById('checkout-button');
        const paypalContainer = document.getElementById('paypal-button-container');
        const totalAmount = <?= json_encode($total); ?>;

        // Render PayPal button below when Checkout is clicked
        checkoutButton.addEventListener('click', function () {
            if (!paypalContainer.innerHTML.trim()) {
                paypal.Buttons({
                    createOrder: function (data, actions) {
                        return actions.order.create({
                            purchase_units: [{
                                amount: {
                                    value: totalAmount.toFixed(2)
                                }
                            }]
                        });
                    },
                    onApprove: function (data, actions) {
                        return actions.order.capture().then(function (details) {
                            // Send cart details to the server
                            const orderDetails = {
                                cart: <?= json_encode($_SESSION['cart']); ?>,
                                total: totalAmount
                            };

                            fetch('process_order.php', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify(orderDetails)
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    // Store the success message in session for displaying in cart
                                    sessionStorage.setItem("success_message", data.message);
                                    window.location.href = "cart1.php"; // Reload the cart page to show success message
                                } else {
                                    alert("Error: " + data.message);
                                }
                            })
                            .catch(error => {
                                console.error("Error:", error);
                                alert("An error occurred. Please try again.");
                            });
                        });
                    },
                    onError: function (err) {
                        console.error(err);
                        alert("Payment failed. Please try again.");
                    }
                }).render('#paypal-button-container');
            }
        });
    });
</script>
</body>
</html>
