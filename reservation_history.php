<?php
session_start();
include 'db_connect.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Enable error reporting for debugging
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    // Get the logged-in user's ID from the session
    $customer_id = $_SESSION['user_id'];

    // Fetch reservation history for the user
    $reservation_query = "
    SELECT 
        r.reservation_id AS reservation_id,
        r.reservation_date, 
        r.customer_name, 
        s.service_name AS service_type, 
        r.reservation_time, 
        r.end_time, 
        r.slot, 
        r.price, 
        r.paid_fee, 
        r.remaining_fee, 
        r.reservation_status 
    FROM reservations r
    JOIN services s ON r.service_type = s.id
    WHERE r.user_id = ?
    ORDER BY r.reservation_date DESC";

    // Prepare and execute the query
    $stmt = $conn->prepare($reservation_query);
    $stmt->bind_param("i", $customer_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $reservations = [];
    while ($row = $result->fetch_assoc()) {
        $reservations[] = $row;
    }

    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservation History</title>
    <link rel="stylesheet" href="css/reservation_history.css">
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="main-container">
        <h2>Your Reservation History</h2>

        <?php if (empty($reservations)): ?>
            <p>No reservations found.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Name</th>
                        <th>Service</th>
                        <th>Time Start</th>
                        <th>Time End</th>
                        <th>Status</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reservations as $reservation): ?>
                        <tr>
                            <td><?= htmlspecialchars($reservation['reservation_date']) ?></td>
                            <td><?= htmlspecialchars($reservation['customer_name']) ?></td>
                            <td><?= htmlspecialchars($reservation['service_type']) ?></td>
                            <td><?= htmlspecialchars($reservation['reservation_time']) ?></td>
                            <td><?= htmlspecialchars($reservation['end_time']) ?></td>
                            <td id="reservation_status_<?= $reservation['reservation_id'] ?>">
                                <?= htmlspecialchars($reservation['reservation_status']) ?>
                            </td>
                            <td>
                                <button class="button" onclick="showReceipt(`
                                    <strong>Service Name:</strong> <?= htmlspecialchars($reservation['service_type']) ?><br>
                                    <strong>Slot:</strong> <?= htmlspecialchars($reservation['slot']) ?><br>
                                    <strong>Price:</strong> <?= htmlspecialchars($reservation['price']) ?><br>
                                    <strong>Paid Fee:</strong> <?= htmlspecialchars($reservation['paid_fee']) ?> (Paid)<br>
                                    <strong>Remaining Fee:</strong> <?= htmlspecialchars($reservation['remaining_fee']) ?><br>
                                    <strong>Status:</strong> <?= htmlspecialchars($reservation['reservation_status']) ?>
                                `)">View Receipt</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <!-- Receipt Modal -->
    <div id="receiptModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <div id="receiptDetails"></div>
        </div>
    </div>

    <script src="js/reservation_history.js"></script>
</body>
</html>
