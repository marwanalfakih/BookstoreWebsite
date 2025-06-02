<?php
include 'includes/config.php';

// Check if order_id is provided
if (!isset($_GET['order_id'])) {
    header("Location: index.php");
    exit();
}

$order_id = intval($_GET['order_id']);

// Get order details
$stmt = $conn->prepare("
    SELECT o.*, u.username, u.email 
    FROM orders o
    JOIN users u ON o.user_id = u.id
    WHERE o.id = ? AND o.user_id = ?
");
$stmt->bind_param("ii", $order_id, $_COOKIE['user_id']);
$stmt->execute();
$order_result = $stmt->get_result();

if ($order_result->num_rows === 0) {
    header("Location: index.php?error=Order not found");
    exit();
}

$order = $order_result->fetch_assoc();

// Get order items
$stmt = $conn->prepare("
    SELECT oi.*, b.title, b.image 
    FROM order_items oi
    JOIN books b ON oi.book_id = b.id
    WHERE oi.order_id = ?
");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$items_result = $stmt->get_result();
$order_items = $items_result->fetch_all(MYSQLI_ASSOC);

// Get order status history
$stmt = $conn->prepare("
    SELECT * FROM order_status_history
    WHERE order_id = ?
    ORDER BY status_date DESC
");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$history_result = $stmt->get_result();
$status_history = $history_result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Order Confirmation | Bookstore</title>
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
        <div class="order-confirmation">
            <h1>Order Confirmation</h1>
            <div class="confirmation-message">
                <p>Thank you for your order, <?= htmlspecialchars($order['username']) ?>!</p>
                <p>Your order #<?= $order['id'] ?> has been received and is being processed.</p>
                <p>A confirmation email has been sent to <?= htmlspecialchars($order['email']) ?>.</p>
            </div>

            <div class="order-details">
                <div class="order-section">
                    <h2>Order Summary</h2>
                    <p><strong>Order Number:</strong> #<?= $order['id'] ?></p>
                    <p><strong>Order Date:</strong> <?= date('F j, Y, g:i a', strtotime($order['order_date'])) ?></p>
                    <p><strong>Status:</strong> <span
                            class="status-<?= $order['status'] ?>"><?= ucfirst($order['status']) ?></span></p>
                    <p><strong>Payment Method:</strong> <?= ucfirst(str_replace('_', ' ', $order['payment_method'])) ?>
                    </p>
                    <p><strong>Payment Status:</strong> <?= ucfirst($order['payment_status']) ?></p>
                    <p><strong>Total Amount:</strong> $<?= number_format($order['total_amount'], 2) ?></p>
                </div>

                <div class="order-section">
                    <h2>Shipping Information</h2>
                    <p><?= htmlspecialchars($order['shipping_name']) ?></p>
                    <p><?= htmlspecialchars($order['shipping_address']) ?></p>
                    <p><?= htmlspecialchars($order['shipping_city']) ?>, <?= htmlspecialchars($order['shipping_zip']) ?>
                    </p>
                </div>
            </div>

            <div class="order-items">
                <h2>Order Items</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($order_items as $item): ?>
                        <tr>
                            <td>
                                <div class="item-info">
                                    <?php if ($item['image']): ?>
                                    <img src="assets/images/<?= htmlspecialchars($item['image']) ?>"
                                        alt="<?= htmlspecialchars($item['title']) ?>" width="50">
                                    <?php endif; ?>
                                    <span><?= htmlspecialchars($item['title']) ?></span>
                                </div>
                            </td>
                            <td>$<?= number_format($item['price'], 2) ?></td>
                            <td><?= $item['quantity'] ?></td>
                            <td>$<?= number_format($item['price'] * $item['quantity'], 2) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="text-right">Subtotal:</td>
                            <td>$<?= number_format($order['total_amount'], 2) ?></td>
                        </tr>
                        <tr>
                            <td colspan="3" class="text-right">Shipping:</td>
                            <td>$0.00</td>
                        </tr>
                        <tr class="total">
                            <td colspan="3" class="text-right">Total:</td>
                            <td>$<?= number_format($order['total_amount'], 2) ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="order-actions">
                <a href="index.php" class="btn">Continue Shopping</a>
                <a href="order_history.php" class="btn">View Order History</a>
            </div>
        </div>
    </div>
</body>

</html>