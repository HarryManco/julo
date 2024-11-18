<?php
session_start();
include 'db_connect.php';

// Get user ID from session
$user_id = $_SESSION['user_id'];

// Fetch all notifications for the user, ordered by creation time (most recent first)
$query = "SELECT id, message, status FROM notifications WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Prepare notifications array
$notifications = [];
while ($row = $result->fetch_assoc()) {
    $notifications[] = $row;
}

// Check if no notifications are found
if (empty($notifications)) {
    echo json_encode(["notifications" => [], "message" => "No notifications available."]);
    exit();
}

// Return notifications with a success response
echo json_encode(["notifications" => $notifications]);
?>
