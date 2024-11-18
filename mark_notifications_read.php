<?php
session_start();
include 'db_connect.php';

$user_id = $_SESSION['user_id'];

$sql = "UPDATE notifications SET status = 'read' WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);

// Execute the query and provide feedback
if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Notifications marked as read."]);
} else {
    echo json_encode(["success" => false, "error" => $stmt->error]);
}

$stmt->close();
$conn->close();
?>
