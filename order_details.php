<?php
include 'includes/config.php';

// Check if order_id is provided and belongs to user
if (!isset($_GET['order_id'])) {
    header("Location: order_history.php");
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
    header("Location: order_history.php?error=Order not found");
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

// Get status history
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
    <title>Order #<?= $order['id'] ?> Details | Bookstore</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
    <header>
        <nav>
            <div>
                <a href="index.php">Home</a>
                <?php if (isset($_COOKIE['user_id'])): ?>
                    <a href="cart.php">Cart</a>
                    <a href="order_history.php" class="active">Orders</a>
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
        <div class="order-details-page">
            <div class="back-link">
                <a href="order_history.php">&larr; Back to Order History</a>
            </div>

            <h1>Order #<?= $order['id'] ?></h1>

            <div class="order-meta">
                <p><strong>Order Date:</strong> <?= date('F j, Y, g:i a', strtotime($order['order_date'])) ?></p>
                <p><strong>Status:</strong> <span
                        class="status-<?= $order['status'] ?>"><?= ucfirst($order['status']) ?></span></p>
                <p><strong>Total:</strong> $<?= number_format($order['total_amount'], 2) ?></p>
            </div>

            <div class="order-sections">
                <div class="order-section">
                    <h2>Shipping Information</h2>
                    <p><?= htmlspecialchars($order['shipping_name']) ?></p>
                    <p><?= htmlspecialchars($order['shipping_address']) ?></p>
                    <p><?= htmlspecialchars($order['shipping_city']) ?>, <?= htmlspecialchars($order['shipping_zip']) ?>
                    </p>
                </div>

                <div class="order-section">
                    <h2>Payment Information</h2>
                    <p><strong>Method:</strong> <?= ucfirst(str_replace('_', ' ', $order['payment_method'])) ?></p>
                    <p><strong>Status:</strong> <?= ucfirst($order['payment_status']) ?></p>
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

            <div class="status-history">
                <h2>Status History</h2>
                <ul>
                    <?php foreach ($status_history as $history): ?>
                        <li>
                            <strong><?= ucfirst($history['status']) ?></strong>
                            <span><?= date('M j, Y g:i a', strtotime($history['status_date'])) ?></span>
                            <?php if (!empty($history['notes'])): ?>
                                <p class="notes"><?= htmlspecialchars($history['notes']) ?></p>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
</body>

</html>