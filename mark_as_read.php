<?php
session_start();
include 'db_connect.php'; // Include your database connection file

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['notification_id'])) {
    $notification_id = intval($_POST['notification_id']);

    if ($notification_id > 0) {
        // Update notification status to 'read'
        $query = "UPDATE notifications SET status = 'read' WHERE id = ?";
        $stmt = $conn->prepare($query);

        if ($stmt) {
            $stmt->bind_param("i", $notification_id);

            if ($stmt->execute()) {
                $_SESSION['message'] = "Notification marked as read successfully.";
            } else {
                $_SESSION['error'] = "Failed to mark the notification as read. Please try again.";
            }

            $stmt->close();
        } else {
            $_SESSION['error'] = "Failed to prepare the query: " . $conn->error;
        }
    } else {
        $_SESSION['error'] = "Invalid notification ID.";
    }

    $conn->close();
} else {
    $_SESSION['error'] = "Invalid request.";
}

// Redirect back to the notifications page
header("Location: admin_notifications.php");
exit;
?>
