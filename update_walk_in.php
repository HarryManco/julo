<?php
include 'db_connect.php';

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $walk_in_id = $_POST['walk_in_id'];
    $status = $_POST['walk_in_status'];
    $payment_status = $_POST['payment_status'];

    $response = ["status" => "error", "message" => ""];

    // Prevent setting to Completed if Payment Status is Unpaid
    if ($status == 'Completed' && $payment_status == 'Unpaid') {
        $response["message"] = "Cannot mark as Completed when Payment Status is Unpaid.";
        echo json_encode($response);
        exit;
    }

    try {
        $conn->begin_transaction();

        // Update walk-in record
        $update_query = "UPDATE walk_in SET walk_in_status = ?, payment_status = ? WHERE walk_in_id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("ssi", $status, $payment_status, $walk_in_id);
        $stmt->execute();
        $stmt->close();

        // Update queue status if status is Completed
        if ($status == 'Completed') {
            $queue_update_query = "UPDATE queue SET queue_status = 'Completed' WHERE customer_id = ? AND customer_type = 'Walk-in'";
            $queue_stmt = $conn->prepare($queue_update_query);
            $queue_stmt->bind_param("i", $walk_in_id);
            $queue_stmt->execute();
            $queue_stmt->close();
        }

        $conn->commit();
        $response["status"] = "success";
        $response["message"] = "Update successful!";
    } catch (Exception $e) {
        $conn->rollback();
        $response["message"] = "An error occurred: " . $e->getMessage();
    }
    
    echo json_encode($response);
}
