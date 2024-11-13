<?php
// Include database connection
include 'db_connect.php';

$edit_mode = false;
$message = "";
$message_class = "";

// Fetch all added service prices initially
$prices_query = "SELECT sp.id, s.service_name, sp.car_model, sp.price, sp.duration
                 FROM service_prices sp
                 JOIN services s ON sp.service_id = s.id";
$prices_result = mysqli_query($conn, $prices_query);
if (!$prices_result) {
    die("Error fetching service prices: " . mysqli_error($conn));
}

// Fetch available services for the form
$services_query = "SELECT id, service_name FROM services";
$services_result = mysqli_query($conn, $services_query);
if (!$services_result) {
    die("Error fetching services: " . mysqli_error($conn));
}

// Fetch available car models for the form
$cars_query = "SELECT car_model FROM car_sizes";
$cars_result = mysqli_query($conn, $cars_query);
if (!$cars_result) {
    die("Error fetching car models: " . mysqli_error($conn));
}
?>
<?php include 'sidebar.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Service Prices</title>
    <link rel="stylesheet" href="css/viewreservation.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> <!-- Include jQuery for AJAX -->
</head>
<body>
    <div class="container">
        <h2>Manage Service Prices</h2>

        <form id="servicePriceForm">
            <label for="service_id">Select Service:</label>
            <select name="service_id" id="service_id" required>
                <option value="0">--Select Service--</option>
                <?php while ($service = mysqli_fetch_assoc($services_result)): ?>
                    <option value="<?= $service['id'] ?>"><?= $service['service_name'] ?></option>
                <?php endwhile; ?>
            </select>

            <label for="car_model">Select Car Model:</label>
            <select name="car_model" id="car_model" required>
                <option value="">--Select Car Model--</option>
                <?php while ($car = mysqli_fetch_assoc($cars_result)): ?>
                    <option value="<?= $car['car_model'] ?>"><?= $car['car_model'] ?></option>
                <?php endwhile; ?>
            </select>

            <label for="price">Enter Service Price:</label>
            <input type="number" step="0.01" name="price" id="price" required placeholder="Enter price">

            <label for="duration">Enter Service Duration (minutes):</label>
            <input type="number" name="duration" id="duration" required placeholder="Enter duration">

            <button type="submit">Add Service Price</button>
        </form>

        <h3>Added Service Prices</h3>
        <table>
            <thead>
                <tr>
                    <th>Service Name</th>
                    <th>Car Model</th>
                    <th>Price</th>
                    <th>Duration (minutes)</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="servicePriceTableBody">
                <?php while ($price = mysqli_fetch_assoc($prices_result)): ?>
                    <tr id="priceRow-<?= $price['id'] ?>">
                        <td><?= $price['service_name'] ?></td>
                        <td><?= $price['car_model'] ?></td>
                        <td>P<?= number_format($price['price'], 2) ?></td>
                        <td><?= $price['duration'] ?></td>
                        <td>
                            <a href="edit_service_price.php?id=<?= $price['id'] ?>">Edit</a> |
                            <a href="delete_service_price.php?id=<?= $price['id'] ?>" onclick="return confirm('Are you sure you want to delete this service price?')">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <a href="admin_dashboard.php" class="back-button">Back to Dashboard</a>
    </div>
    <script src="js/manage_service_price.js"></script> <!-- Link to the external JavaScript file -->
</head>
</body>
</html>
