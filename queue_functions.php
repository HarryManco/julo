<?php
include 'db_connect.php';

function checkSlotAvailability($slot, $date, $start_time, $duration) {
    global $conn;

    $end_time = date("H:i:s", strtotime("+$duration minutes", strtotime($start_time)));

    $query = "SELECT * FROM queue 
              WHERE slot = ? 
              AND queue_date = ?
              AND (
                  (start_time <= ? AND end_time > ?) OR 
                  (start_time < ? AND end_time >= ?)
              )";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("isssss", $slot, $date, $end_time, $start_time, $start_time, $end_time);
    $stmt->execute();
    $result = $stmt->get_result();

    return $result->num_rows == 0; // true if available, false if occupied
}
?>
