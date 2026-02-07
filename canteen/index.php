<?php
session_start();
include "../includes/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

/* ---------- ADD TO CART ---------- */
if (isset($_POST['item'])) {

    $item = $_POST['item'];
    $price = $_POST['price'];
    $qty = $_POST['qty'] ?? 1;

    if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    $found = false;

    foreach ($_SESSION['cart'] as &$cartItem) {
        if ($cartItem['item'] == $item) {
            $cartItem['qty'] += $qty;
            $found = true;
            break;
        }
    }
    unset($cartItem);

    if (!$found) {
        $_SESSION['cart'][] = [
            "item" => $item,
            "price" => $price,
            "qty" => $qty
        ];
    }

    header("Location: index.php");
    exit();
}

/* ---------- FETCH MENU ---------- */
$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';

$query = "SELECT * FROM menu WHERE 1";

if ($search != "") {
    $query .= " AND item_name LIKE '%$search%'";
}
if ($category != "") {
    $query .= " AND category='$category'";
}

$result = $conn->query($query);

/* ---------- CART COUNT ---------- */
$cartCount = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
?>

<!DOCTYPE html>
<html>

<head>
    <title>Canteen</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <script>
        function updatePrice(id, price) {
            let qty = document.getElementById("qty" + id).value;
            document.getElementById("total" + id).innerText = "₹" + (price * qty);
        }
    </script>
</head>

<body style=" background-color: #cfe2f3;">

    <!-- ================= NAVBAR (FULL WIDTH) ================= -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">

            <!-- LEFT -->
            <a class="navbar-brand fw-bold" href="../dashboard.php">
                CampusHubX
            </a>

            <!-- RIGHT -->
            <div class="ms-auto d-flex align-items-center gap-3">


                <span class="text-white me-3">Welcome,
                    <?= htmlspecialchars($_SESSION['user_name']); ?>!
                </span>
                <a href="cart.php" class="btn btn-outline-light btn-sm">
                    🛒 Cart (<?= $cartCount ?>)
                </a>

            </div>
        </div>
    </nav>


    <!-- ================= PAGE CONTENT ================= -->
    <div class="container mt-4">

        <!-- SEARCH -->
        <form class="mb-3">
            <input type="text" name="search" class="form-control" placeholder="Search food..."
                value="<?= htmlspecialchars($search) ?>">
        </form>

        <!-- CATEGORY -->
        <div class="mb-3">
            <a href="?category=Breakfast" class="btn btn-outline-primary">Breakfast</a>
            <a href="?category=Lunch" class="btn btn-outline-primary">Lunch</a>
            <a href="?category=Snacks" class="btn btn-outline-primary">Snacks</a>
            <a href="?category=Drinks" class="btn btn-outline-primary">Drinks</a>
            <a href="index.php" class="btn btn-outline-secondary">All</a>
        </div>

        <div class="row">

            <?php while ($row = $result->fetch_assoc()) { ?>

                <div class="col-md-4 mb-3">
                    <div class="card shadow-sm">
                        <div class="card-body text-center">

                            <h5><?= htmlspecialchars($row['item_name']) ?></h5>
                            <p>₹<?= number_format($row['price'], 2) ?></p>

                            <!-- ADD TO CART FORM -->
                            <form method="POST">

                                <input type="hidden" name="item" value="<?= htmlspecialchars($row['item_name']) ?>">
                                <input type="hidden" name="price" value="<?= $row['price'] ?>">

                                Qty:
                                <input type="number" id="qty<?= $row['item_id'] ?>" name="qty" value="1" min="1"
                                    class="form-control mb-2 text-center"
                                    onchange="updatePrice(<?= $row['item_id'] ?>, <?= $row['price'] ?>)">

                                <p>Total:
                                    <span id="total<?= $row['item_id'] ?>">
                                        ₹<?= number_format($row['price'], 2) ?>
                                    </span>
                                </p>

                                <button class="btn btn-primary w-100">
                                    🛒 Add to Cart
                                </button>

                            </form>

                        </div>
                    </div>
                </div>

            <?php } ?>

        </div>
    </div>

</body>

</html>