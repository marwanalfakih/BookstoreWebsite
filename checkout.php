<?php
include 'includes/config.php';

// Check if cart is empty
$stmt = $conn->prepare("SELECT COUNT(*) FROM cart WHERE user_id = ?");
$stmt->bind_param("i", $_COOKIE['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$cart_count = $result->fetch_row()[0];

if ($cart_count == 0) {
    header("Location: cart.php");
    exit();
}

// Get cart items for order summary
$stmt = $conn->prepare("
    SELECT books.id, books.title, books.price, cart.quantity 
    FROM cart 
    JOIN books ON cart.book_id = books.id 
    WHERE cart.user_id = ?
");
$stmt->bind_param("i", $_COOKIE['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$cart_items = $result->fetch_all(MYSQLI_ASSOC);

// Calculate order totals
$subtotal = 0;
foreach ($cart_items as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}
$shipping = 0; // Free shipping for this example
$total = $subtotal + $shipping;

// Process checkout when form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Insert order
    $stmt = $conn->prepare("
        INSERT INTO orders (
            user_id, 
            total_amount, 
            payment_method,
            shipping_name,
            shipping_address,
            shipping_city,
            shipping_zip
        ) VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param(
        "idsssss",
        $_COOKIE['user_id'],
        $total,
        $_POST['payment_method'],
        $_POST['name'],
        $_POST['address'],
        $_POST['city'],
        $_POST['zip']
    );
    $stmt->execute();
    $order_id = $conn->insert_id;

    // Insert order items
    foreach ($cart_items as $item) {
        $stmt = $conn->prepare("
            INSERT INTO order_items (
                order_id,
                book_id,
                quantity,
                price
            ) VALUES (?, ?, ?, ?)
        ");
        $stmt->bind_param(
            "iiid",
            $order_id,
            $item['id'],
            $item['quantity'],
            $item['price']
        );
        $stmt->execute();
    }

    // Record initial status
    $stmt = $conn->prepare("
        INSERT INTO order_status_history (
            order_id,
            status
        ) VALUES (?, 'pending')
    ");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();

    // Clear cart
    $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
    $stmt->bind_param("i", $_COOKIE['user_id']);
    $stmt->execute();

    header("Location: order_confirmation.php?order_id=" . $order_id);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Checkout | Bookstore</title>
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
        <h1>Checkout</h1>
        <form method="POST" class="checkout-form">
            <h2>Shipping Information</h2>
            <input type="text" name="name" placeholder="Full Name" required>
            <input type="text" name="address" placeholder="Address" required>
            <input type="text" name="city" placeholder="City" required>
            <input type="text" name="zip" placeholder="ZIP Code" required>

            <h2>Payment Method</h2>
            <select name="payment_method" required>
                <option value="credit_card">Credit Card</option>
                <option value="paypal">PayPal</option>
            </select>

            <!-- Dynamic Order Summary -->
            <div class="order-summary">
                <h3>Order Summary</h3>

                <!-- Cart Items List -->
                <?php foreach ($cart_items as $item): ?>
                    <div class="order-summary-item">
                        <span><?= htmlspecialchars($item['title']) ?> (x<?= $item['quantity'] ?>)</span>
                        <span>$<?= number_format($item['price'] * $item['quantity'], 2) ?></span>
                    </div>
                <?php endforeach; ?>

                <!-- Subtotal -->
                <div class="order-summary-item">
                    <span>Subtotal</span>
                    <span>$<?= number_format($subtotal, 2) ?></span>
                </div>

                <!-- Shipping -->
                <div class="order-summary-item">
                    <span>Shipping</span>
                    <span>$<?= number_format($shipping, 2) ?></span>
                </div>

                <!-- Total -->
                <div class="order-summary-total">
                    <span>Total</span>
                    <span>$<?= number_format($total, 2) ?></span>
                </div>
            </div>

            <button type="submit">Place Order</button>
        </form>
    </div>
</body>

</html>