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
    <a href="#">Home</a>
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
    <a href="manage_queue.php">Queue</a>
    <a href="manage_carsize.php">Car Sizing</a>
    <a href="manage_services.php">Services</a>
    <a href="manage_service_price.php">Pricing</a>
    <a href="carwash_transactions.php">Carwash Transaction</a>
    <a href="#">Sales Report</a>
    <a href="login.php">Log Out</a>
</div>
<!-- Main content area -->
<div class="main-content">
    <!-- Rest of your page content goes here -->
</div>

<script src="js/sidebar.js"></script>
</body>
</html>
