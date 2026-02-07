<?php
session_start();

/* ---------- ADD ITEM TO CART ---------- */
if (isset($_POST['item'])) {

    $item = $_POST['item'];
    $price = $_POST['price'];
    $qty = $_POST['qty'] ?? 1;

    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    $_SESSION['cart'][] = [
        "item" => $item,
        "price" => $price,
        "qty" => $qty
    ];

    header("Location: cart.php"); // prevent duplicate add on refresh
    exit();
}



/* -------- REMOVE ITEM -------- */
if (isset($_GET['remove'])) {
    $index = $_GET['remove'];
    unset($_SESSION['cart'][$index]);
    $_SESSION['cart'] = array_values($_SESSION['cart']);
    header("Location: cart.php");
    exit();
}

/* -------- UPDATE QTY -------- */
if (isset($_POST['update_qty'])) {
    $index = $_POST['index'];
    $qty = max(1, (int) $_POST['qty']); // minimum 1
    $_SESSION['cart'][$index]['qty'] = $qty;
}

/* -------- CLEAR CART -------- */
if (isset($_GET['clear'])) {
    unset($_SESSION['cart']);
    header("Location: cart.php");
    exit();
}

$cart = $_SESSION['cart'] ?? [];
$total = 0;
?>

<!DOCTYPE html>
<html>

<head>
    <title>Your Cart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body style="background:#f4f6f9">

    <div class="container mt-5">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3>🛒 Your Cart</h3>
            <div>
                <a href="index.php" class="btn btn-outline-secondary">← Back</a>
                <?php if (!empty($cart)) { ?>
                    <a href="cart.php?clear=1" class="btn btn-outline-danger ms-2">🗑 Clear</a>
                <?php } ?>
            </div>
        </div>

        <?php if (empty($cart)) { ?>

            <div class="alert alert-warning text-center">Cart is empty 🛒</div>

        <?php } else { ?>

            <div class="card shadow-sm">
                <div class="card-body">

                    <table class="table table-bordered text-center align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Item</th>
                                <th>Price</th>
                                <th width="180">Qty</th>
                                <th>Total</th>
                                <th></th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php foreach ($cart as $i => $c):
                                $price = $c['price'];
                                $qty = $c['qty'] ?? 1;
                                $rowTotal = $price * $qty;
                                $total += $rowTotal;
                                ?>
                                <tr>
                                    <td><?= htmlspecialchars($c['item']) ?></td>
                                    <td>₹<?= number_format($price, 2) ?></td>

                                    <!-- QTY CONTROL -->
                                    <td>
                                        <form method="POST" class="d-flex justify-content-center align-items-center">

                                            <input type="hidden" name="index" value="<?= $i ?>">

                                            <!-- MINUS -->
                                            <button type="submit" name="update_qty" value="1"
                                                onclick="this.form.qty.value = Math.max(1, parseInt(this.form.qty.value) - 1)"
                                                class="btn btn-sm btn-outline-secondary">
                                                −
                                            </button>

                                            <!-- QTY DISPLAY (NO ARROWS) -->
                                            <input type="text" name="qty" value="<?= $qty ?>" readonly
                                                class="form-control text-center mx-2" style="width:50px; font-weight:bold;">

                                            <!-- PLUS -->
                                            <button type="submit" name="update_qty" value="1"
                                                onclick="this.form.qty.value = parseInt(this.form.qty.value) + 1"
                                                class="btn btn-sm btn-outline-secondary">
                                                +
                                            </button>

                                        </form>
                                    </td>


                                    <td>₹<?= number_format($rowTotal, 2) ?></td>

                                    <td>
                                        <a href="cart.php?remove=<?= $i ?>" class="btn btn-sm btn-blue">✖</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <div class="text-end mt-3">
                        <h4>Total Amount: <span class="text-success">₹<?= number_format($total, 2) ?></span></h4>
                    </div>

                    <div class="text-end mt-3">
                        <a href="checkout.php" class="btn btn-success btn-lg">
                            💳 Proceed to Payment
                        </a>
                    </div>

                </div>
            </div>

        <?php } ?>

    </div>

</body>

</html>