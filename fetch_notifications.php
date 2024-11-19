<?php
session_start();
include 'db_connect.php';

// Check if the request is for admin notifications
if (isset($_GET['admin']) && $_GET['admin'] == 'true') {
    // Fetch admin-specific notifications
    $query = "SELECT id, message, status, created_at 
              FROM notifications 
              WHERE user_id IS NULL 
              ORDER BY created_at DESC";
    $stmt = $conn->prepare($query);

    if (!$stmt) {
        die(json_encode(["error" => "Failed to prepare query: " . $conn->error]));
    }

    $stmt->execute();
    $result = $stmt->get_result();

    $notifications = [];
    while ($row = $result->fetch_assoc()) {
        $notifications[] = $row;
    }

    echo json_encode(["notifications" => $notifications]);
    $stmt->close();
    $conn->close();
    exit();
}

// For user notifications
if (!isset($_SESSION['user_id'])) {
    die(json_encode(["error" => "Unauthorized"]));
}

$user_id = $_SESSION['user_id'];

// Fetch all notifications for the user
$query = "SELECT id, message, status FROM notifications WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$notifications = [];
while ($row = $result->fetch_assoc()) {
    $notifications[] = $row;
}

// Return user notifications
echo json_encode(["notifications" => $notifications]);
$stmt->close();
$conn->close();
?>
