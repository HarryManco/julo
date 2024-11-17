<?php
session_start();
include 'db_connect.php';

$user_id = $_SESSION['user_id'];

$sql = "UPDATE notifications SET status = 'read' WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();

echo json_encode(["success" => true]);
?>
