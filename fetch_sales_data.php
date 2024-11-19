<?php
include 'db_connect.php';

$time_period = $_GET['period']; // daily, weekly, monthly, or yearly

switch ($time_period) {
    case 'daily':
        $query = "SELECT DATE(created_at) as period, SUM(amount) as total_sales 
                  FROM carwash_transactions 
                  WHERE DATE(created_at) = CURDATE() 
                  GROUP BY DATE(created_at)";
        break;

    case 'weekly':
        $query = "SELECT WEEK(created_at) as period, SUM(amount) as total_sales 
                  FROM carwash_transactions 
                  WHERE YEARWEEK(created_at) = YEARWEEK(CURDATE()) 
                  GROUP BY WEEK(created_at)";
        break;

    case 'monthly':
        $query = "SELECT MONTH(created_at) as period, SUM(amount) as total_sales 
                  FROM carwash_transactions 
                  WHERE MONTH(created_at) = MONTH(CURDATE()) 
                  GROUP BY MONTH(created_at)";
        break;

    case 'yearly':
        $query = "SELECT YEAR(created_at) as period, SUM(amount) as total_sales 
                  FROM carwash_transactions 
                  WHERE YEAR(created_at) = YEAR(CURDATE()) 
                  GROUP BY YEAR(created_at)";
        break;

    default:
        echo json_encode(["error" => "Invalid time period."]);
        exit;
}

$result = $conn->query($query);
$data = [];

while ($row = $result->fetch_assoc()) {
    $data[] = [
        'period' => $row['period'],
        'total_sales' => (float) $row['total_sales']
    ];
}

header('Content-Type: application/json');
echo json_encode($data);
?>
