<?php
session_start();
require_once 'db_connect.php';

// Check if cart exists in the session
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    echo "<p>Your cart is empty.</p>";
    exit;
}

// Initialize total price
$totalPrice = 0;

// If the form is submitted to update quantities
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_cart'])) {
    foreach ($_POST['quantity'] as $itemId => $quantity) {
        if ($quantity > 0) {
            $_SESSION['cart'][$itemId]['quantity'] = $quantity;
        } else {
            unset($_SESSION['cart'][$itemId]);
        }
    }
    header("Location: view_cart.php");
    exit;
}

// Remove an item from the cart
if (isset($_GET['remove'])) {
    $removeId = $_GET['remove'];
    unset($_SESSION['cart'][$removeId]);
    header("Location: view_cart.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Added viewport for responsiveness -->
    <title>View Cart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
</head>
<body>
<div class="container mt-5">
    <h2>Your Cart</h2>
    <form method="post" action="view_cart.php">
        <table class="table table-striped table-hover"> <!-- Added table styles for better UX -->
            <thead class="table-light">
                <tr>
                    <th>Item</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Subtotal</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($_SESSION['cart'] as $itemId => $item): ?>
                    <?php
                    $subtotal = $item['price'] * $item['quantity'];
                    $totalPrice += $subtotal;
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['name']); ?></td>
                        <td>PHP <?php echo number_format($item['price'], 2); ?></td>
                        <td>
                            <input type="number" name="quantity[<?php echo $itemId; ?>]" value="<?php echo $item['quantity']; ?>" min="1" class="form-control" style="width: 80px;">
                        </td>
                        <td>PHP <?php echo number_format($subtotal, 2); ?></td>
                        <td>
                            <a href="view_cart.php?remove=<?php echo $itemId; ?>" class="btn btn-danger btn-sm">Remove</a> <!-- Small button for better fit -->
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" class="text-end"><strong>Total:</strong></td>
                    <td colspan="2">PHP <?php echo number_format($totalPrice, 2); ?></td>
                </tr>
            </tfoot>
        </table>
        <div class="d-flex justify-content-between mt-4"> <!-- Added margin-top for spacing -->
            <button type="submit" name="update_cart" class="btn btn-primary">Update Cart</button>
            <a href="checkout.php" class="btn btn-success">Proceed to Checkout</a>
            <a href="menu.php" class="btn btn-secondary">Continue Shopping</a>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL"
        crossorigin="anonymous"></script>
</body>
</html>
