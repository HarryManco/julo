<?php
session_start();
include 'db_connect.php';

// Check if the user is logged in as admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Handle status update
$message = "";
$message_class = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $reservation_id = $_POST['reservation_id'];
    $reservation_status = $_POST['reservation_status'];
    $payment_status = $_POST['payment_status'];

    // Fetch reservation details
    $fetch_query = "SELECT price, paid_fee, remaining_fee, user_id, customer_name FROM reservations WHERE reservation_id = ?";
    $fetch_stmt = $conn->prepare($fetch_query);
    $fetch_stmt->bind_param("i", $reservation_id);
    $fetch_stmt->execute();
    $reservation = $fetch_stmt->get_result()->fetch_assoc();
    $fetch_stmt->close();

    if ($reservation) {
        $price = $reservation['price'];
        $paid_fee = $reservation['paid_fee'];
        $remaining_fee = $reservation['remaining_fee'];
        $user_id = $reservation['user_id'];
        $customer_name = $reservation['customer_name'];
    } else {
        $message = "Reservation not found.";
        $message_class = "error";
        return;
    }

    // Prevent updating to Completed if Payment Status is Unpaid
    if ($reservation_status == 'Completed' && $payment_status == 'Unpaid') {
        $message = "Cannot mark as Completed when Payment Status is Unpaid.";
        $message_class = "error";
    } else {
        // Begin a transaction to ensure data consistency
        $conn->begin_transaction();
        try {
            // Update reservation and payment status
            $update_query = "UPDATE reservations SET reservation_status = ?, payment_status = ? WHERE reservation_id = ?";
            $update_stmt = $conn->prepare($update_query);
            $update_stmt->bind_param("ssi", $reservation_status, $payment_status, $reservation_id);
            $update_stmt->execute();
            $update_stmt->close();

            // If status is Completed and payment is Fully Paid, create transaction and adjust fees
            if ($reservation_status == 'Completed' && $payment_status == 'Fully Paid') {
                // Create a transaction for the remaining fee
                if ($remaining_fee > 0) {
                    $transaction_query = "INSERT INTO carwash_transactions (customer_id, customer_name, transaction_type, payment_type, amount, payment_method) 
                                          VALUES (?, ?, 'Reservation', 'Remaining Fee', ?, 'Cash')";
                    $transaction_stmt = $conn->prepare($transaction_query);
                    $transaction_stmt->bind_param("isd", $user_id, $customer_name, $remaining_fee);

                    if (!$transaction_stmt->execute()) {
                        throw new Exception("Error inserting transaction for remaining fee: " . $transaction_stmt->error);
                    }
                    $transaction_stmt->close();
                }

                // Update the reservation record to set remaining fee to 0 and add the remaining fee to paid fee
                $new_paid_fee = $paid_fee + $remaining_fee;
                $remaining_fee = 0;

                $fee_update_query = "UPDATE reservations SET paid_fee = ?, remaining_fee = ? WHERE reservation_id = ?";
                $fee_update_stmt = $conn->prepare($fee_update_query);
                $fee_update_stmt->bind_param("dii", $new_paid_fee, $remaining_fee, $reservation_id);
                $fee_update_stmt->execute();
                $fee_update_stmt->close();

                // Add a customer notification
                $notification_message = "Your vehicle service has been completed. The total fee of ₱" . number_format($new_paid_fee, 2) . " has been successfully paid. Thank you! Come again.";
                $notification_sql = "INSERT INTO notifications (user_id, message, type) VALUES (?, ?, 'reservation_update')";
                $notification_stmt = $conn->prepare($notification_sql);
                $notification_stmt->bind_param("is", $user_id, $notification_message);
                $notification_stmt->execute();
                $notification_stmt->close();

                $message = "Reservation and transaction records updated successfully!";
                $message_class = "success";
            }

            $conn->commit();
        } catch (Exception $e) {
            $conn->rollback();
            $message = "An error occurred: " . $e->getMessage();
            $message_class = "error";
        }
    }
}

// Fetch all reservations
$reservations_query = "SELECT * FROM reservations ORDER BY reservation_date DESC";
$reservations_result = mysqli_query($conn, $reservations_query);
?>
<?php include 'sidebar.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Reservations</title>
    <link rel="stylesheet" href="css/admin_reservations.css">
</head>
<body>
    <div class="container">
        <h2>Manage Reservations</h2>

        <!-- Display notification message -->
        <?php if (!empty($message)): ?>
            <div class="notification <?= $message_class ?>"><?= $message ?></div>
        <?php endif; ?>

        <table>
            <thead>
                <tr>
                    <th>Reservation Date</th>
                    <th>Customer Name</th>
                    <th>Service</th>
                    <th>Slot</th>
                    <th>Price</th>
                    <th>Paid Fee</th>
                    <th>Remaining Fee</th>
                    <th>Reservation Status</th>
                    <th>Payment Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($reservation = mysqli_fetch_assoc($reservations_result)): ?>
                <tr>
                    <form method="POST" action="manage_reservations.php">
                        <td><?= htmlspecialchars($reservation['reservation_date']) ?></td>
                        <td><?= htmlspecialchars($reservation['customer_name']) ?></td>
                        <td><?= htmlspecialchars($reservation['service_type']) ?></td>
                        <td><?= htmlspecialchars($reservation['slot']) ?></td>
                        <td>P<?= number_format($reservation['price'], 2) ?></td>
                        <td>P<?= number_format($reservation['paid_fee'], 2) ?></td>
                        <td>P<?= number_format($reservation['remaining_fee'], 2) ?></td>
                        <td><?= htmlspecialchars($reservation['reservation_status']) ?></td>
                        <td><?= htmlspecialchars($reservation['payment_status']) ?></td>
                        <td>
                            <input type="hidden" name="reservation_id" value="<?= $reservation['reservation_id'] ?>">
                            <div class="action-container">
                                <select name="reservation_status">
                                    <option value="Waiting" <?= $reservation['reservation_status'] == 'Waiting' ? 'selected' : '' ?>>Waiting</option>
                                    <option value="Serving" <?= $reservation['reservation_status'] == 'Serving' ? 'selected' : '' ?>>Serving</option>
                                    <option value="Finished" <?= $reservation['reservation_status'] == 'Finished' ? 'selected' : '' ?>>Finished</option>
                                    <option value="Completed" <?= ($reservation['reservation_status'] == 'Completed' || $reservation['payment_status'] == 'Fully Paid') ? '' : 'disabled' ?> <?= $reservation['reservation_status'] == 'Completed' ? 'selected' : '' ?>>Completed</option>
                                </select>
                                <select name="payment_status">
                                    <option value="Unpaid" <?= $reservation['payment_status'] == 'Unpaid' ? 'selected' : '' ?>>Unpaid</option>
                                    <option value="Fully Paid" <?= $reservation['payment_status'] == 'Fully Paid' ? 'selected' : '' ?>>Fully Paid</option>
                                </select>
                                <button type="submit" name="update_status">Update</button>
                            </div>
                        </td>
                    </form>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <a href="admin_dashboard.php" class="back-link">Back to Dashboard</a>
    </div>
</body>
</html>
