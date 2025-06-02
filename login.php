<!-- login.php -->
<?php
include 'includes/config.php';

if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Fetch user from database
    $stmt = $conn->query("SELECT * FROM users WHERE email = '$email'");
    $user = $stmt->fetch_assoc();

    // Verify password
    if ($user && password_verify($password, $user['password'])) {

        //$_SESSION['user_id'] = $user['id'];
        // $_SESSION['username'] = $user['username'];
        setcookie("user_id", $user['id'], time() + 3600 * 24);
        setcookie("username", $user['username'], time() + 3600 * 24);

        header("Location: ../index.php");
        exit();
    } else {
        header("Location: ../login.php?error=Invalid username or password");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Login | Bookstore</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
    <div class="auth-container">
        <h1>Login</h1>
        <?php if (isset($_GET['error'])): ?>
            <div class="error-message"><?= htmlspecialchars($_GET['error']) ?></div>
        <?php endif; ?>
        <?php if (isset($_GET['success'])): ?>
            <div class="success-message"><?= htmlspecialchars($_GET['success']) ?></div>
        <?php endif; ?>
        <form class="auth-form" action="" method="POST">
            <input type="text" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" name="login">Login</button>
        </form>
        <div class="auth-links">
            Don't have an account? <a href="register.php">Register here</a>
        </div>
        <div class="auth-links">
            <a href="index.php">Back to Home</a>
        </div>
    </div>
</body>

</html>