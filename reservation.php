<?php
session_start();
include 'db_connect.php';

$message = "";
$message_class = "";

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Get the logged-in user's ID from the session
$customer_id = $_SESSION['user_id'];

// Check if the customer has any registered cars
$vehicle_check_query = "SELECT COUNT(*) AS vehicle_count FROM cars WHERE user_id = ?";
$stmt = $conn->prepare($vehicle_check_query);
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$has_vehicle = $row['vehicle_count'] > 0;
$stmt->close();

// Fetch the user's registered vehicles
$vehicles_query = "SELECT id, car_model, plate_no FROM cars WHERE user_id = ?";
$stmt = $conn->prepare($vehicles_query);
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$vehicles_result = $stmt->get_result();
$vehicles = [];
while ($vehicle = $vehicles_result->fetch_assoc()) {
    $vehicles[] = $vehicle;
}
$stmt->close();

// Fetch available services
$services_query = "SELECT id, service_name FROM services";
$services_result = mysqli_query($conn, $services_query);
$services = [];
while ($service = mysqli_fetch_assoc($services_result)) {
    $services[] = $service;
}

// Close connection after fetching data
$conn->close();

// Check for a success message in the URL
$success_message = isset($_GET['success']) && $_GET['success'] == 'true' ? "Reservation successfully made!" : "";
?>

<?php include 'header.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Make a Reservation</title>
    <link rel="stylesheet" href="css/reservation.css">
</head>
<body>

<div class="main-container">
    <?php if (!$has_vehicle): ?>
        <div class="notification warning">
            <p>You need to register a vehicle before making a reservation.</p>
            <a href="car_registration.php" class="register-link">Register Vehicle</a>
        </div>
    <?php else: ?>
        <div class="reservation-form-container">
            <h2>Make a Reservation</h2>

            <?php if ($success_message): ?>
                <div class="notification success"><?= htmlspecialchars($success_message); ?></div>
            <?php endif; ?>

            <?php $selectedDate = isset($_GET['date']) ? $_GET['date'] : ''; ?>

            <form id="reservationForm">
                <label for="reservation_date">Selected Date:</label>
                <input type="text" id="reservation_date" name="reservation_date" value="<?= htmlspecialchars($selectedDate); ?>" readonly>

                <label for="customer_name">Full Name:</label>
                <input type="text" id="customer_name" name="customer_name" required>

                <label for="vehicle_type">Select Vehicle:</label>
                <select id="vehicle_type" name="vehicle_type" required onchange="updatePriceAndDuration()">
                    <option value="">--Select Vehicle--</option>
                    <?php foreach ($vehicles as $vehicle): ?>
                        <option value="<?= $vehicle['id'] ?>" data-model="<?= $vehicle['car_model'] ?>"><?= $vehicle['car_model'] ?> (<?= $vehicle['plate_no'] ?>)</option>
                    <?php endforeach; ?>
                </select>

                <label for="phone">Phone Number:</label>
                <input type="tel" id="phone" name="phone" required>

                <label for="service_type">Select Service:</label>
                <select id="service_type" name="service_type" required onchange="updatePriceAndDuration()">
                    <option value="">--Select Service--</option>
                    <?php foreach ($services as $service): ?>
                        <option value="<?= $service['id'] ?>"><?= $service['service_name'] ?></option>
                    <?php endforeach; ?>
                </select>

                <label for="price">Service Price:</label>
                <input type="text" id="price" name="price" readonly>

                <label for="paid_fee">Paid Fee:</label>
                <input type="text" id="paid_fee" name="paid_fee" value="1.00" readonly>

                <label for="remaining_fee">Remaining Fee:</label>
                <input type="text" id="remaining_fee" name="remaining_fee" readonly>

                <label for="slot">Select Slot:</label>
                <select id="slot" name="slot" required>
                    <option value="">--Select Slot--</option>
                    <option value="1">1</option>
                    <option value="2">2</option>
                    <option value="3">3</option>
                </select>

                <label for="reservation_time">Select Time:</label>
                <select id="reservation_time" name="reservation_time" required>
                    <option value="">--Select Time--</option>
                </select>

                <label for="end_time">End Time:</label>
                <input type="text" id="end_time" name="end_time" readonly>
            </form>

            <!-- PayPal Button Container -->
            <div id="paypal-button-container"></div>
        </div>
    <?php endif; ?>
</div>
    <!-- Include PayPal SDK -->
    <script src="https://www.paypal.com/sdk/js?client-id=AZeoASur185mEoI_Ds1k9OET1cgdacu9_7yIlGqrSEFAgABJeYnBkRb5PIjJkIrl7gc0pIDr7qmwM09j&currency=PHP"></script>

    <!-- PayPal Button Integration -->
    <script>
        paypal.Buttons({
            createOrder: function(data, actions) {
                return actions.order.create({
                    purchase_units: [{
                        amount: {
                            value: document.getElementById('paid_fee').value // Ensure 'paid_fee' has a valid amount
                        }
                    }]
                });
            },
            onApprove: function(data, actions) {
                return actions.order.capture().then(function(details) {
                    alert('Payment completed by ' + details.payer.name.given_name);

                    // Send form data to server for reservation
                    const formData = new FormData(document.getElementById('reservationForm'));
                    fetch('process_reservation.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.text())
                    .then(data => {
                        window.location.href = "reservation.php?success=true";
                    })
                    .catch(error => console.error('Error:', error));
                });
            }
        }).render('#paypal-button-container'); // Render PayPal button
    </script>
<script src="js/reservation.js"></script>
</body>
</html>
