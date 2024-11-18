<?php
include 'db_connect.php';

$response = ["status" => "error", "queues" => []];
date_default_timezone_set('Asia/Manila');
$current_date = date("Y-m-d");

$query = "SELECT q.slot, w.customer_name, q.start_time, q.end_time 
          FROM queue q 
          LEFT JOIN walk_in w ON q.walk_in_id = w.walk_in_id 
          WHERE q.queue_date = ?
          ORDER BY q.slot, q.start_time";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $current_date);
$stmt->execute();
$result = $stmt->get_result();

$queues = [];
while ($row = $result->fetch_assoc()) {
    $slot = $row['slot'];
    if (!isset($queues[$slot])) {
        $queues[$slot] = [];
    }
    $queues[$slot][] = [
        "customer_name" => $row['customer_name'],
        "start_time" => $row['start_time'],
        "end_time" => $row['end_time']
    ];
}

$stmt->close();
$conn->close();

$response["status"] = "success";
$response["queues"] = $queues;

echo json_encode($response);
