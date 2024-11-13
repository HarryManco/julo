<?php
include 'db_connect.php';

// Retrieve the service_id and vehicle_id from the GET parameters
$service_id = isset($_GET['service_id']) ? intval($_GET['service_id']) : 0;
$vehicle_id = isset($_GET['vehicle_id']) ? intval($_GET['vehicle_id']) : 0;

if ($service_id === 0 || $vehicle_id === 0) {
    echo json_encode(["error" => "Invalid service or vehicle selection."]);
    exit();
}

// First, attempt to fetch the car model from the `cars` table (for reservation context)
$car_model_query = "SELECT car_model FROM cars WHERE id = ?";
$car_model_stmt = $conn->prepare($car_model_query);
$car_model_stmt->bind_param("i", $vehicle_id);
$car_model_stmt->execute();
$car_model_result = $car_model_stmt->get_result();

if ($car_model_result->num_rows > 0) {
    // If found in `cars`, use this result
    $car = $car_model_result->fetch_assoc();
    $car_model = $car['car_model'];
} else {
    // If not found, try fetching from the `car_sizes` table (for walk-in context)
    $car_model_query = "SELECT car_model FROM car_sizes WHERE id = ?";
    $car_model_stmt = $conn->prepare($car_model_query);
    $car_model_stmt->bind_param("i", $vehicle_id);
    $car_model_stmt->execute();
    $car_model_result = $car_model_stmt->get_result();

    if ($car_model_result->num_rows > 0) {
        $car = $car_model_result->fetch_assoc();
        $car_model = $car['car_model'];
    } else {
        echo json_encode(["error" => "Vehicle not found in either table."]);
        exit();
    }
}
$car_model_stmt->close();

// Fetch the price and duration for the specified service and car model
$query = "SELECT price, duration FROM service_prices WHERE service_id = ? AND car_model = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("is", $service_id, $car_model);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $data = $result->fetch_assoc();
    echo json_encode(["price" => $data['price'], "duration" => $data['duration']]);
} else {
    echo json_encode(["error" => "No pricing found for the selected service and vehicle."]);
}

// Close statement and connection
$stmt->close();
$conn->close();
?>
