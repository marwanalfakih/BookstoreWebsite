<?php
include 'includes/config.php';
// include 'includes/auth.php'; // Uncomment to ensure user is logged in

// Get all orders for the current user
$stmt = $conn->prepare("
    SELECT o.id, o.order_date, o.total_amount, o.status, 
           COUNT(oi.id) AS item_count
    FROM orders o
    LEFT JOIN order_items oi ON o.id = oi.order_id
    WHERE o.user_id = ?
    GROUP BY o.id
    ORDER BY o.order_date DESC
");
$stmt->bind_param("i", $_COOKIE['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$orders = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Order History | Bookstore</title>
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
        <h1>Order History</h1>

        <?php if (count($orders) === 0): ?>
            <div class="empty-order-history">
                <p>You haven't placed any orders yet.</p>
                <a href="index.php" class="btn">Browse Books</a>
            </div>
        <?php else: ?>
            <div class="order-list">
                <table>
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <th>Date</th>
                            <th>Items</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td>#<?= $order['id'] ?></td>
                                <td><?= date('M j, Y', strtotime($order['order_date'])) ?></td>
                                <td><?= $order['item_count'] ?></td>
                                <td>$<?= number_format($order['total_amount'], 2) ?></td>
                                <td>
                                    <span class="status-<?= $order['status'] ?>">
                                        <?= ucfirst($order['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="order_details.php?order_id=<?= $order['id'] ?>" class="btn btn-small">
                                        View Details
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</body>

</html>