<?php
session_start();
include 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Initialize a notification variable
$notification = "";

// Handle car registration
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];
    $car_model = trim($_POST['car_model']);
    $car_year = trim($_POST['car_year']);

    // Validate inputs
    if (empty($car_model) || empty($car_year)) {
        $notification = "<div class='notification error'>Please fill in all fields.</div>";
    } elseif (!preg_match('/^\d{4}$/', $car_year) || $car_year < 1886 || $car_year > date("Y")) {
        $notification = "<div class='notification error'>Please enter a valid car year.</div>";
    } else {
        // Insert the car registration into the database
        $insert_query = "INSERT INTO car_models (user_id, car_model, car_year) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param("iss", $user_id, $car_model, $car_year);

        if ($stmt->execute()) {
            $notification = "<div class='notification success'>Your car has been successfully submitted for approval!</div>";
        } else {
            $notification = "<div class='notification error'>Error: " . $stmt->error . "</div>";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Car Registration</title>
    <link rel="stylesheet" href="css/car_model.css">
</head>
<body>
<?php include 'header.php'; ?>
    <div class="container">
        <h2>Car Registration</h2>

        <!-- Display Notification -->
        <?= $notification; ?>

        <form method="POST" action="car_model.php">
            <!-- Car Model -->
            <label for="car_model">Enter Car Model:</label>
            <input type="text" name="car_model" id="car_model" placeholder="Enter your car model" required>

            <!-- Car Year -->
            <label for="car_year">Enter Car Year:</label>
            <input type="text" name="car_year" id="car_year" placeholder="Enter car year (e.g., 2023)" required maxlength="4">

            <!-- Submit Button -->
            <button type="submit">Submit</button>
        </form>

        <!-- Additional Paragraph -->
        <p class="additional-info">Go back to <a href="car_registration.php">Car Registration</a></p>

        <!-- Approval Notice -->
        <p class="approval-notice">
            Please note: Approval for your car model may take 1-3 business days. We will send you a notification once the business can serve your car model.
        </p>
    </div>
</body>
</html>
