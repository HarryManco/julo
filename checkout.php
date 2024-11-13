<?php
session_start();

// Check if the cart exists
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header('Location: your_previous_page.php'); // Redirect if the cart is empty
    exit;
}

// Calculate total amount
$totalAmount = array_sum(array_map(function($item) {
    return $item['price'] * $item['quantity'];
}, $_SESSION['cart']));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Checkout</title>
    <script src="https://www.paypal.com/sdk/js?client-id=AZeoASur185mEoI_Ds1k9OET1cgdacu9_7yIlGqrSEFAgABJeYnBkRb5PIjJkIrl7gc0pIDr7qmwM09j&currency=PHP"></script>
</head>
<body>
    <div class="container mt-5">
        <h2>Checkout</h2>
        <h5>Total Amount: PHP <?php echo number_format($totalAmount, 2); ?></h5>

        <div id="paypal-button-container"></div>
    </div>

    <script>
    paypal.Buttons({
        createOrder: (data, actions) => {
            return actions.order.create({
                purchase_units: [{
                    amount: {
                        value: "<?php echo $totalAmount; ?>" // Pass the total amount here
                    }
                }]
            });
        },
        onApprove: (data, actions) => {
            return actions.order.capture().then(function(details) {
                // Handle post-payment actions
                alert('Transaction completed by ' + details.payer.name.given_name);
                // Optionally, you can clear the cart here
                <?php unset($_SESSION['cart']); // Clear the cart after payment ?>
                window.location.href = "checkout_success.php"; // Redirect to a success page
            });
        },
        onError: (err) => {
            console.error(err); // Handle errors
        }
    }).render('#paypal-button-container');
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
