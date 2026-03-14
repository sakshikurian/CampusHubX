<?php
session_start();
require('fpdf/fpdf.php');

$cart = $_SESSION['cart'] ?? [];
if (empty($cart)) {
    die("Cart empty");
}

$pdf = new FPDF();
$pdf->AddPage();

/* ---------- HEADER ---------- */
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, 'CampusHubX Canteen', 0, 1, 'C');

$pdf->SetFont('Arial', '', 11);
$pdf->Cell(0, 6, 'Campus Food Ordering System', 0, 1, 'C');
$pdf->Ln(5);

$pdf->SetFont('Arial', '', 10);
$pdf->Cell(0, 6, 'Date: ' . date("d M Y, h:i A"), 0, 1, 'R');

$pdf->Ln(5);

/* ---------- TABLE HEADER ---------- */
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(80, 8, 'Item', 1);
$pdf->Cell(30, 8, 'Price', 1);
$pdf->Cell(20, 8, 'Qty', 1);
$pdf->Cell(30, 8, 'Total', 1);
$pdf->Ln();

/* ---------- ITEMS ---------- */
$pdf->SetFont('Arial', '', 11);
$grandTotal = 0;

foreach ($cart as $c) {
    $item = $c['item'];
    $price = $c['price'];
    $qty = $c['qty'];
    $total = $price * $qty;
    $grandTotal += $total;

    $pdf->Cell(80, 8, $item, 1);
    $pdf->Cell(30, 8, "Rs " . $price, 1);
    $pdf->Cell(20, 8, $qty, 1);
    $pdf->Cell(30, 8, "Rs " . $total, 1);
    $pdf->Ln();
}

/* ---------- TOTAL ---------- */
$pdf->Ln(5);
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(130, 10, 'Grand Total', 1);
$pdf->Cell(30, 10, "Rs " . $grandTotal, 1);

$pdf->Ln(15);

/* ---------- FOOTER ---------- */
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(0, 6, 'Thank you for ordering!', 0, 1, 'C');
$pdf->Cell(0, 6, 'CampusHubX', 0, 1, 'C');

/* ---------- CLEAR CART AFTER BILL ---------- */
unset($_SESSION['cart']);

/* ---------- OUTPUT ---------- */
$pdf->Output('I', 'Receipt.pdf');
?>