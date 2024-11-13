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

// Fetch all reservations
$reservations_query = "
    SELECT reservations.*, users.username, cars.car_model, services.service_name, services.duration 
    FROM reservations
    JOIN users ON reservations.user_id = users.id
    JOIN cars ON reservations.car_id = cars.id
    JOIN services ON reservations.service_id = services.id
";
$reservations_result = mysqli_query($conn, $reservations_query);

// Check for query execution errors
if (!$reservations_result) {
    echo "Error: " . mysqli_error($conn);
    exit();
}

// Display reservations if the query is successful
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View All Reservations</title>
    <link rel="stylesheet" href="css/viewreservation.css">
</head>
<body>
    <div class="container">
        <h2>All Reservations</h2>

        <table>
            <thead>
                <tr>
                    <th>Reservation ID</th>
                    <th>Username</th>
                    <th>Car Model</th>
                    <th>Service</th>
                    <th>Duration</th>
                    <th>Reservation Date</th>
                    <th>Reservation Time</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($reservation = mysqli_fetch_assoc($reservations_result)): ?>
                <tr>
                    <td><?= $reservation['id'] ?></td>
                    <td><?= $reservation['username'] ?></td>
                    <td><?= $reservation['car_model'] ?></td>
                    <td><?= $reservation['service_name'] ?></td>
                    <td><?= $reservation['duration'] ?> mins</td>
                    <td><?= $reservation['reservation_date'] ?></td>
                    <td><?= $reservation['reservation_time'] ?></td>
                    <td><?= ucfirst($reservation['reservation_status']) ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <a href="admin_dashboard.php">Back to Dashboard</a>
    </div>
</body>
</html>
