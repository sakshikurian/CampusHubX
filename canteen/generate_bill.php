<?php
require_once '../includes/session.php';
require('fpdf/fpdf.php');
include "../includes/db.php";

/* GET ORDER ID FROM URL */
$order_id = $_GET['id'] ?? 0;

if (!$order_id) {
    die("Invalid Order");
}

/* GET ORDER DETAILS */
$order = $conn->query("SELECT * FROM orders WHERE order_id=$order_id")->fetch_assoc();

/* CREATE PDF */
$pdf = new FPDF();
$pdf->AddPage();

/* ---------- HEADER ---------- */
$pdf->SetFont('Arial', 'B', 18);
$pdf->Cell(0, 10, 'CampusHubX Canteen', 0, 1, 'C');

$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 6, 'Food Pickup Coupon', 0, 1, 'C');

$pdf->Ln(5);

/* ---------- TOKEN DISPLAY ---------- */
$pdf->SetFont('Arial', 'B', 20);
$pdf->Cell(0, 12, 'TOKEN #' . $order['token_no'], 0, 1, 'C');

$pdf->Ln(5);

/* ---------- ORDER INFO ---------- */
$pdf->SetFont('Arial', '', 11);
$pdf->Cell(0, 6, 'Order ID: ' . $order_id, 0, 1);
$pdf->Cell(0, 6, 'Name: ' . $order['name'], 0, 1);
$pdf->Cell(0, 6, 'Date: ' . $order['order_date'], 0, 1);
$pdf->Cell(0, 6, 'Time: ' . $order['order_time'], 0, 1);

$pdf->Ln(5);

/* ---------- TABLE HEADER ---------- */
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(80, 8, 'Item', 1);
$pdf->Cell(30, 8, 'Price', 1);
$pdf->Cell(30, 8, 'Qty', 1);
$pdf->Cell(30, 8, 'Total', 1);
$pdf->Ln();

/* ---------- GET ORDER ITEMS ---------- */
$items = $conn->query("
SELECT oi.item_name, oi.qty, m.price
FROM order_items oi
JOIN menu m ON m.item_name = oi.item_name
WHERE oi.order_id = $order_id
");

$pdf->SetFont('Arial', '', 11);

$grandTotal = 0;

while ($item = $items->fetch_assoc()) {

    $price = $item['price'];
    $qty = $item['qty'];
    $total = $price * $qty;

    $grandTotal += $total;

    $pdf->Cell(80, 8, $item['item_name'], 1);
    $pdf->Cell(30, 8, "Rs " . $price, 1);
    $pdf->Cell(30, 8, $qty, 1);
    $pdf->Cell(30, 8, "Rs " . $total, 1);
    $pdf->Ln();
}

/* ---------- GRAND TOTAL ---------- */

$pdf->Ln(3);

$pdf->SetFont('Arial', 'B', 12);

$pdf->Cell(140, 10, 'Grand Total', 1);
$pdf->Cell(30, 10, "Rs " . $grandTotal, 1);

/* ---------- FOOTER ---------- */

$pdf->Ln(10);

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, 'Show this coupon at the counter.', 0, 1, 'C');

$pdf->Ln(5);

$pdf->SetFont('Arial', '', 10);
$pdf->Cell(0, 6, 'CampusHubX Canteen', 0, 1, 'C');

/* ---------- OUTPUT ---------- */

$pdf->Output('I', 'Coupon_Token_' . $order['token_no'] . '.pdf');

?>