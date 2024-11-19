<?php
include 'db_connect.php'; // Include your database connection

// Set timezone and current date
date_default_timezone_set('Asia/Manila');
$current_date = date('Y-m-d');
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
    <ul class="notifications-list" id="notifications-list"></ul>
    <p id="no-notifications" style="display: none;">No notifications for today.</p>
</div>
<script src="js/admin_notifications.js"></script>
</body>
</html>
