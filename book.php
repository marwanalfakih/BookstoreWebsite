<?php
include 'includes/config.php';

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$book_id = $_GET['id'];
$stmt = $conn->prepare("SELECT * FROM books WHERE id = ?");
$stmt->bind_param("i", $book_id);
$stmt->execute();
$result = $stmt->get_result();
$book = $result->fetch_assoc();

if (!$book) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($book['title']) ?> | Bookstore</title>
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
    <div class="container book-detail">
        <div class="book-main">
            <div class="book-image-large">
                <img src="assets/images/<?= htmlspecialchars($book['image']) ?>"
                    alt="<?= htmlspecialchars($book['title']) ?>">
            </div>
            <div class="book-info">
                <h1><?= htmlspecialchars($book['title']) ?></h1>
                <p class="author">by <?= htmlspecialchars($book['author']) ?></p>
                <p class="price">$<?= number_format($book['price'], 2) ?></p>
                <p class="description"><?= htmlspecialchars($book['description']) ?></p>

                <?php if (isset($_COOKIE['user_id'])): ?>
                    <button onclick="addToCart(<?= $book['id'] ?>)" class="btn">Add to Cart</button>
                <?php else: ?>
                    <a href="login.php" class="btn">Login to Purchase</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <script src="assets/js/script.js"></script>
</body>

</html>