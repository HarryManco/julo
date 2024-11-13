<?php
include 'db_connect.php';

$response = ["status" => "error", "message" => ""];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $walk_in_id = $_POST['walk_in_id'] ?? null;
    $status = $_POST['walk_in_status'] ?? null;
    $payment_status = $_POST['payment_status'] ?? null;

    if ($walk_in_id && $status && $payment_status) {
        try {
            // Begin transaction
            $conn->begin_transaction();

            // Prevent setting "Completed" status if Payment Status is "Unpaid"
            if ($status === 'Completed' && $payment_status === 'Unpaid') {
                throw new Exception("Cannot mark as Completed when Payment Status is Unpaid.");
            }

            // Update walk-in table
            $update_query = "UPDATE walk_in SET walk_in_status = ?, payment_status = ? WHERE walk_in_id = ?";
            $stmt = $conn->prepare($update_query);
            $stmt->bind_param("ssi", $status, $payment_status, $walk_in_id);
            $stmt->execute();
            $stmt->close();

            // Update queue table if status is "Completed"
            if ($status === 'Completed') {
                $queue_update_query = "UPDATE queue SET queue_status = 'Completed' WHERE customer_id = ? AND customer_type = 'Walk-in'";
                $queue_stmt = $conn->prepare($queue_update_query);
                $queue_stmt->bind_param("i", $walk_in_id);
                $queue_stmt->execute();
                $queue_stmt->close();

                // Add transaction if Fully Paid
                if ($payment_status === 'Fully Paid') {
                    $fetch_price_query = "SELECT price FROM walk_in WHERE walk_in_id = ?";
                    $price_stmt = $conn->prepare($fetch_price_query);
                    $price_stmt->bind_param("i", $walk_in_id);
                    $price_stmt->execute();
                    $price_result = $price_stmt->get_result();
                    $price_data = $price_result->fetch_assoc();
                    $amount = $price_data['price'];
                    $price_stmt->close();

                    $transaction_query = "INSERT INTO carwash_transactions (customer_id, transaction_type, payment_type, amount, payment_method) 
                                          VALUES (?, 'Walk-in', 'Full Payment', ?, 'Cash')";
                    $transaction_stmt = $conn->prepare($transaction_query);
                    $transaction_stmt->bind_param("id", $walk_in_id, $amount);
                    $transaction_stmt->execute();
                    $transaction_stmt->close();
                }
            }

            // Commit transaction
            $conn->commit();

            $response = ["status" => "success", "message" => "Update successful!"];
        } catch (Exception $e) {
            $conn->rollback();
            $response = ["status" => "error", "message" => $e->getMessage()];
        }
    } else {
        $response = ["status" => "error", "message" => "Invalid data provided."];
    }
}

echo json_encode($response);
$conn->close();
