<?php
session_start();
require_once 'db_connect.php';

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['id']) && isset($data['quantity']) && is_numeric($data['quantity']) && $data['quantity'] > 0) {
    $id = $data['id'];
    $quantity = intval($data['quantity']); // Ensure quantity is an integer

    // Update the quantity in the cart_orders table
    $query = "UPDATE cart_orders SET quantity = ? WHERE id = ?";
    $stmt = $conn->prepare($query);

    if ($stmt) {
        $stmt->bind_param("ii", $quantity, $id);
        $success = $stmt->execute();
        $stmt->close();
        
        if ($success) {
            echo json_encode(['status' => 'success', 'message' => 'Quantity updated successfully.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Database update failed.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to prepare SQL statement.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid data provided.']);
}
?>
