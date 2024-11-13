<?php
include 'db_connect.php';
include 'queue_functions.php';

$date = $_GET['date'];
$slot = $_GET['slot'];
$service_duration = intval($_GET['duration']); // Duration in minutes

$available_times = [];
$opening_time = "07:00"; // Store opens at 7:00 AM
$closing_time = "19:00"; // Store closes at 7:00 PM
$current_time = $opening_time;

while (strtotime($current_time) < strtotime($closing_time)) {
    // Check if the current time slot is available
    if (checkSlotAvailability($slot, $date, $current_time, $service_duration)) {
        $available_times[] = $current_time;
    }

    // Move to the next time slot (increment by 30 minutes)
    $current_time = date("H:i", strtotime("+30 minutes", strtotime($current_time)));
}

// Return the available times as a JSON response
echo json_encode($available_times);
?>
