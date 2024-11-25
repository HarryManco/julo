<?php
include 'db_connect.php';

$response = ["status" => "error", "message" => ""];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $walk_in_id = $_POST['walk_in_id'] ?? null;
    $status = $_POST['walk_in_status'] ?? null;
    $payment_status = $_POST['payment_status'] ?? null;

    if ($walk_in_id && $status && $payment_status) {
        try {
            // Begin transaction for data consistency
            $conn->begin_transaction();

            // Prevent setting "Completed" status if Payment Status is "Unpaid"
            if ($status === 'Completed' && $payment_status === 'Unpaid') {
                throw new Exception("Cannot mark as Completed when Payment Status is Unpaid.");
            }

            // Update the walk-in table
            $update_query = "UPDATE walk_in SET walk_in_status = ?, payment_status = ? WHERE walk_in_id = ?";
            $stmt = $conn->prepare($update_query);
            if (!$stmt) {
                throw new Exception("Failed to prepare walk-in update query: " . $conn->error);
            }
            $stmt->bind_param("ssi", $status, $payment_status, $walk_in_id);
            $stmt->execute();
            $stmt->close();

            // Update queue status if walk-in status is "Completed"
            if ($status === 'Completed') {
                $queue_update_query = "UPDATE queue SET queue_status = 'Completed' WHERE walk_in_id = ?";
                $queue_stmt = $conn->prepare($queue_update_query);
                if (!$queue_stmt) {
                    throw new Exception("Failed to prepare queue update query: " . $conn->error);
                }
                $queue_stmt->bind_param("i", $walk_in_id);
                $queue_stmt->execute();
                $queue_stmt->close();

                // Add a transaction if Fully Paid
                if ($payment_status === 'Fully Paid') {
                    // Fetch the walk-in details (price and user ID)
                    $fetch_price_query = "SELECT price, user_id FROM walk_in WHERE walk_in_id = ?";
                    $price_stmt = $conn->prepare($fetch_price_query);
                    if (!$price_stmt) {
                        throw new Exception("Failed to prepare price fetch query: " . $conn->error);
                    }
                    $price_stmt->bind_param("i", $walk_in_id);
                    $price_stmt->execute();
                    $price_result = $price_stmt->get_result();
                    $price_data = $price_result->fetch_assoc();
                    $amount = $price_data['price'];
                    $user_id = $price_data['user_id']; // Fetch user_id for tracking
                    $price_stmt->close();

                    // Insert the transaction into carwash_transactions table
                    $transaction_query = "INSERT INTO carwash_transactions (customer_id, transaction_type, payment_type, amount, payment_method) 
                                          VALUES (?, 'Walk-in', 'Full Payment', ?, 'Cash')";
                    $transaction_stmt = $conn->prepare($transaction_query);
                    if (!$transaction_stmt) {
                        throw new Exception("Failed to prepare transaction insert query: " . $conn->error);
                    }
                    $transaction_stmt->bind_param("id", $walk_in_id, $amount);
                    $transaction_stmt->execute();
                    $transaction_stmt->close();

                    // Add a notification for the customer
                    $notification_message = "Your walk-in service has been completed. The full payment of ₱" . number_format($amount, 2) . " has been successfully received. Thank you!";
                    $notification_query = "INSERT INTO notifications (user_id, message, type) VALUES (?, ?, 'walk_in_update')";
                    $notification_stmt = $conn->prepare($notification_query);
                    if (!$notification_stmt) {
                        throw new Exception("Failed to prepare notification insert query: " . $conn->error);
                    }
                    $notification_stmt->bind_param("is", $user_id, $notification_message);
                    $notification_stmt->execute();
                    $notification_stmt->close();
                }
            }

            // Commit the transaction
            $conn->commit();

            $response = ["status" => "success", "message" => "Walk-in and queue status updated successfully!"];
        } catch (Exception $e) {
            // Rollback the transaction on error
            $conn->rollback();
            $response = ["status" => "error", "message" => $e->getMessage()];
        }
    } else {
        $response = ["status" => "error", "message" => "Invalid data provided."];
    }
}

// Return the JSON response
echo json_encode($response);
$conn->close();
