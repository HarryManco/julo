<?php
include 'db_connect.php'; // Include your database connection

// Set timezone and current date
date_default_timezone_set('Asia/Manila');
$current_date = date('Y-m-d');

// Fetch admin-specific notifications for the current date
$query = "SELECT id, message, type, status, created_at 
          FROM notifications 
          WHERE user_id IS NULL AND DATE(created_at) = ? 
          ORDER BY created_at DESC";

$stmt = $conn->prepare($query);

// Check if the query was prepared successfully
if (!$stmt) {
    die("Query preparation failed: " . $conn->error);
}

$stmt->bind_param("s", $current_date);
$stmt->execute();
$result = $stmt->get_result();

$notifications = [];
while ($row = $result->fetch_assoc()) {
    $notifications[] = $row;
}
$stmt->close();
$conn->close();
?>
<?php include 'sidebar.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Notifications</title>
    <link rel="stylesheet" href="css/notifications.css">
</head>
<body>
<div class="container">
    <h1>Notifications for <?php echo date('F j, Y'); ?></h1>
    <?php if (!empty($notifications)): ?>
        <ul class="notifications-list">
            <?php foreach ($notifications as $notification): ?>
                <li class="notification <?php echo $notification['status'] === 'unread' ? 'unread' : 'read'; ?>">
                    <p><?php echo htmlspecialchars($notification['message']); ?></p>
                    <span class="type"><?php echo ucfirst($notification['type']); ?></span>
                    <span class="timestamp"><?php echo date('h:i A', strtotime($notification['created_at'])); ?></span>
                    <?php if ($notification['status'] === 'unread'): ?>
                        <form method="POST" class="mark-as-read-form" action="mark_as_read.php">
                            <input type="hidden" name="notification_id" value="<?php echo $notification['id']; ?>">
                            <button type="submit" class="mark-as-read-btn">Mark as Read</button>
                        </form>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>No notifications for today.</p>
    <?php endif; ?>
</div>
</body>
</html>
