<?php
// Include database connection
include 'db_connect.php';

// Handle login form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Fetch user by username from the database
    $query = "SELECT * FROM users WHERE username = '$username'";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);

        // Verify password
        if (password_verify($password, $user['password'])) {
            // Start session and redirect based on role
            session_start();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            if ($user['role'] === 'admin') {
                header('Location: sidebar.php');
            } else {
                header('Location: calendar.php');
            }
        } else {
            echo "Invalid password!";
        }
    } else {
        echo "No account found with that username!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
   <title>Sign in</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

</head>
<body class="text-center">

    <div class="container d-flex justify-content-center align-items-center vh-100">
        <div class="card" style="border: none; border-radius: 24px;">
            <div class="card-body m-4">
                <div class="text-center mb-4">
                    <img src="images/CARWASH.png" class="img-fluid logo-image mb-5" style="max-width: 200px; height: auto;" alt="Logo">
                    <label class="sign-in-title">Welcome to Julo Carwash</label>
                </div>
            
                <form action="" method="POST">
                    <div class="mb-3 text-start">
                        <label class="form-label">Username</label>
                        <input type="text" name="username" required placeholder="Username" class="form-control" style="border-radius: 20px; font-size: 1.5rem;" required>
                    </div>
                    <div class="mb-3 d-flex justify-content-between align-items-center">
                        <label class="form-label mb-0">Password</label>
                        <a href="forgot_password.html" class="forgot-password">Forgot password?</a>
                    </div>
                    <div class="mb-3">
                        <input type="password" name="password" required placeholder="Password" class="form-control" style="border-radius: 20px; font-size: 1.5rem;" required>
                    </div>
                    <div class="mb-5">
                        <button type="submit" name="submit" class="btn btn-primary w-100"  value="Login" style="border-radius: 20px; font-size: 1.5rem; font-weight: 500;">Sign in</button>
                    </div>
                    <div>
                        <label class="form-label">Can't sign in?</label>
                        <a href="register.php" class="create-account">Create account</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- Error Modal -->
    <div class="modal fade" id="errorModal" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="errorModalLabel">Error</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php if (!empty($error_message)): ?>
                        <p><?php echo $error_message; ?></p>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
                </div>
            </div>
        </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    
    <script>
        // Show the modal if there's an error message
        const errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
        <?php if (!empty($error_message)): ?>
            errorModal.show();
        <?php endif; ?>
    </script>
</body>
</html>
