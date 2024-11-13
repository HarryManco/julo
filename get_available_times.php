<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'db_connect.php';
include 'queue_functions.php';

$date = $_GET['date'];
$slot = intval($_GET['slot']);
$service_duration = intval($_GET['duration']);

$available_times = [];
$opening_time = "07:00"; // Store opens at 7:00 AM
$closing_time = "19:00"; // Store closes at 7:00 PM
$current_time = $opening_time;

try {
    // Prepare and execute the query to fetch existing bookings
    $query = "SELECT start_time, end_time FROM queue 
              WHERE assigned_slot = ? AND queue_date = ? AND queue_status IN ('Waiting', 'Serving', 'Finished')";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("is", $slot, $date);
    $stmt->execute();
    $result = $stmt->get_result();
    $bookedTimes = $result->fetch_all(MYSQLI_ASSOC);

    // Helper function to get the next available half-hour or hour after a given time
    function getNextAlignedTime($time) {
        $minutes = (int)date('i', strtotime($time));
        if ($minutes <= 30) {
            return date('H:30', strtotime($time));
        } else {
            return date('H:00', strtotime($time . ' +1 hour'));
        }
    }

    // Process each booked period and adjust the current_time accordingly
    foreach ($bookedTimes as $booking) {
        $booked_start = $booking['start_time'];
        $booked_end = $booking['end_time'];
        
        // While current_time is before the booked start time, add available slots
        while (strtotime($current_time) < strtotime($booked_start) && strtotime($current_time) < strtotime($closing_time)) {
            $available_times[] = $current_time;
            $current_time = date("H:i", strtotime("+30 minutes", strtotime($current_time)));
        }
        
        // Skip over the booked period and set current_time to the next aligned time after booked_end
        if (strtotime($current_time) < strtotime($booked_end)) {
            $current_time = getNextAlignedTime($booked_end);
        }
    }

    // After processing all bookings, continue adding available times until closing time
    while (strtotime($current_time) < strtotime($closing_time)) {
        $available_times[] = $current_time;
        $current_time = date("H:i", strtotime("+30 minutes", strtotime($current_time)));
    }

    // Set Content-Type header for JSON response
    header('Content-Type: application/json');
    echo json_encode($available_times);

} catch (Exception $e) {
    // Log the error and output a JSON error response
    error_log("Error in get_available_times.php: " . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode(["error" => "An error occurred while fetching available times."]);
}

$conn->close();
?>
