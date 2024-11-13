<header class="header navbar navbar-expand-lg navbar-light bg-light fixed-top">
    <div class="container-fluid d-flex justify-content-center">
        <!-- Navbar Brand -->
        <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a href="#" data-bs-toggle="dropdown" class="nav-icon pe-md-0">
                        <svg width="40" height="40" viewBox="0 0 25 24" fill="#73c1fa" xmlns="http://www.w3.org/2000/svg" transform="rotate(0 0 0)">
<path d="M4.32031 4.75C3.62996 4.75 3.07031 5.30964 3.07031 6C3.07031 6.69036 3.62996 7.25 4.32031 7.25H4.33031C5.02067 7.25 5.58031 6.69036 5.58031 6C5.58031 5.30964 5.02067 4.75 4.33031 4.75H4.32031Z" fill="#73c1fa"/>
<path d="M8.31055 5.25C7.89633 5.25 7.56055 5.58579 7.56055 6C7.56055 6.41421 7.89633 6.75 8.31055 6.75L20.3105 6.75C20.7248 6.75 21.0605 6.41421 21.0605 6C21.0605 5.58579 20.7248 5.25 20.3105 5.25H8.31055Z" fill="#73c1fa"/>
<path d="M8.31055 17.25C7.89633 17.25 7.56055 17.5858 7.56055 18C7.56055 18.4142 7.89633 18.75 8.31055 18.75L20.3105 18.75C20.7248 18.75 21.0605 18.4142 21.0605 18C21.0605 17.5858 20.7248 17.25 20.3105 17.25L8.31055 17.25Z" fill="#73c1fa"/>
<path d="M7.56055 12C7.56055 11.5858 7.89633 11.25 8.31055 11.25L20.3105 11.25C20.7248 11.25 21.0605 11.5858 21.0605 12C21.0605 12.4142 20.7248 12.75 20.3105 12.75L8.31055 12.75C7.89633 12.75 7.56055 12.4142 7.56055 12Z" fill="#73c1fa"/>
<path d="M3.07031 12C3.07031 11.3096 3.62996 10.75 4.32031 10.75H4.33031C5.02067 10.75 5.58031 11.3096 5.58031 12C5.58031 12.6904 5.02067 13.25 4.33031 13.25H4.32031C3.62996 13.25 3.07031 12.6904 3.07031 12Z" fill="#73c1fa"/>
<path d="M4.32031 16.75C3.62996 16.75 3.07031 17.3096 3.07031 18C3.07031 18.6904 3.62996 19.25 4.32031 19.25H4.33031C5.02067 19.25 5.58031 18.6904 5.58031 18C5.58031 17.3096 5.02067 16.75 4.33031 16.75H4.32031Z" fill="#73c1fa"/>
</svg>



                    </a>
                    <div class="dropdown-menu dropdown-menu-end rounded">
                        <a class="dropdown-item"href="homepage.php"> Home</a>
                        <a class="dropdown-item"href="#"> Services</a>
                        <a class="dropdown-item"href="reservation.php">Reservation</a>
                        <a class="dropdown-item" href="cafemenu.php"> Cafe</a>
                        
                    </div>
                </li>
            </ul>
        <a class="navbar-brand" href="#">
            <img src="img/CARWASH.png" alt="Logo" style="height: 40px; width: auto;">
            
        </a>


        <!-- Navbar Content -->
        <div class="collapse navbar-collapse justify-content-center" id="navbarNav">
            <ul class="navbar-nav">
                <?php if ($isLoggedIn): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="homepage.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Services</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="reservation.php">Reservation</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="cafemenu.php">Cafe</a>
                    </li>
                    <li class="nav-item my-2">
                        <form target="paypal" action="https://www.paypal.com/cgi-bin/webscr" method="post" >
        <input type="hidden" name="cmd" value="_cart">
        <input type="hidden" name="business" value="JK225KJJHFV38">
        <input type="hidden" name="display" value="1">
        <input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_viewcart_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
        </form>
                    </li>
                    

                    <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link active" href="landingpage.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Carwash</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Cafe</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">About Us</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Contact Us</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>

        <!-- Profile Dropdown for Logged-in Users -->
        <?php if ($isLoggedIn): ?>
        
            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a href="#" data-bs-toggle="dropdown" class="nav-icon pe-md-0">
                        <?php if (!empty($imagePath)): ?>
                        
                            
                        <?php endif; ?>
                        <span class="username" style="font-family:sans-serif; font-size:large; font-weight: bolder;">
                            <?php echo htmlspecialchars($_SESSION['full_name']); ?>
                        </span>

                    </a>
                    <div class="dropdown-menu dropdown-menu-end rounded">
                        <a class="dropdown-item" href="userprofile.php"><i class="lni lni-user"></i> Profile</a>
                        <a class="dropdown-item" href="#"><i class="lni lni-cog"></i> Settings</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="logout.php"><i class="lni lni-exit"></i>Logout</a>
                    </div>
                </li>
            </ul>
        <?php else: ?>
            <a href="login.php" class="btn btn-primary ms-3">Login</a>
        <?php endif; ?>
    </div>
</header>