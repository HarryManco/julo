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

    <?php if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])): ?>
        <table>
            <tr>
                <th>Item</th>
                <th>Quantity</th>
                <th>Price</th>
                <th>Subtotal</th>
                <th>Actions</th>
            </tr>
            <?php
            $valid_cart_items = [];
            $total = 0;

            foreach ($_SESSION['cart'] as $menu_item_id => $quantity):
                $stmt = $conn->prepare("SELECT id, name, price FROM menu_items WHERE id = ?");
                $stmt->bind_param("i", $menu_item_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $item = $result->fetch_assoc();
                $stmt->close();

                if ($item):
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
        <div class="checkout-btn">
            <button id="checkout-button">Checkout</button>
            <form method="post" action="cafe.php" style="display: inline;">
                <button type="submit">Add More</button>
            </form>
        </div>
    <?php else: ?>
        <p>Your cart is empty</p>
        <form method="post" action="cafe.php">
            <button type="submit">Add Order</button>
        </form>
    <?php endif; ?>
</div>

<!-- Modal for PayPal Button -->
<div id="paypal-modal" class="modal">
    <div class="modal-content">
        <span class="close-btn">&times;</span>
        <h2>Complete Your Payment</h2>
        <div id="paypal-button-container"></div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modal = document.getElementById('paypal-modal');
        const closeModal = document.querySelector('.close-btn');
        const checkoutButton = document.getElementById('checkout-button');
        const totalAmount = <?= json_encode($total); ?>;

        // Show modal and render PayPal button
        checkoutButton.addEventListener('click', function () {
            modal.style.display = 'block';

            paypal.Buttons({
                createOrder: function (data, actions) {
                    return actions.order.create({
                        purchase_units: [{
                            amount: {
                                value: totalAmount
                            }
                        }]
                    });
                },
                onApprove: function (data, actions) {
                    return actions.order.capture().then(function (details) {
                        alert('Payment completed by ' + details.payer.name.given_name);
                        window.location.href = "checkout_success.php";
                    });
                },
                onError: function (err) {
                    console.error(err);
                }
            }).render('#paypal-button-container');
        });

        // Close modal
        closeModal.addEventListener('click', function () {
            modal.style.display = 'none';
        });

        // Close modal when clicking outside of it
        window.addEventListener('click', function (event) {
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        });
    });
</script>
</body>
</html>
