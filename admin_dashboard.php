<?php
// Start the session
session_start();

// Check if the user is logged in as admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Include database connection
include 'db_connect.php';

// Fetch the number of total reservations
$reservations_query = "SELECT COUNT(*) AS total_reservations FROM reservations";
$reservations_result = mysqli_query($conn, $reservations_query);
$reservation_count = mysqli_fetch_assoc($reservations_result)['total_reservations'];

// Fetch the number of total services
$services_query = "SELECT COUNT(*) AS total_services FROM services";
$services_result = mysqli_query($conn, $services_query);
$service_count = mysqli_fetch_assoc($services_result)['total_services'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body>
    <div class="sidebar">
        <h2>Admin Dashboard</h2>
        <ul>
            <li><a href="dashboard.php">Home</a></li>
            <li class="dropdown">
                <a href="javascript:void(0)">Reservations</a>
                <div class="dropdown-content">
                    <a href="view_reservation.php">View All Reservations</a>
                    <a href="manage_reservations.php">Manage Pending Reservations</a>
                </div>
            </li>
            <li class="dropdown">
                <a href="javascript:void(0)"> Walk-In</a>
                <div class="dropdown-content">
                    <a href="manage_walk_in.php">Manage Walk-In</a>
                    <a href="walk_in.php">Add Walk-In</a>
                </div>
            </li>
            <li><a href="manage_queue.php">Queuing</a></li>
            <li><a href="manage_carsize.php">Car Sizing</a></li>
            <li><a href="manage_services.php">Services</a></li>
            <li><a href="manage_service_price.php">Service Pricing</a></li>
            <li class="dropdown">
                <a href="javascript:void(0)">Cafe</a>
                <div class="dropdown-content">
                    <a href="view_orders.php">View All Cafe Orders</a>
                    <a href="manage_orders.php">Manage Cafe Orders</a>
                    <a href="admin_cafe.php">Manage Cafe Products</a>
                </div>
            </li>
            <li><a href="carwash_transactions.php">Transactions</a></li>
            <li><a href="sales_report.php">Sales Report</a></li>
            <li><a href="login.php"><img src="images/icons8-logout-24.png" alt="Logout Icon"> Log Out</a></li>
        </ul>
    </div>
    
    <div class="main-content">
        <div class="header">
            <h1>Admin Dashboard</h1>
        </div>
        <div class="stats">
            <div class="stat-box">
                <h3>Total Reservations</h3>
                <p><?= $reservation_count ?></p>
            </div>
            <div class="stat-box">
                <h3>Total Services</h3>
                <p><?= $service_count ?></p>
            </div>
        </div>
    </div>
</body>
</html>
