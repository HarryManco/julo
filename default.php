
<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
// Check if the customer is logged in
$isLoggedIn = false;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Car Wash & Cafe</title>
    <link href="https://cdn.lineicons.com/4.0/lineicons.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="css/style_lp.css">
    <link rel="stylesheet" href="css/home.css">
</head>
<body>
<?php include 'homeheader.php'; ?>
    <!-- Header Section with Background Image -->
    <header class="home">
        <div class="home-content">
            <h1>Welcome to Julo Carwash and Detailing Services</h1>
            <p>Relax at our cafe while your car gets pampered!</p>
            <a href="#services" class="btn">View Services</a>
        </div>
    </header>

    <!-- Services Section -->
    <section id="services" class="services">
        <h2>Our Services</h2>
        <div class="service-items">
            <div class="service-item">
                <h3>Exterior Wash</h3>
                <p>Top-to-bottom cleaning with premium products.</p>
            </div>
            <div class="service-item">
                <h3>Interior Detailing</h3>
                <p>Leave the interior fresh and spotless.</p>
            </div>
            <div class="service-item">
                <h3>Relaxing Cafe</h3>
                <p>Enjoy coffee and snacks while you wait.</p>
            </div>
            <div class="service-item">
                <h3>Exterior Wash</h3>
                <p>Top-to-bottom cleaning with premium products.</p>
            </div>
            <div class="service-item">
                <h3>Interior Detailing</h3>
                <p>Leave the interior fresh and spotless.</p>
            </div>
            <div class="service-item">
                <h3>Relaxing Cafe</h3>
                <p>Enjoy coffee and snacks while you wait.</p>
            </div>
        </div>
    </section>

    <!-- Cafe Section
    <section id="cafe" class="cafe">
        <h2>Our Cafe</h2>
        <p>Enjoy freshly brewed coffee and pastries while we take care of your car.</p>
        <div class="menu">
            <div class="menu-item">
                <h3>Espresso</h3>
                <p>Rich, smooth espresso for a quick boost.</p>
            </div>
            <div class="menu-item">
                <h3>Latte</h3>
                <p>Creamy latte to enjoy while you wait.</p>
            </div>
            <div class="menu-item">
                <h3>Pastries</h3>
                <p>A selection of freshly baked pastries.</p>
            </div>
        </div>
    </section> -->
    <section class="cafe">
        <h2>Our Cafe</h2>
        <div class="menu-items">
            <?php
            // Array of feedback and images for customers
            $cafes = [
                ["name" => "Espresso", "comment" => "Rich, smooth espresso for a quick boost.", "image" => "images/espresso.jpg"],
                ["name" => "Milkteas", "comment" => "Creamy milktea to enjoy while you wait.", "image" => "images/milk.webp"],
                ["name" => "Pastries", "comment" => "A selection of freshly baked pastries.", "image" => "images/pastries.jpg"]
            ];

            foreach ($cafes as $cafe) {
                echo "<div class='menu-item'>";
                echo "<img src='" . $cafe['image'] . "' alt='Customer image'>";
                echo "<h3>" . $cafe['name'] . "</h3>";
                echo "<p>" . $cafe['comment'] . "</p>";
                echo "</div>";
            }
            ?>
        </div>
        <h3>.</h3>
        <a href="cafe.php" class="btn">View Cafe Menu</a>
    </section>

    <!-- Customer Feedback Section -->
    <section class="feedback">
        <h2>Customer Feedback</h2>
        <div class="feedback-items">
            <?php
            // Array of feedback and images for customers
            $feedbacks = [
                ["name" => "Rona G.", "comment" => "Amazing service and great coffee!", "image" => "images/img2.jpg"],
                ["name" => "Kevs A.", "comment" => "Perfect place to relax while waiting.", "image" => "images/img2.jpg"],
                ["name" => "Harry M.", "comment" => "My car has never looked better!", "image" => "images/img2.jpg"]
            ];

            foreach ($feedbacks as $feedback) {
                echo "<div class='feedback-item'>";
                echo "<img src='" . $feedback['image'] . "' alt='Customer image'>";
                echo "<h3>" . $feedback['name'] . "</h3>";
                echo "<p>" . $feedback['comment'] . "</p>";
                echo "</div>";
            }
            ?>
        </div>
    </section>
    <section id="contact" class="contact">
            <h2>Contact Us</h2>
            <p>Phone: (123) 456-7890</p>
            <p>Email: julocarwashanddetailingservices@gmail.com</p>
            <p>Address: address ni Julo</p>
    </section>

    <!-- Footer -->
    <footer>
        <p>&copy; 2023 Julo Carwash & Detailing Services. All rights reserved.</p>
        <p><a href="#privacy">Privacy Policy</a> | <a href="#terms">Terms of Service</a></p>
    </footer>
</body>
</html>