<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/header.css">   
</head>
<body>
    <div class="hero">
        <nav>
            <img src="img/CARWASH.png" class="logo">
            <ul>
                <li><a href="home.php">Home</a></li>
                <li class="dropdown"><a href="#">Carwash</a>
                    <ul class="dropdown-content">
                        <li><a href="car_registration.php">Car Registration</a></li>
                        <li><a href="calendar.php">Reservation</a></li>
                        <li><a href="reservation_history.php">My Reservation History</a></li>
                    </ul>
                <li class="dropdown"><a href="#">Cafe</a>
                    <ul class="dropdown-content">
                        <li><a href="cafemenu.php">Cafe Menu</a></li>
                        <li><a href="cart.php">My Cart</a></li>
                        <li><a href="order_history.php">Order History</a></li>
                    </ul>
                </li>
                </li>
                <li><a href="#">About Us</a></li>
                <li><a href="#">Contact</a></li>
            </ul>
            <img src="images/user.jpg" class="user-pic" onclick="toggleMenu()">

            <div class="sub-menu-wrap" id="subMenu">
                <div class="sub-menu">
                    <div class="user-info">
                        <img src="images/user.jpg">
                        <h3>Johary Manco</h3>
                    </div>
                    <hr>

                    <a href="#" class="sub-menu-link">
                        <img src="images/profile.png">
                        <p>Edit Profile</p>
                        <span>></span>
                    </a>
                    <a href="#" class="sub-menu-link">
                        <img src="images/setting.png">
                        <p>Settings</p>
                        <span>></span>
                    </a>
                    <a href="#" class="sub-menu-link">
                        <img src="images/help.png">
                        <p>Help & Support</p>
                        <span>></span>
                    </a>
                    <a href="login.php" class="sub-menu-link">
                        <img src="images/logout.png">
                        <p>Logout</p>
                        <span>></span>
                    </a>
                </div>
            </div>
        </nav>
    </div>

    <script>
        let subMenu = document.getElementById("subMenu");

        function toggleMenu(){
            subMenu.classList.toggle("open-menu");
        }
    </script>
</body>
</html>
