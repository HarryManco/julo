<?php
include 'db_connect.php';

// Set timezone
date_default_timezone_set('Asia/Manila');
$current_date = date('Y-m-d');

// Handle POST requests for updating queue status
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if it's an AJAX request
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        header('Content-Type: application/json'); // Set header for JSON response

        $response = ["success" => false, "message" => "Invalid request."];

        if (isset($_POST['queue_id']) && isset($_POST['queue_status'])) {
            $queue_id = $_POST['queue_id'];
            $queue_status = $_POST['queue_status'];

            // Update queue status
            $update_query = "UPDATE queue SET queue_status = ?, notification_status = 'pending' WHERE queue_id = ?";
            $stmt = $conn->prepare($update_query);
            $stmt->bind_param("si", $queue_status, $queue_id);

            if ($stmt->execute()) {
                $response["success"] = true;
                $response["message"] = "Queue status updated successfully.";
            
                // Fetch related data for updating reservation or walk-in status
                $fetch_query = "SELECT q.reservation_id, q.walk_in_id, q.user_id, r.remaining_fee, w.customer_name 
                                FROM queue q 
                                LEFT JOIN reservations r ON q.reservation_id = r.reservation_id 
                                LEFT JOIN walk_in w ON q.walk_in_id = w.walk_in_id 
                                WHERE q.queue_id = ?";
                $fetch_stmt = $conn->prepare($fetch_query);
                $fetch_stmt->bind_param("i", $queue_id);
                $fetch_stmt->execute();
                $result = $fetch_stmt->get_result();
            
                if ($result->num_rows > 0) {
                    $queue_data = $result->fetch_assoc();
                    $reservation_id = $queue_data['reservation_id'];
                    $walk_in_id = $queue_data['walk_in_id'];
                    $user_id = $queue_data['user_id'];
                    $remaining_fee = $queue_data['remaining_fee'];
                    $customer_name = $queue_data['customer_name'];
            
                    // Update reservation status if it's linked to this queue
                    if (!empty($reservation_id)) {
                        $reservation_status = null;
                        if ($queue_status === 'Serving') {
                            $reservation_status = 'Serving';
                        } elseif ($queue_status === 'Finished') {
                            $reservation_status = 'Finished';
                        }
            
                        if ($reservation_status !== null) {
                            $update_reservation_query = "UPDATE reservations SET reservation_status = ? WHERE reservation_id = ?";
                            $update_reservation_stmt = $conn->prepare($update_reservation_query);
                            $update_reservation_stmt->bind_param("si", $reservation_status, $reservation_id);
                            $update_reservation_stmt->execute();
                            $update_reservation_stmt->close();
                        }
                    }
            
                    // Add notifications for different statuses
                    $notification_message = "";
                    if ($queue_status === 'Serving') {
                        $notification_message = "Your vehicle is now being served.";
                    } elseif ($queue_status === 'Finished') {
                        $notification_message = "Your vehicle service has been finished. Please pay the remaining fee of ₱" . number_format($remaining_fee, 2) . " at the counter.";
                    }
            
                    if (!empty($notification_message)) {
                        $notification_sql = "INSERT INTO notifications (user_id, message, type) VALUES (?, ?, 'queue_update')";
                        $notification_stmt = $conn->prepare($notification_sql);
                        $notification_stmt->bind_param("is", $user_id, $notification_message);
                        $notification_stmt->execute();
                    }
            
                    // Add admin notification if status is "Finished"
                    if ($queue_status === 'Finished') {
                        $admin_message = "Queue ID $queue_id for customer $customer_name has been marked as Finished. Please process the remaining fees.";
                        $admin_notification_sql = "INSERT INTO notifications (user_id, message, type) VALUES (NULL, ?, 'queue_update')";
                        $admin_notification_stmt = $conn->prepare($admin_notification_sql);
                        $admin_notification_stmt->bind_param("s", $admin_message);
                        $admin_notification_stmt->execute();
                    }
                }
            }
             else {
                $response["message"] = "Failed to update queue status.";
            }
        }

        echo json_encode($response);
        exit;
    }
}

// Handle GET requests for fetching queue data
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    // Check if it's an AJAX request
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        header('Content-Type: application/json'); // Set header for JSON response

        $query = "
            SELECT 
                q.queue_id,
                q.queue_status,
                q.slot,
                CASE 
                    WHEN q.reservation_id IS NOT NULL THEN r.customer_name
                    WHEN q.walk_in_id IS NOT NULL THEN w.customer_name
                    ELSE 'Unknown'
                END AS customer_name
            FROM queue q
            LEFT JOIN reservations r ON q.reservation_id = r.reservation_id
            LEFT JOIN walk_in w ON q.walk_in_id = w.walk_in_id
            WHERE q.queue_date = ?
        ";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $current_date);
        $stmt->execute();
        $result = $stmt->get_result();

        $queue_data = [];
        while ($row = $result->fetch_assoc()) {
            $queue_data[] = $row;
        }

        echo json_encode($queue_data);
        exit;
    }
}
?>

<?php include 'sidebar.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Queue</title>
    <link rel="stylesheet" href="css/queue_manage.css">
</head>
<body>
<div id="notification" class="notification success" style="display: none;"></div>
<div class="container">
    <h2>Manage Queue - <?php echo date('F j, Y'); ?></h2>
    <table>
        <thead>
        <tr>
            <th>Queue ID</th>
            <th>Customer</th>
            <th>Status</th>
            <th>Slot</th>
            <th>Action</th>
        </tr>
        </thead>
        <tbody id="queueTable">
        </tbody>
    </table>
</div>
<script src="js/queue_manage.js"></script>
</body>
</html>
