<?php
session_start();

include "../includes/db.php";
$total = $_GET['amount'] ?? 0;


if (isset($_POST['submit_payment'])) {

    $name = $_POST['name'];
    $user_id = $_SESSION['user_id'] ?? 1; // change if you store login id

    $file = $_FILES['payment_ss'];

    $filename = time() . "_" . $file['name'];

    move_uploaded_file($file['tmp_name'], $_SERVER['DOCUMENT_ROOT'] . "/images/" . $filename);

    /* GET TODAY TOKEN NUMBER */

    $res = $conn->query("
    SELECT COUNT(*) as total 
    FROM orders 
    WHERE DATE(order_date) = CURDATE()
    ");

    $row = $res->fetch_assoc();

    $token_no = $row['total'] + 1;
    /* INSERT ORDER */

    $sql = "INSERT INTO orders 
(user_id,name,amount,payment_ss,status,token_no)
VALUES 
('$user_id','$name','$total','$filename','pending','$token_no')";

    if (!$conn->query($sql)) {
        echo "Database Error: " . $conn->error;
    } else {
        echo "Order saved successfully";
    }
    $order_id = $conn->insert_id;

    /* SAVE CART ITEMS */

    if (isset($_SESSION['cart'])) {

        foreach ($_SESSION['cart'] as $item) {

            $item_name = $item['item'];
            $qty = $item['qty'];

            $conn->query("
            INSERT INTO order_items (order_id,item_name,qty)
            VALUES ('$order_id','$item_name','$qty')
            ");

        }
    }
    /* CLEAR CART AFTER ORDER */

    unset($_SESSION['cart']);

    header("Location: index.php");
    exit();

}
?>

<!DOCTYPE html>
<html>

<head>

    <title>Payment</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body style="background:#f4f6f9">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand mb-2 fw-150" href="../canteen/cart.php">
                ⬅ Back to Cart
            </a>
        </div>
    </nav>
    <div class="container mt-5">

        <div class="card shadow p-4 text-center">

            <h3 class="mb-4">Scan QR & Pay</h3>
            <center><img src="QR.jpeg" width="250" class="mb-3">
            </center>

            <h4 class="text-success mb-4">
                Amount: ₹<?= number_format($total, 2) ?>
            </h4>

            <hr>

            <form method="POST" enctype="multipart/form-data">

                <div class="mb-3 text-start">

                    <label class="form-label">Your Name</label>

                    <input type="text" name="name" class="form-control" required>

                </div>

                <div class="mb-3 text-start">

                    <label class="form-label">Upload Payment Screenshot</label>

                    <input type="file" name="payment_ss" class="form-control" required>

                </div>

                <button type="submit" name="submit_payment" class="btn btn-success w-100">
                    Submit Payment
                </button>

            </form>

        </div>

    </div>

</body>

</html>