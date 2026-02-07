<?php
session_start();
$_SESSION['item'] = $_POST['item'];
$_SESSION['amount'] = $_POST['price'];
?>

<script src="https://checkout.razorpay.com/v1/checkout.js"></script>

<button onclick="payNow()">Processing Payment...</button>

<script>
    function payNow() {
        var options = {
            "key": "YOUR_KEY_ID",
            "amount": "<?= $_SESSION['amount'] * 100 ?>",
            "currency": "INR",
            "name": "CampusHubX Canteen",
            "description": "Food Order",
            "handler": function (response) {
                window.location = "coupon.php?pay_id=" + response.razorpay_payment_id;
            }
        };
        var rzp = new Razorpay(options);
        rzp.open();
    }
    payNow();
</script>