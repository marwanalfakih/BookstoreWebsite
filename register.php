<!-- register.php -->
<?php
include 'includes/config.php';
if (isset($_POST['register'])) {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate passwords match
    if ($password !== $confirm_password) {
        header("Location: register.php?error=Passwords do not match");
        exit();
    }

    // Check if user already exists
    $stmt = $conn->query("SELECT * FROM users WHERE username = '$username' OR email = '$email'");
    if ($stmt->fetch_assoc()) {
        header("Location: register.php?error=Username or email already exists");
        exit();
    }

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert new user
    $stmt = $conn->query("INSERT INTO users (username, email, password) 
                    VALUES ('$username', '$email', '$hashed_password')");
    if ($stmt) {
        header("Location: ../login.php?success=Registration successful. Please login.");
    } else {
        header("Location: ../register.php?error=Registration failed");
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Register | Bookstore</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
    <div class="auth-container">
        <h1>Register</h1>
        <?php if (isset($_GET['error'])): ?>
            <div class="error-message"><?= htmlspecialchars($_GET['error']) ?></div>
        <?php endif; ?>
        <form class="auth-form" action="" method="POST">
            <input type="text" name="username" placeholder="Username" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <input type="password" name="confirm_password" placeholder="Confirm Password" required>
            <button type="submit" name="register">Register</button>
        </form>
        <div class="auth-links">
            Already have an account? <a href="login.php">Login here</a>
        </div>
        <div class="auth-links">
            <a href="index.php">Back to Home</a>
        </div>
    </div>
</body>

</html>