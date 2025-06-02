<?php
session_start();
include 'includes/config.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Bookstore Home</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
    <header>
        <nav>
            <div>
                <a href="index.php" class="active">Home</a>
                <?php if (isset($_COOKIE['user_id'])): ?>
                <a href="cart.php">Cart</a>
                <a href="order_history.php">Orders</a>
                <?php endif; ?>
            </div>
            <div>
                <?php if (isset($_COOKIE['user_id'])): ?>
                <span class="user-greeting">Hello, <?= htmlspecialchars($_COOKIE['username']) ?></span>
                <a href="logout.php">Logout</a>
                <?php else: ?>
                <a href="login.php">Login</a>
                <a href="register.php">Register</a>
                <?php endif; ?>
            </div>
        </nav>
    </header>
    <div class="container">
        <h1>Welcome to Our Bookstore</h1>

        <?php if (!isset($_COOKIE['user_id'])): ?>
        <div class="guest-message">
            <p>Please <a href="login.php">login</a> or <a href="register.php">register</a> to purchase books.</p>
        </div>
        <?php endif; ?>

        <div class="books">
            <?php
            $stmt = $conn->query("SELECT * FROM books");
            while ($book = $stmt->fetch_assoc()):
            ?>
            <div class="book">
                <div class="book-image">
                    <img src="assets/images/<?= $book['image'] ?>" alt="<?= $book['title'] ?>">
                </div>
                <h3><?= $book['title'] ?></h3>
                <p>by <?= $book['author'] ?>
                <div class="price">$<?= number_format($book['price'], 2) ?></div>
                </p>
                <?php if (isset($_COOKIE['user_id'])): ?>
                <a href="book.php?id=<?= $book['id'] ?>" class="btn">View Details</a>
                <?php else: ?>
                <a href="login.php" class="btn">Login to View</a>
                <?php endif; ?>
            </div>
            <?php endwhile; ?>
        </div>
    </div>
</body>

</html>