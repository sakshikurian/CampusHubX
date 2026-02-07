<?php
session_start();
$total = 0;
foreach ($_SESSION['cart'] as $c) {
    $total += $c['price'];
}
?>

<script src="https://checkout.razorpay.com/v1/checkout.js"></script>

<button onclick="payNow()">Processing Payment...</button>

<script>
    function payNow() {
        var options = {
            "key": "YOUR_KEY_ID",
            "amount": "<?= $total * 100 ?>",
            "currency": "INR",
            "name": "CampusHubX Canteen",
            "description": "Cart Payment",
            "handler": function (response) {
                window.location = "coupon.php?pay_id=" + response.razorpay_payment_id;
            }
        };
        var rzp = new Razorpay(options);
        rzp.open();
    }
    payNow();
</script>