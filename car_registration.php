<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

include 'db_connect.php';

// Fetch car models and sizes from the car_sizes table
$car_sizes_query = "SELECT * FROM car_sizes";
$car_sizes_result = mysqli_query($conn, $car_sizes_query);

// Initialize a notification variable
$notification = "";

// Handle car registration for customers
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];
    $car_model = $_POST['car_model'];
    $plate_no = $_POST['plate_no'];
    $color = $_POST['color'];

    // Validate the plate number format (PHP validation)
    if (!preg_match("/^[A-Z]{3} \d{3}$/", $plate_no)) {
        $notification = "<div class='notification error'>Error: Plate number must be in the format 'AAA 111'.</div>";
    } else {
        // Fetch the car size based on the selected model
        $car_size_query = "SELECT car_size FROM car_sizes WHERE car_model = '$car_model'";
        $car_size_result = mysqli_query($conn, $car_size_query);
        $car_size_row = mysqli_fetch_assoc($car_size_result);
        $car_size = $car_size_row['car_size'];

        // Check if the car model is already registered by the same user
        $check_car_query = "SELECT * FROM cars WHERE user_id = $user_id AND car_model = '$car_model'";
        $check_car_result = mysqli_query($conn, $check_car_query);

        if (mysqli_num_rows($check_car_result) > 0) {
            // Car model already registered by this user
            $notification = "<div class='notification error'>Error: You have already registered this car model.</div>";
        } else {
            // Insert the car registration with the current date
            $insert_car_query = "INSERT INTO cars (user_id, car_model, car_size, plate_no, color, registration_date) 
                                 VALUES ($user_id, '$car_model', '$car_size', '$plate_no', '$color', NOW())";

            if (mysqli_query($conn, $insert_car_query)) {
                $notification = "<div class='notification success'>Car registered successfully on " . date('Y-m-d H:i:s') . "!</div>";
            } else {
                $notification = "<div class='notification error'>Error: " . mysqli_error($conn) . "</div>";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Car</title>
    <link rel="stylesheet" href="css/car.css">
    <!-- Link to Select2 CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
</head>
<body>
<?php include 'header.php'; ?>
    <div class="container">
        <h2>Register Your Car</h2>

        <!-- Display notification message -->
        <?= $notification ?>

        <form method="POST" action="">
            <!-- Car Model -->
            <label for="car_model">Select Car Model:</label>
            <select id="car_model" name="car_model" class="searchable-dropdown" required>
                <option></option> <!-- Placeholder for Select2 -->
                <?php
                while ($car_size = mysqli_fetch_assoc($car_sizes_result)) {
                    echo "<option value='{$car_size['car_model']}'>{$car_size['car_model']}</option>";
                }
                ?>
            </select>

            <!-- Plate Number -->
            <label for="plate_no">Plate Number:</label>
            <input type="text" name="plate_no" required placeholder="Ex. AAA 111" maxlength="7" 
                   oninput="validatePlateNumber(this)">

            <!-- Car Color -->
            <label for="color">Car Color:</label>
            <input type="text" name="color" required placeholder="Enter Car Color">

            <button type="submit">Register Car</button>
        </form>

        <!-- Reserve Now button -->
        <a href="calendar.php" class="reserve-btn">Reserve Now</a>

        <!-- Add paragraph under the Reserve Now button -->
        <p class="additional-info">Can't find your car model? Try to register your car. <a href="car_model.php">Click here.</a></p>
    </div>

    <!-- Include jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Include Select2 JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <!-- Link to external JS file -->
    <script src="js/car_registration.js"></script>
</body>
</html>
