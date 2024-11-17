<?php
session_start();
include 'db_connect.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode([]);
    exit();
}

// Get the logged-in user's ID from the session
$customer_id = $_SESSION['user_id'];

// Fetch the updated reservation data
$reservation_query = "
SELECT 
    id AS reservation_id, 
    paid_fee, 
    reservation_status 
FROM reservations 
WHERE customer_name = (SELECT full_name FROM users WHERE id = ?)
ORDER BY reservation_date DESC";

$stmt = $conn->prepare($reservation_query);
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$result = $stmt->get_result();

$reservations = [];
while ($row = $result->fetch_assoc()) {
    $reservations[] = $row;
}

$stmt->close();
$conn->close();

// Return data as JSON
echo json_encode($reservations);
