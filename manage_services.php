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

// Initialize message variables
$message = "";
$message_class = "";

// Handle adding a new service
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_service'])) {
    $service_name = $_POST['service_name'];
    $service_quantity = $_POST['service_quantity'];

    // Insert the new service with quantity
    $insert_service = "INSERT INTO services (service_name, quantity) VALUES ('$service_name', $service_quantity)";
    if (mysqli_query($conn, $insert_service)) {
        $message = "Service added successfully!";
        $message_class = "success";
    } else {
        $message = "Error: " . mysqli_error($conn);
        $message_class = "error";
    }
}

// Fetch all services
$services_query = "SELECT * FROM services";
$services_result = mysqli_query($conn, $services_query);
?>
<?php include 'sidebar.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Services</title>
    <link rel="stylesheet" href="css/viewreservation.css">
</head>
<body>
    <div class="container">
        <h2>Manage Services</h2>

        <?php if ($message): ?>
            <div class="notification <?= $message_class ?>"><?= $message ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <label for="service_name">Service Name:</label>
            <input type="text" name="service_name" required placeholder="Enter service name">

            <label for="service_quantity">Service Quantity:</label>
            <input type="number" name="service_quantity" required placeholder="Enter available quantity">

            <button type="submit" name="add_service">Add Service</button>
        </form>

        <h3>Existing Services</h3>
        <table>
            <thead>
                <tr>
                    <th>Service Name</th>
                    <th>Quantity</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($service = mysqli_fetch_assoc($services_result)): ?>
                <tr>
                    <td><?= $service['service_name'] ?></td>
                    <td><?= $service['quantity'] ?></td>
                    <td>
                        <a href="edit_service.php?id=<?= $service['id'] ?>">Edit</a> | 
                        <a href="delete_service.php?id=<?= $service['id'] ?>" onclick="return confirm('Are you sure you want to delete this service?')">Delete</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <a href="admin_dashboard.php" class="back-link">Back to Dashboard</a>
    </div>
</body>
</html>
