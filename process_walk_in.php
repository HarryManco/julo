<?php
session_start();
include 'db_connect.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$log_file = 'walkin_debug.log';
file_put_contents($log_file, "Starting walk-in process...\n", FILE_APPEND);

try {
    // Collect and log POST data
    $customer_name = $_POST['customer_name'] ?? '';
    $vehicle_id = $_POST['vehicle_type'] ?? '';
    $service_id = $_POST['service_type'] ?? '';
    $slot = $_POST['slot'] ?? '';
    $price = $_POST['price'] ?? '';
    $duration = $_POST['duration'] ?? '';
    $today_date = date("Y-m-d");

    file_put_contents($log_file, "POST data: " . print_r($_POST, true) . "\n", FILE_APPEND);

    // Validate required fields
    if (!$customer_name || !$vehicle_id || !$service_id || !$slot || !$price || !$duration) {
        throw new Exception("Missing required fields.");
    }

    // Start transaction
    $conn->begin_transaction();
    file_put_contents($log_file, "Transaction started\n", FILE_APPEND);

    // Insert walk-in record
    $insert_query = "INSERT INTO walk_in (customer_name, vehicle_type, service_type, slot, price, payment_status, walk_in_status, duration, created_at) 
                     VALUES (?, ?, ?, ?, ?, 'Unpaid', 'Waiting', ?, NOW())";
    $stmt = $conn->prepare($insert_query);
    
    if (!$stmt) {
        throw new Exception("Failed to prepare walk-in insert query: " . $conn->error);
    }

    $stmt->bind_param("siisdi", $customer_name, $vehicle_id, $service_id, $slot, $price, $duration);
    if (!$stmt->execute()) {
        throw new Exception("Failed to execute walk-in insert query: " . $stmt->error);
    }
    $walk_in_id = $stmt->insert_id;
    file_put_contents($log_file, "Walk-in record inserted with ID: $walk_in_id\n", FILE_APPEND);
    $stmt->close();

    // Get the next available time
    $available_time = getAvailableTime($slot, $today_date, $duration, $conn);
    if ($available_time === false) {
        throw new Exception("No available time within business hours.");
    }

    // Calculate end time based on duration
    $end_time = date("H:i:s", strtotime($available_time) + $duration * 60);
    file_put_contents($log_file, "Available time: $available_time, End time: $end_time\n", FILE_APPEND);

    // Insert into queue
    $queue_query = "INSERT INTO queue (customer_id, customer_type, queue_status, assigned_slot, queue_time, queue_date, start_time, end_time) 
                    VALUES (?, 'Walk-in', 'Waiting', ?, NOW(), ?, ?, ?)";
    $queue_stmt = $conn->prepare($queue_query);
    $queue_stmt->bind_param("issss", $walk_in_id, $slot, $today_date, $available_time, $end_time);
    
    if (!$queue_stmt->execute()) {
        throw new Exception("Failed to execute queue insert query: " . $queue_stmt->error);
    }

    file_put_contents($log_file, "Queue entry added successfully\n", FILE_APPEND);
    $queue_stmt->close();

    $conn->commit();
    file_put_contents($log_file, "Transaction committed\n", FILE_APPEND);

    echo json_encode(["status" => "success", "message" => "Walk-in and queue entry added successfully!"]);
} catch (Exception $e) {
    $conn->rollback();
    $error_message = "An error occurred: " . $e->getMessage();
    file_put_contents($log_file, $error_message . "\n", FILE_APPEND);
    echo json_encode(["status" => "error", "message" => $error_message]);
}

$conn->close();

// Helper function for available time
function getAvailableTime($slot, $date, $duration, $conn) {
    $opening_time = "07:00:00";
    $closing_time = "19:00:00";

    $query = "SELECT end_time FROM queue WHERE assigned_slot = ? AND queue_date = ? ORDER BY end_time DESC LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("is", $slot, $date);
    $stmt->execute();
    $result = $stmt->get_result();
    $last_entry = $result->fetch_assoc();
    $stmt->close();

    $last_end_time = $last_entry ? $last_entry['end_time'] : $opening_time;
    $next_start_time = date("H:i:s", strtotime($last_end_time) + 5 * 60);

    if (strtotime($next_start_time) + $duration * 60 > strtotime($closing_time)) {
        return false;
    }
    return $next_start_time;
}
?>
