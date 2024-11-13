<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
// Include database connection
include 'db_connect.php';
$error_message = '';
$success_message = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $repeat_password = $_POST['repeat_password'];

    // Input validation
    if (strlen($password) < 8 || 
        !preg_match('/[A-Za-z]/', $password) || 
        !preg_match('/[0-9]/', $password) || 
        !preg_match('/[^A-Za-z0-9]/', $password)) {
        $error_message = "Password must be at least 8 characters long, include one letter, one number, and one special character.";
    } elseif ($password !== $repeat_password) {
        $error_message = "Passwords do not match!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Invalid email format.";
    } else {
        // Hash the password and generate a verification token
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        $token = bin2hex(random_bytes(50));

        // Insert user data into the database
        $query = "INSERT INTO users (username, email, password, role, verificationtoken, emailverified) VALUES (?, ?, ?, 'customer', ?, 0)";
        $stmt = $conn->prepare($query);

        if ($stmt) {
            $stmt->bind_param("ssss", $username, $email, $hashed_password, $token);
            if ($stmt->execute()) {
                // Send verification email
                if (sendVerificationEmail($email, $token)) {
                    $success_message = "Registration successful! Please verify your email.";
                } else {
                    $error_message = "Failed to send verification email.";
                }
            } else {
                $error_message = "Registration failed: " . $stmt->error;
            }
        } else {
            $error_message = "Error preparing statement: " . $conn->error;
        }
    }
}
function sendVerificationEmail($email, $token) {
    $subject = "Verify your email address";
    $message = "Please click the link below to verify your email address:\n\n";
    $message .= "https://jsr-julo.online/verify.php?token=" . $token;
    $headers = "From: no-reply@yourdomain.com";

    return mail($email, $subject, $message, $headers);
}
error_reporting(E_ALL);
ini_set('display_errors', 1);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
   <title>Register</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">
</head>
<body class="text-center">

    <div class="container d-flex justify-content-center align-items-center vh-100">
        <div class="card" style="border: none; border-radius: 24px;">
            <div class="card-body m-4">
                <div class="text-center mb-3">
                    <label class="create-acc-title">Create an Account</label>
                </div>
                
                <form action="" method="POST">
                    <div class="row mb-2">

                    <div class="mb-2 text-start">
                            <label for="inputUsername" class="form-label">Username</label>
                            <input type="text" name="username" required placeholder="Username" class="form-control" style="border-radius: 20px; font-size: 1.5rem;" required>
                        </div>

                        <div class="mb-2 text-start">
                            <label for="inputEmail" class="form-label">Email address</label>
                            <input type="email" name="email" required placeholder="Email" class="form-control" style="border-radius: 20px; font-size: 1.5rem;" required>
                        </div>
                    </div>
                    <div class="mb-2 text-start">
                        <label for="inputPassword" class="form-label">Password</label>
                        <input type="password" name="password" required placeholder="Password" class="form-control" style="border-radius: 20px; font-size: 1.5rem;" required>
                        <div id="passwordHelpBlock" class="form-text">Password must be at least 8 characters long, include at least one letter, one number, and one special character.</div>               
                    </div>
                    <div class="mb-2 text-start">
                        <label class="form-label">Confirm password</label>
                        <input type="password" name="repeat_password" required placeholder="Repeat Password" class="form-control" style="border-radius: 20px; font-size: 1.5rem;" required>
                    </div>

                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" style="height: 1.8rem; width: 1.8rem;" id="agreeTerms" required>
                        <label class="form-check-label text-center" style="font-size: 1.5rem;" >I agree to the <a href="termandconditions.php">terms and conditions</a></label>
                    </div>

                    <div class="mb-4">
                        <button type="submit" name="submit" class="btn btn-primary w-100" value="Register" style="border-radius: 20px; font-size: 1.5rem; font-weight: 600;">Register</button>
                    </div>

                    <div>
                        <label class="form-label">Already have an account?</label>
                        <a href="login.php" class="create-account">Sign in</a>
                    </div>
                    <!-- Display error or success message -->
<?php if ($error_message): ?>
    <p style="color: red;"><?php echo $error_message; ?></p>
<?php endif; ?>
<?php if ($success_message): ?>
    <p style="color: green;"><?php echo $success_message; ?></p>
<?php endif; ?>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>