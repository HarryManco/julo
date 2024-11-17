<?php
include 'db_connect.php';

date_default_timezone_set('Asia/Manila');
$current_date = date('Y-m-d');

$query = "SELECT * FROM queue WHERE queue_date = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $current_date);
$stmt->execute();
$result = $stmt->get_result();

$queue_data = [];
while ($row = $result->fetch_assoc()) {
    $queue_data[] = $row;
}

echo json_encode($queue_data);
?>
