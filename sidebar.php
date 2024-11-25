<?php
include 'db_connect.php'; // Include your database connection file

// Fetch count of unread notifications for admin (user_id IS NULL)
$query = "SELECT COUNT(*) AS unread_count FROM notifications WHERE user_id IS NULL AND status = 'unread'";
$result = $conn->query($query);
$unread_count = 0;

if ($result) {
    $row = $result->fetch_assoc();
    $unread_count = $row['unread_count'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <link rel="stylesheet" href="css/admin_sidebar.css">
</head>
<body>
<div class="admin-sidebar">
    <h2>Admin Panel</h2>
    <a href="sidebar.php">Home</a>
    <a href="admin_notifications.php">
        Notifications 
        <?php if ($unread_count > 0): ?>
            <span class="notification-count"><?php echo $unread_count; ?></span>
        <?php endif; ?>
    </a>
    <div class="dropdown">
        <a href="#">Reservations</a>
        <div class="dropdown-content">
            <a href="manage_reservations.php">Manage Reservations</a>
        </div>
    </div>
    <div class="dropdown">
        <a href="#">Walk-In</a>
        <div class="dropdown-content">
            <a href="manage_walk_in.php">Manage Walk-In</a>
            <a href="walk_in.php">Add Walk-In</a>
        </div>
    </div>
    <div class="dropdown">
        <a href="#">Manage Cafe</a>
        <div class="dropdown-content">
            <a href="admin_cafe.php">Cafe Products</a>
            <a href="manage_orders.php">Cafe Orders</a>
        </div>
    </div>
    <a href="manage_queue.php">Manage Queue</a>
    <a href="#">Manage Car Request</a>
    <a href="manage_carsize.php">Manage Car Sizing</a>
    <a href="manage_services.php">Manage Services</a>
    <a href="manage_service_price.php">Manage Service Pricing</a>
    <a href="carwash_transactions.php">Carwash Transaction</a>
    <a href="#">Cafe Transaction</a>
    <a href="sales_report.php">Carwash Sales</a>
    <a href="#">Cafe Sales</a>
    <a href="login.php">Log Out</a>
</div>
<!-- Main content area -->
<div class="main-content">
    <!-- Rest of your page content goes here -->
</div>

<script src="js/sidebar.js"></script>
</body>
</html>
