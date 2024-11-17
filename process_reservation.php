<?php
session_start();
include 'db_connect.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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
    $user_id = $_SESSION['user_id']; // Retrieve the user ID from the session

    $conn->begin_transaction();

    try {
        // Deduct service quantity
        $quantity_update_sql = "UPDATE services SET quantity = quantity - 1 WHERE id = ? AND quantity > 0";
        $quantity_stmt = $conn->prepare($quantity_update_sql);
        if ($quantity_stmt) {
            $quantity_stmt->bind_param("i", $service_type);
            if (!$quantity_stmt->execute() || $quantity_stmt->affected_rows === 0) {
                throw new Exception("Service is out of stock or invalid service ID.");
            }
        } else {
            throw new Exception("Error preparing quantity update statement: " . $conn->error);
        }
        $quantity_stmt->close();

        // Insert reservation data
        $sql = "INSERT INTO reservations (user_id, reservation_date, customer_name, vehicle_type, phone, service_type, slot, reservation_time, end_time, price, paid_fee, remaining_fee, reservation_status, payment_status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);

        if ($stmt) {
            $stmt->bind_param("isssssssssddss", $user_id, $reservation_date, $customer_name, $vehicle_type, $phone, $service_type, $slot, $reservation_time, $end_time, $price, $paid_fee, $remaining_fee, $reservation_status, $payment_status);

            if ($stmt->execute()) {
                $reservation_id = $stmt->insert_id;
                
                // Insert into queue
                $queue_status = "Waiting";
                $queue_sql = "INSERT INTO queue (customer_id, customer_type, queue_status, assigned_slot, queue_time, start_time, end_time, queue_date) 
                              VALUES (?, 'Reservation', ?, ?, NOW(), ?, ?, ?)";
                $queue_stmt = $conn->prepare($queue_sql);
                
                if ($queue_stmt) {
                    $queue_stmt->bind_param("isssss", $reservation_id, $queue_status, $slot, $reservation_time, $end_time, $reservation_date);
                    if (!$queue_stmt->execute()) {
                        throw new Exception("Error adding reservation to queue: " . $queue_stmt->error);
                    }
                    $queue_stmt->close();
                } else {
                    throw new Exception("Error preparing queue statement: " . $conn->error);
                }

                // Insert transaction for reservation fee
                $transaction_sql = "INSERT INTO carwash_transactions (customer_id, transaction_type, payment_type, amount, payment_method) 
                                    VALUES (?, 'Reservation', 'Reservation Fee', ?, ?)";
                $transaction_stmt = $conn->prepare($transaction_sql);

                if ($transaction_stmt) {
                    $transaction_stmt->bind_param("ids", $user_id, $paid_fee, $payment_method);
                    if (!$transaction_stmt->execute()) {
                        throw new Exception("Error adding transaction: " . $transaction_stmt->error);
                    }
                    $transaction_stmt->close();
                } else {
                    throw new Exception("Error preparing transaction statement: " . $conn->error);
                }
            } else {
                throw new Exception("Error adding reservation: " . $stmt->error);
            }
            $stmt->close();
        } else {
            throw new Exception("Error preparing reservation statement: " . $conn->error);
        }

        $conn->commit();
        echo "Reservation, Queue, and Transaction successfully added. Quantity updated.";
    } catch (Exception $e) {
        $conn->rollback();
        echo "Transaction Error: " . $e->getMessage();
    }
} else {
    echo "All fields are required!";
}

$conn->close();
