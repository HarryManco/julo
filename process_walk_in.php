<?php
session_start();
include 'db_connect.php';

// Enable detailed error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$log_file = 'walkin_debug.log';
file_put_contents($log_file, "Starting walk-in process...\n", FILE_APPEND);

try {
    // Collect POST data
    $customer_name = $_POST['customer_name'] ?? '';
    $vehicle_type = $_POST['vehicle_type'] ?? '';
    $service_type = $_POST['service_type'] ?? '';
    $slot = $_POST['slot'] ?? '';
    $price = $_POST['price'] ?? '';
    $duration = $_POST['duration'] ?? '';
    $today_date = date("Y-m-d");

    // Get the logged-in admin's user_id from session
    $user_id = $_SESSION['user_id'] ?? null;

    file_put_contents($log_file, "POST data: " . print_r($_POST, true) . "\n", FILE_APPEND);

    // Validate required fields
    if (!$customer_name || !$vehicle_type || !$service_type || !$slot || !$price || !$duration || !$user_id) {
        throw new Exception("Missing required fields.");
    }

    // Start database transaction
    $conn->begin_transaction();
    file_put_contents($log_file, "Transaction started\n", FILE_APPEND);

    // Insert the walk-in record
    $insert_query = "
        INSERT INTO walk_in (customer_name, vehicle_type, service_type, slot, price, payment_status, walk_in_status, duration, created_at, user_id) 
        VALUES (?, ?, ?, ?, ?, 'Unpaid', 'Waiting', ?, NOW(), ?)
    ";
    $stmt = $conn->prepare($insert_query);

    if (!$stmt) {
        throw new Exception("Failed to prepare walk-in insert query: " . $conn->error);
    }

    $stmt->bind_param("ssiisdi", $customer_name, $vehicle_type, $service_type, $slot, $price, $duration, $user_id);
    if (!$stmt->execute()) {
        throw new Exception("Failed to execute walk-in insert query: " . $stmt->error);
    }

    $walk_in_id = $stmt->insert_id; // Get the last inserted ID for the walk-in record
    file_put_contents($log_file, "Walk-in record inserted with ID: $walk_in_id\n", FILE_APPEND);
    $stmt->close();

    // Get the next available time for the slot
    $available_time = getAvailableTime($slot, $today_date, $duration, $conn);
    if ($available_time === false) {
        throw new Exception("No available time within business hours.");
    }

    // Calculate end time
    $end_time = date("H:i:s", strtotime($available_time) + $duration * 60);
    file_put_contents($log_file, "Available time: $available_time, End time: $end_time\n", FILE_APPEND);

    // Insert into the queue
    $queue_query = "
        INSERT INTO queue (walk_in_id, user_id, queue_status, slot, queue_time, queue_date, start_time, end_time, notification_status) 
        VALUES (?, ?, 'Waiting', ?, NOW(), ?, ?, ?, 'pending')
    ";
    $queue_stmt = $conn->prepare($queue_query);

    if (!$queue_stmt) {
        throw new Exception("Failed to prepare queue insert query: " . $conn->error);
    }

    $queue_stmt->bind_param("iiisss", $walk_in_id, $user_id, $slot, $today_date, $available_time, $end_time);
    if (!$queue_stmt->execute()) {
        throw new Exception("Failed to execute queue insert query: " . $queue_stmt->error);
    }

    file_put_contents($log_file, "Queue entry added successfully\n", FILE_APPEND);
    $queue_stmt->close();

    // Commit the transaction
    $conn->commit();
    file_put_contents($log_file, "Transaction committed\n", FILE_APPEND);

    // Return a success response
    echo json_encode(["status" => "success", "message" => "Walk-in and queue entry added successfully!"]);
} catch (Exception $e) {
    // Roll back the transaction in case of an error
    $conn->rollback();
    $error_message = "An error occurred: " . $e->getMessage();
    file_put_contents($log_file, $error_message . "\n", FILE_APPEND);

    // Return an error response
    echo json_encode(["status" => "error", "message" => $error_message]);
}

$conn->close();

// Helper function to find the next available time for a slot
function getAvailableTime($slot, $date, $duration, $conn) {
    $opening_time = "07:00:00"; // Opening time of the carwash
    $closing_time = "19:00:00"; // Closing time of the carwash

    // Query to get the last end time for the slot on the given date
    $query = "
        SELECT end_time 
        FROM queue 
        WHERE slot = ? AND queue_date = ? 
        ORDER BY end_time DESC 
        LIMIT 1
    ";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("is", $slot, $date);
    $stmt->execute();
    $result = $stmt->get_result();
    $last_entry = $result->fetch_assoc();
    $stmt->close();

    $last_end_time = $last_entry ? $last_entry['end_time'] : $opening_time; // Default to opening time if no entry exists
    $next_start_time = date("H:i:s", strtotime($last_end_time)); // Calculate the next start time

    // Check if the next start time plus the duration exceeds the closing time
    if (strtotime($next_start_time) + $duration * 60 > strtotime($closing_time)) {
        return false; // No available time
    }

    return $next_start_time; // Return the next available time
}
?>
