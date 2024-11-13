<?php
// Start the session
session_start();

// Check if the user is logged in as admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Include database connection
include 'db_connect.php';

$message = "";
$message_class = "";

// Handle AJAX request for adding a new car model and size
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['ajax'])) {
    $car_model = $_POST['car_model'];
    $car_size = $_POST['car_size'];

    // Insert the new car model and size
    $insert_query = "INSERT INTO car_sizes (car_model, car_size) VALUES ('$car_model', '$car_size')";
    if (mysqli_query($conn, $insert_query)) {
        // Fetch the newly added row to return as JSON response
        $last_id = mysqli_insert_id($conn);
        $new_car_size_query = "SELECT * FROM car_sizes WHERE id = $last_id";
        $new_car_size_result = mysqli_query($conn, $new_car_size_query);
        $new_car_size = mysqli_fetch_assoc($new_car_size_result);

        echo json_encode([
            "status" => "success",
            "message" => "Car model added successfully!",
            "data" => $new_car_size
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "Error: " . mysqli_error($conn)
        ]);
    }
    exit();
}

// Fetch all car models and sizes for initial page load
$car_sizes_query = "SELECT * FROM car_sizes";
$car_sizes_result = mysqli_query($conn, $car_sizes_query);
?>
<?php include 'sidebar.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Car Models and Sizes</title>
    <link rel="stylesheet" href="css/viewreservation.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div class="container">
        <h2>Manage Car Models and Sizes</h2>

        <div id="message" class="notification"></div>

        <form id="car-size-form" method="POST" action="">
            <label for="car_model">Car Model:</label>
            <input type="text" name="car_model" id="car_model" required placeholder="Enter car model">
            
            <label for="car_size">Car Size:</label>
            <select name="car_size" id="car_size" required>
                <option value="small">Small</option>
                <option value="medium">Medium</option>
                <option value="large">Large</option>
            </select>

            <button type="submit">Add Car Model</button>
        </form>

        <h3>Existing Car Models and Sizes</h3>
        <table id="car-size-table">
            <thead>
                <tr>
                    <th>Car Model</th>
                    <th>Car Size</th>
                    <th>Created At</th>
                    <th>Updated At</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($car_size = mysqli_fetch_assoc($car_sizes_result)): ?>
                <tr>
                    <td><?= $car_size['car_model'] ?></td>
                    <td><?= ucfirst($car_size['car_size']) ?></td>
                    <td><?= $car_size['created_at'] ?></td>
                    <td><?= $car_size['updated_at'] ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <a href="sidebar.php">Back to Dashboard</a>
    </div>

    <script src="js/carsize.js"></script>
</body>
</html>
