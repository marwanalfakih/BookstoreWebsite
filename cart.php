<?php
include 'includes/config.php';
session_start();

// Redirect if not logged in
if (!isset($_COOKIE['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get cart items with book details
$user_id = $_COOKIE['user_id'];
$stmt = $conn->prepare("
    SELECT books.id, books.title, books.author, books.price, books.image, cart.quantity 
    FROM cart 
    JOIN books ON cart.book_id = books.id 
    WHERE cart.user_id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$cart_items = $result->fetch_all(MYSQLI_ASSOC);

// Calculate total
$total = 0;
foreach ($cart_items as $item) {
    $total += $item['price'] * $item['quantity'];
}

// Handle quantity updates
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_quantity'])) {
        $book_id = $_POST['book_id'];
        $quantity = (int)$_POST['quantity'];

        if ($quantity > 0) {
            $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND book_id = ?");
            $stmt->bind_param("iii", $quantity, $user_id, $book_id);
            $stmt->execute();
        } else {
            $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ? AND book_id = ?");
            $stmt->bind_param("ii", $user_id, $book_id);
            $stmt->execute();
        }

        header("Location: cart.php");
        exit();
    }

    if (isset($_POST['checkout'])) {
        // Process checkout logic here
        header("Location: checkout.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Your Shopping Cart | Bookstore</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
    <header>
        <nav>
            <div>
                <a href="index.php">Home</a>
                <?php if (isset($_COOKIE['user_id'])): ?>
                <a href="cart.php" class="active">Cart</a>
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
        <h1>Your Shopping Cart</h1>

        <?php if (empty($cart_items)): ?>
        <div class="empty-cart">
            <p>Your cart is empty. <a href="index.php">Browse our books</a></p>
        </div>
        <?php else: ?>
        <div class="cart-items">
            <?php foreach ($cart_items as $item): ?>
            <div class="cart-item">
                <div class="cart-item-image">
                    <img src="assets/images/<?= htmlspecialchars($item['image']) ?>"
                        alt="<?= htmlspecialchars($item['title']) ?>">
                </div>
                <div class="cart-item-details">
                    <h3><?= htmlspecialchars($item['title']) ?></h3>
                    <p>by <?= htmlspecialchars($item['author']) ?></p>
                    <p class="price">$<?= number_format($item['price'], 2) ?></p>

                    <form method="POST" class="quantity-form">
                        <input type="hidden" name="book_id" value="<?= $item['id'] ?>">
                        <label>
                            Quantity:
                            <input type="number" name="quantity" value="<?= $item['quantity'] ?>" min="1" max="10">
                        </label>
                        <button type="submit" name="update_quantity" class="btn">Update</button>
                        <a href="includes/functions.php?action=remove_from_cart&book_id=<?= $item['id'] ?>"
                            class="btn btn-danger">Remove</a>
                    </form>
                </div>
                <div class="cart-item-subtotal">
                    $<?= number_format($item['price'] * $item['quantity'], 2) ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="cart-summary">
            <div class="cart-total">
                <h3>Total: $<?= number_format($total, 2) ?></h3>
                <form method="POST">
                    <button type="submit" name="checkout" class="btn btn-success">Proceed to Checkout</button>
                </form>
            </div>
        </div>
        <?php endif; ?>
    </div>
</body>

</html>