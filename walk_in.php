<?php
session_start();
include 'db_connect.php';

$message = "";
$message_class = "";

// Fetch available vehicles from car_sizes
$vehicles_query = "SELECT id, car_model FROM car_sizes";
$vehicles_result = mysqli_query($conn, $vehicles_query);
$vehicles = [];
while ($vehicle = mysqli_fetch_assoc($vehicles_result)) {
    $vehicles[] = $vehicle;
}

// Fetch available services
$services_query = "SELECT id, service_name FROM services";
$services_result = mysqli_query($conn, $services_query);
$services = [];
while ($service = mysqli_fetch_assoc($services_result)) {
    $services[] = $service;
}

// Fetch today's queue based on slots
$today_date = date("Y-m-d");
$queue_query = "SELECT queue_id, customer_id, start_time, end_time, assigned_slot FROM queue WHERE queue_date = ? ORDER BY assigned_slot, start_time";
$stmt = $conn->prepare($queue_query);
$stmt->bind_param("s", $today_date);
$stmt->execute();
$queue_result = $stmt->get_result();

$queues = ['1' => [], '2' => [], '3' => []];
while ($row = mysqli_fetch_assoc($queue_result)) {
    $slot = $row['assigned_slot'];
    $queues[$slot][] = $row;
}
$stmt->close();
$conn->close();
?>
<?php include 'sidebar.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Walk-In Customer</title>
    <link rel="stylesheet" href="css/walk_in.css">
</head>
<body>
<div class="container">
    <h2>Add Walk-In Customer</h2>

    <!-- Notification message area -->
    <div id="notification" class="notification <?= $message_class ?>" style="display: <?= $message ? 'block' : 'none' ?>;">
        <?= $message ?>
    </div>

    <form id="walkInForm">
        <label for="customer_name">Customer Name:</label>
        <input type="text" id="customer_name" name="customer_name" required placeholder="Enter customer name">

        <label for="vehicle_type">Select Vehicle:</label>
        <select id="vehicle_type" name="vehicle_type" required>
            <option value="">--Select Vehicle--</option>
            <?php foreach ($vehicles as $vehicle): ?>
                <option value="<?= $vehicle['id'] ?>"><?= $vehicle['car_model'] ?></option>
            <?php endforeach; ?>
        </select>

        <label for="service_type">Select Service:</label>
        <select id="service_type" name="service_type" required>
            <option value="">--Select Service--</option>
            <?php foreach ($services as $service): ?>
                <option value="<?= $service['id'] ?>"><?= $service['service_name'] ?></option>
            <?php endforeach; ?>
        </select>

        <label for="slot">Select Slot:</label>
        <select id="slot" name="slot" required>
            <option value="">--Select Slot--</option>
            <option value="1">1</option>
            <option value="2">2</option>
            <option value="3">3</option>
        </select>

        <label for="price">Price:</label>
        <input type="text" id="price" name="price" readonly>

        <label for="duration">Duration:</label>
        <input type="text" id="duration" name="duration" readonly>

        <button type="submit">Add Walk-In</button>
    </form>

    <!-- Today's Queue Section -->
    <div class="queue-section">
        <h3>Today's Queue</h3>

        <?php for ($slot = 1; $slot <= 3; $slot++): ?>
            <div class="slot-section">
                <h4>Slot <?= $slot ?></h4>
                <table class="queue-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Start Time</th>
                            <th>End Time</th>
                        </tr>
                    </thead>
                    <tbody id="slot<?= $slot ?>Queue">
                        <?php if (!empty($queues[$slot])): ?>
                            <?php foreach ($queues[$slot] as $entry): ?>
                                <tr>
                                    <td><?= $entry['customer_id'] ?></td>
                                    <td><?= date("H:i", strtotime($entry['start_time'])) ?></td>
                                    <td><?= date("H:i", strtotime($entry['end_time'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="3">No entries for this slot</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        <?php endfor; ?>
    </div>
</div>

<script src="js/walk_in.js"></script>
</body>
</html>
