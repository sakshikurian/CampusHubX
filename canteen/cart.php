<?php
session_start();
include "../includes/db.php";

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

    header("Location: cart.php");
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
    $qty = max(1, (int) $_POST['qty']);
    $_SESSION['cart'][$index]['qty'] = $qty;
}

/* -------- CLEAR CART -------- */
if (isset($_GET['clear'])) {
    unset($_SESSION['cart']);
    header("Location: cart.php");
    exit();
}

/* -------- CHECK STOCK STATUS -------- */

if (isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $k => $item) {

        $name = $item['item'];

        $res = $conn->query("SELECT status FROM menu WHERE item_name='$name'");
        $row = $res->fetch_assoc();

        if ($row && $row['status'] == "unavailable") {
            $_SESSION['cart'][$k]['unavailable'] = true;
        }
    }
}

$cart = $_SESSION['cart'] ?? [];
$total = 0;
$blocked = false;
?>

<!DOCTYPE html>
<html>

<head>
    <title>Your Cart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        :root {
            --brand: #0f4c81;
            --brand-dark: #0a2f4f;
            --brand-soft: #e9f4ff;
            --accent: #ff9f1c;
            --success-soft: #e9f8f1;
            --danger-soft: #ffe8ea;
            --surface: rgba(255, 255, 255, 0.9);
            --text-main: #16324f;
            --text-muted: #5f7488;
            --border-soft: rgba(15, 76, 129, 0.12);
            --shadow-soft: 0 18px 40px rgba(15, 76, 129, 0.12);
        }
        .navbar-shell {
            background: linear-gradient(120deg, var(--brand-dark), var(--brand));
            box-shadow: 0 14px 30px rgba(10, 47, 79, 0.24);
        }

        .brand-mark {
            width: 44px;
            height: 44px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 14px;
            background: rgba(255, 255, 255, 0.12);
            font-size: 1.2rem;
        }
        </style>
</head>

<body style="background:#f4f6f9">



   <nav class="navbar navbar-expand-lg navbar-dark navbar-shell py-3">
        <div class="container">
            <div class="d-flex align-items-center gap-3">
                <div class="brand-mark">YC</div>
                <div>
                    <a class="navbar-brand fw-bold mb-0" href="index.php">Your Cart</a>
                    <div class="small text-white-50">Manage your items</div>
                </div>
            </div>

            <div class="d-flex align-items-center gap-3 text-white mt-3 mt-lg-0">
                <button id="darkModeToggle" class="btn btn-outline-light me-2">
                    🌙
                </button>
                
            </div>
        </div>
    </nav>
    <div class="container mt-5">
       <div class="d-flex justify-content-end mb-4">
    <?php if (!empty($cart)) { ?>
        <a href="cart.php?clear=1" class="btn btn-outline-danger">
            🗑 Clear
        </a>
    <?php } ?>
</div>

        <?php if (empty($cart)) { ?>

            <div class="alert alert-warning text-center">
                Cart is empty 🛒
            </div>

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

                                $isUnavailable = !empty($c['unavailable']);

                                if ($isUnavailable) {
                                    $blocked = true;
                                }

                                ?>

                                <tr class="<?= $isUnavailable ? 'table-secondary' : '' ?>">

                                    <td>
                                        <?= htmlspecialchars($c['item']) ?>

                                        <?php if ($isUnavailable) { ?>
                                            <br>
                                            <span class="badge bg-danger">Out of Stock</span>
                                        <?php } ?>

                                    </td>

                                    <td>₹<?= number_format($price, 2) ?></td>

                                    <!-- QTY CONTROL -->

                                    <td>

                                        <?php if ($isUnavailable) { ?>

                                            <span class="text-danger fw-bold">Unavailable</span>

                                        <?php } else { ?>

                                            <form method="POST" class="d-flex justify-content-center align-items-center">

                                                <input type="hidden" name="index" value="<?= $i ?>">

                                                <button type="submit" name="update_qty"
                                                    onclick="this.form.qty.value = Math.max(1, parseInt(this.form.qty.value) - 1)"
                                                    class="btn btn-sm btn-outline-secondary">−</button>

                                                <input type="text" name="qty" value="<?= $qty ?>" readonly
                                                    class="form-control text-center mx-2" style="width:50px;font-weight:bold;">

                                                <button type="submit" name="update_qty"
                                                    onclick="this.form.qty.value = parseInt(this.form.qty.value) + 1"
                                                    class="btn btn-sm btn-outline-secondary">+</button>

                                            </form>

                                        <?php } ?>

                                    </td>

                                    <td>₹<?= number_format($rowTotal, 2) ?></td>

                                    <td>
                                        <a href="cart.php?remove=<?= $i ?>" class="btn btn-sm ">✖</a>
                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        </tbody>

                    </table>


                    <div class="text-end mt-3">
                        <h4>Total Amount:
                            <span class="text-success">₹<?= number_format($total, 2) ?></span>
                        </h4>
                    </div>


                    <div class="text-end mt-3">

                        <?php if ($blocked) { ?>

                            <button class="btn btn-secondary btn-lg" disabled>
                                Remove Out‑of‑Stock Items
                            </button>

                        <?php } else { ?>

                            <a href="payment.php?amount=<?= $total ?>" class="btn btn-success btn-lg">
                                💳 Proceed to Payment
                            </a>

                        <?php } ?>

                    </div>

                </div>
            </div>

        <?php } ?>

    </div>
    <script src="../js/darkmode.js"></script>
</body>

</html>