<?php
session_start();
include 'db_connect.php';

// Get user ID from session
$user_id = $_SESSION['user_id'];

// Fetch unread notifications
$query = "SELECT id, message FROM notifications WHERE user_id = ? AND status = 'unread' ORDER BY created_at DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$notifications = [];
while ($row = $result->fetch_assoc()) {
    $notifications[] = $row;
}

echo json_encode($notifications);
?>
