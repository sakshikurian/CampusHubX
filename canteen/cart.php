<?php
require_once '../includes/session.php';
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
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta charset="UTF-8">
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
            --dark-surface: #172633;
            --dark-surface-2: #203442;
            --dark-text: #f5fbff;
            --dark-muted: #b8c7d3;
            --dark-accent: #ffb703;
            --dark-border: rgba(255, 255, 255, 0.14);
        }

        body {
            background: #f4f6f9;
            min-height: 100vh;
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

        .cart-shell {
            max-width: 980px;
        }

        .cart-panel {
            border: 1px solid var(--border-soft);
            border-radius: 18px;
            overflow: hidden;
            box-shadow: var(--shadow-soft);
        }

        .qty-form {
            gap: 8px;
        }

        .qty-input {
            width: 56px;
            font-weight: 700;
        }

        body.dark-mode {
            background:
                radial-gradient(circle at top left, rgba(255, 183, 3, 0.14), transparent 28%),
                linear-gradient(145deg, #101820 0%, #15222d 48%, #0d151c 100%) !important;
            color: var(--dark-text);
        }

        body.dark-mode .navbar-shell {
            background: linear-gradient(120deg, #08111a, #16384c);
            box-shadow: 0 16px 38px rgba(0, 0, 0, 0.42);
        }

        body.dark-mode .cart-panel,
        body.dark-mode .card {
            background: linear-gradient(180deg, var(--dark-surface), var(--dark-surface-2));
            color: var(--dark-text);
            border-color: var(--dark-border);
        }

        body.dark-mode .table {
            --bs-table-bg: transparent;
            --bs-table-color: var(--dark-text);
            --bs-table-border-color: var(--dark-border);
            color: var(--dark-text);
        }

        body.dark-mode .table-light {
            --bs-table-bg: rgba(255, 183, 3, 0.13);
            --bs-table-color: var(--dark-text);
        }

        body.dark-mode .form-control {
            background: #f7fbff;
            color: #10202b;
            border-color: rgba(255, 183, 3, 0.35);
        }

        body.dark-mode .btn-outline-secondary,
        body.dark-mode .btn-outline-danger {
            color: var(--dark-text);
            border-color: var(--dark-border);
        }

        body.dark-mode .text-success {
            color: var(--dark-accent) !important;
        }

        body.dark-mode .alert-warning {
            background: rgba(255, 183, 3, 0.14);
            color: var(--dark-text);
            border-color: rgba(255, 183, 3, 0.35);
        }

        @media (max-width: 768px) {
            .navbar-shell .container {
                align-items: flex-start;
                gap: 12px;
            }

            .brand-mark {
                width: 40px;
                height: 40px;
                border-radius: 10px;
                font-size: 0.9rem;
            }

            .navbar-brand {
                font-size: 1rem;
            }

            .cart-shell {
                margin-top: 1.5rem !important;
            }

            .cart-actions {
                justify-content: stretch !important;
            }

            .cart-actions .btn,
            .checkout-actions .btn {
                width: 100%;
            }

            .cart-table thead {
                display: none;
            }

            .cart-table,
            .cart-table tbody,
            .cart-table tr,
            .cart-table td {
                display: block;
                width: 100%;
            }

            .cart-table tr {
                border: 1px solid var(--border-soft);
                border-radius: 14px;
                margin-bottom: 14px;
                padding: 12px;
                background: rgba(255, 255, 255, 0.72);
            }

            body.dark-mode .cart-table tr {
                background: rgba(16, 24, 32, 0.7);
                border-color: var(--dark-border);
            }

            .cart-table td {
                border: 0;
                text-align: left !important;
                padding: 8px 0;
            }

            .cart-table td::before {
                content: attr(data-label);
                display: block;
                font-size: 0.78rem;
                font-weight: 800;
                color: var(--text-muted);
                text-transform: uppercase;
                margin-bottom: 4px;
            }

            body.dark-mode .cart-table td::before {
                color: var(--dark-muted);
            }

            .qty-form {
                justify-content: flex-start !important;
            }

            .total-line,
            .checkout-actions {
                text-align: left !important;
            }
        }
        </style>
</head>

<body>



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
    <div class="container cart-shell mt-5">
       <div class="d-flex justify-content-end mb-4 cart-actions">
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

            <div class="card cart-panel">

                <div class="card-body">

                    <table class="table table-bordered text-center align-middle cart-table">

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

                                    <td data-label="Item">
                                        <?= htmlspecialchars($c['item']) ?>

                                        <?php if ($isUnavailable) { ?>
                                            <br>
                                            <span class="badge bg-danger">Out of Stock</span>
                                        <?php } ?>

                                    </td>

                                    <td data-label="Price">₹<?= number_format($price, 2) ?></td>

                                    <!-- QTY CONTROL -->

                                    <td data-label="Qty">

                                        <?php if ($isUnavailable) { ?>

                                            <span class="text-danger fw-bold">Unavailable</span>

                                        <?php } else { ?>

                                            <form method="POST" class="d-flex justify-content-center align-items-center qty-form">

                                                <input type="hidden" name="index" value="<?= $i ?>">

                                                <button type="submit" name="update_qty"
                                                    onclick="this.form.qty.value = Math.max(1, parseInt(this.form.qty.value) - 1)"
                                                    class="btn btn-sm btn-outline-secondary">−</button>

                                                <input type="text" name="qty" value="<?= $qty ?>" readonly
                                                    class="form-control text-center qty-input">

                                                <button type="submit" name="update_qty"
                                                    onclick="this.form.qty.value = parseInt(this.form.qty.value) + 1"
                                                    class="btn btn-sm btn-outline-secondary">+</button>

                                            </form>

                                        <?php } ?>

                                    </td>

                                    <td data-label="Total">₹<?= number_format($rowTotal, 2) ?></td>

                                    <td data-label="Remove">
                                        <a href="cart.php?remove=<?= $i ?>" class="btn btn-sm btn-outline-danger rounded-pill">Remove</a>
                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        </tbody>

                    </table>


                    <div class="text-end mt-3 total-line">
                        <h4>Total Amount:
                            <span class="text-success">₹<?= number_format($total, 2) ?></span>
                        </h4>
                    </div>


                    <div class="text-end mt-3 checkout-actions">

                        <?php if ($blocked) { ?>

                            <button class="btn btn-secondary btn-lg" disabled>
                                Remove Out‑of‑Stock Items
                            </button>

                        <?php } else { ?>

                            <a href="payment.php?amount=<?= $total ?>" class="btn btn-success btn-lg">
                                Proceed to Payment
                            </a>

                        <?php } ?>

                    </div>

                </div>
            </div>

        <?php } ?>

    </div>
    <script>
        const darkModeToggleBtn = document.getElementById("darkModeToggle");
        if (localStorage.getItem("darkMode") === "enabled") {
            document.body.classList.add("dark-mode");
        }
        if (darkModeToggleBtn) {
            darkModeToggleBtn.textContent = document.body.classList.contains("dark-mode") ? "☀️" : "🌙";
            darkModeToggleBtn.addEventListener("click", () => {
                document.body.classList.toggle("dark-mode");
                const enabled = document.body.classList.contains("dark-mode");
                localStorage.setItem("darkMode", enabled ? "enabled" : "disabled");
                darkModeToggleBtn.textContent = enabled ? "☀️" : "🌙";
            });
        }
    </script>
</body>

</html>
