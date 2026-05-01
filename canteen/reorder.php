<?php
require_once '../includes/session.php';
include "../includes/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

$orderId = (int) ($_GET['id'] ?? 0);
$userId = (int) $_SESSION['user_id'];

if ($orderId <= 0) {
    header("Location: order_history.php?reorder=invalid");
    exit();
}

$orderStmt = $conn->prepare("SELECT order_id FROM orders WHERE order_id = ? AND user_id = ?");
$orderStmt->bind_param("ii", $orderId, $userId);
$orderStmt->execute();
$orderResult = $orderStmt->get_result();

if ($orderResult->num_rows === 0) {
    header("Location: order_history.php?reorder=not_found");
    exit();
}

$itemsStmt = $conn->prepare("
    SELECT oi.item_name, oi.qty, m.price, m.status
    FROM order_items oi
    JOIN menu m ON m.item_name = oi.item_name
    WHERE oi.order_id = ?
");
$itemsStmt->bind_param("i", $orderId);
$itemsStmt->execute();
$itemsResult = $itemsStmt->get_result();

if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$addedCount = 0;
$skippedCount = 0;

while ($item = $itemsResult->fetch_assoc()) {
    if (($item['status'] ?? '') !== 'available') {
        $skippedCount++;
        continue;
    }

    $itemName = $item['item_name'];
    $qty = max(1, (int) $item['qty']);
    $price = (float) $item['price'];
    $found = false;

    foreach ($_SESSION['cart'] as &$cartItem) {
        if (($cartItem['item'] ?? '') === $itemName) {
            $cartItem['qty'] = (int) ($cartItem['qty'] ?? 0) + $qty;
            $cartItem['price'] = $price;
            $found = true;
            break;
        }
    }
    unset($cartItem);

    if (!$found) {
        $_SESSION['cart'][] = [
            "item" => $itemName,
            "price" => $price,
            "qty" => $qty
        ];
    }

    $addedCount += $qty;
}

$_SESSION['canteen_notice'] = $addedCount > 0
    ? "Previous order added to your cart. You can add more items before checkout."
    : "No available items from that order could be added.";

if ($skippedCount > 0) {
    $_SESSION['canteen_notice'] .= " Some unavailable items were skipped.";
}

header("Location: index.php?reorder=1");
exit();
?>
