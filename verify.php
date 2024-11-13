<?php
include 'db_connect.php';

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Check if there's a user with this token
    $query = "SELECT * FROM users WHERE verificationtoken = ? AND emailverified = 0";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // Update the emailverified status
        $updateQuery = "UPDATE users SET emailverified = 1, verificationtoken = NULL WHERE verificationtoken = ?";
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->bind_param("s", $token);

        if ($updateStmt->execute()) {
            header("Location: login.php?status=verified");
        exit();
        } else {
            echo "Error updating verification status.";
        }
    } else {
        echo "Invalid or expired token.";
    }
} else {
    echo "No token provided.";
}
