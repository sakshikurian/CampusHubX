<?php
session_start();
include("../Booksharing/db_connect.php");

$cart = $_SESSION['cart'] ?? [];
$total = 0;
foreach ($cart as $c)
    $total += $c['price'];

$coupon = "CPN" . rand(10000, 99999);
$user = "sakshi";

$conn->query("
INSERT INTO canteen_orders (user_name, item_id, amount, payment_status, coupon_code)
VALUES ('$user',1,'$total','Paid','$coupon')
");

unset($_SESSION['cart']);
?>

<h2>🎉 Payment Successful</h2>
<h3>Your Coupon: <b><?= $coupon ?></b></h3>
<p>Show this at canteen counter</p>

<a href="index.php">Back to Menu</a>