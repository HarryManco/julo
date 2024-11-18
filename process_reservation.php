<?php
session_start();
include 'db_connect.php';

// Turn off error display for production and log errors
ini_set('log_errors', 1);
ini_set('error_log', '/path/to/php-error.log'); // Adjust the path
ini_set('display_errors', 0);

if (
    !empty($_POST['reservation_date']) &&
    !empty($_POST['customer_name']) &&
    !empty($_POST['vehicle_type']) &&
    !empty($_POST['phone']) &&
    !empty($_POST['service_type']) &&
    !empty($_POST['slot']) &&
    !empty($_POST['reservation_time']) &&
    isset($_POST['price']) &&
    isset($_POST['paid_fee']) &&
    isset($_POST['remaining_fee']) &&
    isset($_POST['end_time'])
) {
    $reservation_date = $_POST['reservation_date'];
    $customer_name = $_POST['customer_name'];
    $vehicle_type = $_POST['vehicle_type'];
    $phone = $_POST['phone'];
    $service_type = $_POST['service_type'];
    $slot = $_POST['slot'];
    $reservation_time = $_POST['reservation_time'];
    $end_time = $_POST['end_time'];
    $price = $_POST['price'];
    $paid_fee = $_POST['paid_fee'];
    $remaining_fee = $_POST['remaining_fee'];
    $reservation_status = "Approved";
    $payment_status = "Unpaid";
    $payment_method = "PayPal"; // Assuming PayPal is used
    $user_id = $_SESSION['user_id'];

    // Start a transaction
    $conn->begin_transaction();

    try {
        // Deduct service quantity
        $quantity_update_sql = "UPDATE services SET quantity = quantity - 1 WHERE id = ? AND quantity > 0";
        $quantity_stmt = $conn->prepare($quantity_update_sql);
        $quantity_stmt->bind_param("i", $service_type);
        if (!$quantity_stmt->execute() || $quantity_stmt->affected_rows === 0) {
            throw new Exception("Service is out of stock or invalid.");
        }
        $quantity_stmt->close();

        // Insert reservation
        $reservation_sql = "INSERT INTO reservations (user_id, reservation_date, customer_name, vehicle_type, phone, service_type, slot, reservation_time, end_time, price, paid_fee, remaining_fee, reservation_status, payment_status) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($reservation_sql);
        $stmt->bind_param("isssssssssddss", $user_id, $reservation_date, $customer_name, $vehicle_type, $phone, $service_type, $slot, $reservation_time, $end_time, $price, $paid_fee, $remaining_fee, $reservation_status, $payment_status);
        if (!$stmt->execute()) {
            throw new Exception("Error inserting reservation: " . $stmt->error);
        }
        $reservation_id = $stmt->insert_id;
        $stmt->close();

        // Insert into queue
        $queue_status = "Waiting";
        $queue_sql = "INSERT INTO queue (user_id, reservation_id, queue_status, slot, queue_time, start_time, end_time, queue_date, notification_status) 
              VALUES (?, ?, ?, ?, NOW(), ?, ?, ?, 'pending')";
        $queue_stmt = $conn->prepare($queue_sql);

        if (!$queue_stmt) {
            throw new Exception("Error preparing queue SQL: " . $conn->error);
        }

        // Bind parameters: user_id (int), reservation_id (int), queue_status (string), slot (int),
        // start_time (string), end_time (string), queue_date (string)
        $queue_stmt->bind_param("iisssss", $user_id, $reservation_id, $queue_status, $slot, $reservation_time, $end_time, $reservation_date);

        if (!$queue_stmt->execute()) {
            throw new Exception("Error inserting into queue: " . $queue_stmt->error);
        }

        $queue_stmt->close();

        // Add transaction
        $transaction_sql = "INSERT INTO carwash_transactions (customer_id, transaction_type, payment_type, amount, payment_method) 
                            VALUES (?, 'Reservation', 'Reservation Fee', ?, ?)";
        $transaction_stmt = $conn->prepare($transaction_sql);
        $transaction_stmt->bind_param("ids", $user_id, $paid_fee, $payment_method);
        if (!$transaction_stmt->execute()) {
            throw new Exception("Error inserting transaction: " . $transaction_stmt->error);
        }
        $transaction_stmt->close();

        // Add customer notification
        $customer_message = "Your reservation for $service_type on $reservation_date has been successfully created.";
        $customer_notification_sql = "INSERT INTO notifications (user_id, message, type) VALUES (?, ?, 'reservation')";
        $stmt = $conn->prepare($customer_notification_sql);
        $stmt->bind_param("is", $user_id, $customer_message);
        if (!$stmt->execute()) {
            throw new Exception("Error inserting customer notification: " . $stmt->error);
        }

        // Add admin notification
        $admin_message = "A new reservation has been made by user ID $user_id for service $service_type.";
        $admin_notification_sql = "INSERT INTO notifications (user_id, message, type) VALUES (1, ?, 'reservation')";
        $stmt = $conn->prepare($admin_notification_sql);
        $stmt->bind_param("s", $admin_message);
        if (!$stmt->execute()) {
            throw new Exception("Error inserting admin notification: " . $stmt->error);
        }

        $conn->commit();
        echo json_encode(["success" => true, "message" => "Reservation successfully added."]);
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Transaction Error: " . $e->getMessage());
        echo json_encode(["success" => false, "message" => $e->getMessage()]);
    }
} else {
    echo json_encode(["success" => false, "message" => "All fields are required!"]);
}

$conn->close();
