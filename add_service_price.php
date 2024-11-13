<?php
include 'db_connect.php';

$response = [];

// Check if all required fields are set
if (isset($_POST['service_id'], $_POST['car_model'], $_POST['price'], $_POST['duration'])) {
    $service_id = $_POST['service_id'];
    $car_model = $_POST['car_model'];
    $price = $_POST['price'];
    $duration = $_POST['duration'];

    // Validate fields
    if ($service_id != '0' && !empty($car_model) && !empty($price) && !empty($duration)) {
        // Prepare and execute the insert query
        $insert_query = "INSERT INTO service_prices (service_id, car_model, price, duration, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())";
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param("isdi", $service_id, $car_model, $price, $duration);

        if ($stmt->execute()) {
            // Fetch the service name for the response
            $service_name_query = "SELECT service_name FROM services WHERE id = ?";
            $service_stmt = $conn->prepare($service_name_query);
            $service_stmt->bind_param("i", $service_id);
            $service_stmt->execute();
            $service_result = $service_stmt->get_result();
            $service = $service_result->fetch_assoc();

            // Prepare response data
            $response = [
                "status" => "success",
                "message" => "Service price added successfully!",
                "data" => [
                    "id" => $conn->insert_id,
                    "service_name" => $service['service_name'],
                    "car_model" => $car_model,
                    "price" => $price,
                    "duration" => $duration
                ]
            ];
        } else {
            $response = ["status" => "error", "message" => "Failed to add service price."];
        }

        $stmt->close();
    } else {
        $response = ["status" => "error", "message" => "All fields are required, and a valid service must be selected!"];
    }
} else {
    $response = ["status" => "error", "message" => "Invalid data received."];
}

$conn->close();
echo json_encode($response);
