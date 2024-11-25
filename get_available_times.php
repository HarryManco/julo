<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'db_connect.php';
include 'queue_functions.php';

// Get parameters from the GET request
$date = $_GET['date'] ?? null;
$slot = isset($_GET['slot']) ? intval($_GET['slot']) : null;
$service_duration = isset($_GET['duration']) ? intval($_GET['duration']) : 0;

if (!$date || !$slot || !$service_duration) {
    header('Content-Type: application/json');
    echo json_encode(["error" => "Invalid parameters."]);
    exit;
}

$available_times = [];
$opening_time = "07:00"; // Store opens at 7:00 AM
$closing_time = "19:00"; // Store closes at 7:00 PM
$current_time = $opening_time;

// Get the current date and time
date_default_timezone_set('Asia/Manila');
$currentDate = date("Y-m-d");
$currentTime = date("H:i");

try {
    // Fetch existing bookings for the selected slot and date
    $query = "SELECT start_time, end_time FROM queue 
              WHERE slot = ? AND queue_date = ? AND queue_status IN ('Waiting', 'Serving', 'Finished')";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("is", $slot, $date);
    $stmt->execute();
    $result = $stmt->get_result();
    $bookedTimes = $result->fetch_all(MYSQLI_ASSOC);

    // Helper function to calculate aligned time for intervals
    function getNextAlignedTime($time, $interval = 30) {
        $minutes = (int)date('i', strtotime($time));
        if ($minutes < $interval) {
            return date('H:' . str_pad($interval, 2, '0', STR_PAD_LEFT), strtotime($time));
        } else {
            return date('H:00', strtotime($time . ' +1 hour'));
        }
    }

    // Iterate and calculate available times
    foreach ($bookedTimes as $booking) {
        $booked_start = $booking['start_time'];
        $booked_end = $booking['end_time'];

        // Add times before booked start
        while (strtotime($current_time) < strtotime($booked_start) && strtotime($current_time) < strtotime($closing_time)) {
            $slot_end_time = date("H:i", strtotime("+$service_duration minutes", strtotime($current_time)));

            // Ensure the slot ends before the next booking starts or closing time
            if (strtotime($slot_end_time) <= strtotime($booked_start) &&
                ($date !== $currentDate || strtotime($current_time) >= strtotime($currentTime))) {
                $available_times[] = $current_time;
            }

            $current_time = date("H:i", strtotime("+30 minutes", strtotime($current_time)));
        }

        // Skip over the booked period
        if (strtotime($current_time) < strtotime($booked_end)) {
            $current_time = getNextAlignedTime($booked_end);
        }
    }

    // Add times after the last booking
    while (strtotime($current_time) < strtotime($closing_time)) {
        $slot_end_time = date("H:i", strtotime("+$service_duration minutes", strtotime($current_time)));

        // Ensure the slot ends before closing time and does not fall in the past
        if (
            strtotime($slot_end_time) <= strtotime($closing_time) &&
            ($date !== $currentDate || strtotime($current_time) >= strtotime($currentTime))
        ) {
            $available_times[] = $current_time;
        }

        $current_time = date("H:i", strtotime("+30 minutes", strtotime($current_time)));
    }

    // Return JSON response
    header('Content-Type: application/json');
    echo json_encode($available_times);

} catch (Exception $e) {
    // Log error and return JSON error response
    error_log("Error in get_available_times.php: " . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode(["error" => "An error occurred while fetching available times."]);
}

$conn->close();
?>
