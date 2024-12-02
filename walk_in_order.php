<?php
require 'db_connect.php';
session_start();

// Fetch menu items
$stmt = $conn->prepare("SELECT id, name, price, image FROM menu_items");
$stmt->execute();
$menu_items = $stmt->get_result();
?>
<?php include 'sidebar.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Walk-In Order</title>
    <link rel="stylesheet" href="css/walk_in_order.css">
</head>
<body>
    <div class="container">
        <h2>Walk-In Order</h2>

        <!-- Notification Messages -->
        <?php if (!empty($_SESSION['success_message'])): ?>
            <p class="success-message"><?= htmlspecialchars($_SESSION['success_message']); ?></p>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>
        <?php if (!empty($_SESSION['error_message'])): ?>
            <p class="error-message"><?= htmlspecialchars($_SESSION['error_message']); ?></p>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>

        <form action="process_walk_in_order.php" method="POST" id="order-form">
            <!-- Customer Name Input -->
            <div class="customer-name">
                <label for="customer_name">Customer Name:</label>
                <input type="text" id="customer_name" name="customer_name" placeholder="Enter Customer Name" form="order-form" required>
            </div>

            <div class="content">
                <!-- Menu Items Section -->
                <div class="menu-items">
                    <h3>Items</h3>
                    <div class="items-grid">
                        <?php while ($item = $menu_items->fetch_assoc()): ?>
                            <div class="menu-item">
                                <img src="images/<?= htmlspecialchars($item['image']); ?>" alt="<?= htmlspecialchars($item['name']); ?>">
                                <h4><?= htmlspecialchars($item['name']); ?></h4>
                                <p>P<?= number_format($item['price'], 2); ?></p>
                                <button type="button" class="add-to-order" data-id="<?= $item['id']; ?>" data-name="<?= htmlspecialchars($item['name']); ?>" data-price="<?= $item['price']; ?>">Add to Order</button>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>

                <!-- Orders Section -->
                <div class="orders">
                    <h3>Orders</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Items</th>
                                <th>Quantity</th>
                                <th>Price</th>
                            </tr>
                        </thead>
                        <tbody id="order-items">
                            <!-- Order items will be dynamically added here -->
                        </tbody>
                    </table>
                    <div class="total">
                        <span>Total</span>
                        <span id="order-total">P0.00</span>
                    </div>
                    <button type="submit" class="submit-order">Submit</button>
                </div>
            </div>
        </form>
    </div>
    <script src="js/walk_in_order.js"></script>
</body>
</html>
