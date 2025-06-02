<?php
include 'config.php';
session_start();

if ($_GET['action'] == 'add_to_cart') {
    if (!isset($_COOKIE['user_id'])) {
        echo json_encode(['error' => 'Not logged in']);
        exit();
    }
    $book_id = $_POST['book_id'];
    $user_id = $_COOKIE['user_id'];

    $check = $conn->prepare("SELECT * FROM cart WHERE user_id = ? AND book_id = ?");
    $check->bind_param("ii", $user_id, $book_id);
    $check->execute();

    if ($check->get_result()->num_rows > 0) {
        echo json_encode(['error' => 'Book already in cart']);
        exit();
    }

    $stmt = $conn->prepare("INSERT INTO cart (user_id, book_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $user_id, $book_id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['error' => 'Failed to add to cart']);
    }
    exit();
}
// Remove from cart
if ($_GET['action'] == 'remove_from_cart') {
    if (!isset($_COOKIE['user_id'])) {
        header("Location: login.php");
        exit();
    }
    $book_id = $_GET['book_id'];
    $user_id = $_COOKIE['user_id'];

    $stmt = $conn->query("DELETE FROM cart WHERE user_id = '$user_id' AND book_id = '$book_id'");
    if ($stmt === TRUE) {
        header("Location: ../cart.php");
    } else {
        echo "Error removing item." . $conn->error;
    }
    exit();
}
