<?php
require_once '../includes/session.php';
include "../includes/db.php";

$orderId = intval($_GET['id'] ?? 0);

if ($orderId == 0) {
    die("Invalid order ID");
}

// fetch old order
$order = mysqli_query($conn, "
    SELECT * FROM orders WHERE order_id = $orderId
");

if (!$order) {
    die("SQL Error: " . mysqli_error($conn));
}

$data = mysqli_fetch_assoc($order);

// store in session (cart)
$_SESSION['cart'] = json_decode($data['items'], true);

// redirect to menu page
header("Location: index.php");
exit();
?>