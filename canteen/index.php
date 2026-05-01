<?php
session_start();
include "../includes/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}
$userId = $_SESSION['user_id'] ?? 0;
$userName = $_SESSION['user_name'] ?? "User";


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

if ($search != "") {
    $query .= " AND item_name LIKE '%$search%'";
}

if ($category != "") {
    $query .= " AND category='$category'";
}

$result = $conn->query("
SELECT m.*
FROM menu m
LEFT JOIN (
    SELECT item_name, SUM(qty) as total_orders
    FROM order_items
    JOIN orders ON orders.order_id = order_items.order_id
    WHERE orders.status='approved'
    GROUP BY item_name
) o ON m.item_name = o.item_name
ORDER BY o.total_orders DESC
");

/* ---------- CART COUNT ---------- */
$cartCount = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
?>

<!DOCTYPE html>
<html>

<head>
     <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Canteen</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        
html {
    scroll-behavior: smooth;
}
        body {
            background-color: #cfe2f3;
            overflow-x: hidden;
            
 padding-top: 110px; 
        }
        
input, button {
    min-height: 45px;
}
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
        .category-link {
            padding: 10px 15px;
            border-radius: 12px;
            display: block;
            margin-bottom: 8px;
            text-decoration: none;
            color: #333;
            transition: all 0.3s ease;
        }
        .btn-brand {
            background: var(--brand);
            border-color: var(--brand);
            color: #fff;
        }
         .btn-brand:hover {
            background: #0c3f6b;
            border-color: #0c3f6b;
            color: #fff;
        }
        .category-sidebar {
    

    align-self: flex-start;
    height: fit-content;
    z-index: 10; /* prevents overlap issues */
}
.category-box {
 position: fixed;
    top: 110px; /* same as navbar */
    left: 0;
    width: 16.66%; /* same as col-md-2 */
    padding-left: 10px;
    padding-right: 10px;
}

        .category-link:hover {
            background: #0d6efd;
            color: white;
            transform: translateX(6px);
        }

 .navbar-shell {
            background: linear-gradient(120deg, var(--brand-dark), var(--brand));
            box-shadow: 0 14px 30px rgba(10, 47, 79, 0.24);
            position: fixed;
    top: 0;
    width: 100%;
    z-index: 1000;
        }
.food-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 20px 40px rgba(0,0,0,0.15);
}

        .floating-cart {
            position: fixed;
            bottom: 25px;
            left: auto;
            width:auto;
            right: 25px;
            background: white;
            color: #0d6efd;
            padding: 12px 22px;
            border-radius: 30px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 16px;
            font-weight: bold;
            text-decoration: none;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
            transition: all 0.2s ease;
            z-index: 1000;
        }

        .floating-cart:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.25);
        }

        .cart-badge {
            background-color: #0d6efd;
            color: white;
            font-size: 15px;
            padding: 4px 8px;
            border-radius: 50px;
            font-weight: bold;
        }

        .popular-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: #454541;
            color: white;
            padding: 5px 10px;
            font-size: 12px;
            border-radius: 20px;
            font-weight: bold;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }

        body.dark-mode {
            background-color: #3e3d3d !important;
            color: #ffffff !important;
        }

        /* Only for titles (Categories + My Orders) */
        .bullet-title {
            position: relative;
            padding-left: 15px;
        }

        /* Bullet only for these */
        .bullet-title::before {
            position: absolute;
            left: 0;
            color: #0d6efd;
            font-size: 18px;
        }

        /* PAGE TRANSITION */
        body {
            opacity: 0;
            transition: opacity 0.4s ease-in-out;
        }

        body.page-loaded {
            opacity: 1;
        }
        @media (max-width: 768px) {
    .category-link {
        display: inline-block;
        margin-right: 10px;

    }
    .menu-scroll {
        margin-left: 16.66%;
    }
   
    .category-container {
        flex-direction: row;
        overflow-x: auto;
        gap: 10px;
        padding-bottom: 5px;
    }

    .category-container::-webkit-scrollbar {
        display: none; /* hide scrollbar */
    }
}
        
    .category-container {
       display: flex;
    flex-direction: column; /* vertical on desktop */
    gap: 10px;
    }
    .food-card {
         border-radius: 14px;
    padding: 10px;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    
    }

    .food-card h5 {
        font-size: 16px;
    }

    .food-card h6 {
        font-size: 14px;
    }
    
    
.row {
    align-items: flex-start;
}

.mb-4 {
    position: sticky;
    top: 90px; /* same as navbar */
    z-index: 10;
    background: #cfe2f3; /* same as body */
    padding-top: 5px;
}
.menu-scroll {
    height: calc(100vh - 110px); /* increase this */
    padding-top: 10px; 
    overflow-y: auto;

    scrollbar-width: none; /* Firefox */
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
.menu-scroll::-webkit-scrollbar {
    display: none; /* Chrome, Safari */
}

.food-item {
    animation: fadeUp 0.5s ease forwards;
}

@keyframes fadeUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
* {
    -webkit-overflow-scrolling: touch;
}
    </style>

    <script>
        function updatePrice(id, price) {
            let qty = document.getElementById("qty" + id).value;
            document.getElementById("total" + id).innerText = "₹" + (price * qty);
        }

        
    </script>
    <script>window.addEventListener("scroll", () => {
    document.querySelectorAll(".food-card").forEach(card => {
        let rect = card.getBoundingClientRect();

        if (rect.top < window.innerHeight - 50) {
            card.style.transform = "translateY(0)";
            card.style.opacity = "1";
        } else {
            card.style.transform = "translateY(40px)";
            card.style.opacity = "0.5";
        }
    });
});</script>
</head>

<body>
   <nav class="navbar navbar-expand-lg navbar-dark navbar-shell py-3">
        <div class="container">
            <div class="d-flex align-items-center gap-3">
                <div class="brand-mark">DCC</div>
                <div>
                    <a class="navbar-brand fw-bold mb-0" href="../dashboard.php">Digital Canteen Coupon</a>
                    <div class="small text-white-50">Add cart, scan Qr code, avoid long queue. </div>
                </div>
            </div>
            <div class="d-flex align-items-center gap-3 text-white mt-3 mt-lg-0">
                   <button id="darkModeToggle" class="btn btn-outline-light me-2">
                    🌙
                </button>
                <div class="text-end">
                    <div class="fw-semibold">Welcome back, <?= htmlspecialchars($userName) ?></div>
                    <div class="small text-white-50">Community learning dashboard</div>
                </div>
                <a href="../logout.php" class="btn btn-outline-light btn-sm rounded-pill px-3">Logout</a>
            </div>
        </div>
    </nav>


    <!-- PAGE CONTENT -->
    <div class="container-fluid mt-4">
        <div class="row">

            <!-- LEFT SIDEBAR -->
            <div class="col-12 col-md-2 mb-3 category-sidebar">
    <div class="bg-white p-3 rounded-4 shadow-sm category-box">
    <h6 class="fw-bold mb-3 text-dark bullet-title">Categories</h6>

    <div class="category-container">
        <a href="?category=Breakfast" class="category-link">Breakfast</a>
        <a href="?category=Lunch" class="category-link">Lunch</a>
        <a href="?category=Snacks" class="category-link">Snacks</a>
        <a href="?category=Drinks" class="category-link">Drinks</a>
        <a href="index.php" class="category-link">All</a>
    </div>

    <a href="order_history.php" class="category-link fw-bold bullet-title">My Orders</a>
</div>
            </div>

            <!-- RIGHT CONTENT -->
            <div class="col-md-10">

                <!-- SEARCH -->
                <div class="mb-4">
                    <form method="GET">
                        <input type="text" id="liveSearch" name="search" value="<?= htmlspecialchars($search) ?>"
                            class="form-control rounded-pill shadow-sm px-4" placeholder="🔍 Search food..."
                            style="height:45px;">
                    </form>
                </div>

                <!-- FOOD GRID -->

                <div class="row g-4 menu-scroll" >

                    <?php $i = 0; ?>

                    <?php while ($row = $result->fetch_assoc()) { ?>

                        <div class="col-12 col-sm-6 col-md-4 food-item" data-name="<?= strtolower($row['item_name']) ?>">

                            <?php if ($row['status'] == "unavailable") { ?>
                                <div class="card shadow-sm food-card h-100 border-0 opacity-50 position-relative"
                                    style="pointer-events:none;">
                                <?php } else { ?>
                                    <div class="card shadow-sm food-card h-100 border-0 position-relative">
                                    <?php } ?>

                                    <?php if ($i < 5) { ?>
                                        <div class="popular-badge"> Popular</div>
                                    <?php } ?>

                                    <div class="card-body text-center">

                                        <h5><?= $row['item_name'] ?></h5>

                                        <h6 class="text-primary fw-bold mb-3">
                                            ₹<?= number_format($row['price'], 2) ?>
                                        </h6>

                                        <form method="POST">

                                            <input type="hidden" name="item"
                                                value="<?= htmlspecialchars($row['item_name']) ?>">

                                            <input type="hidden" name="price" value="<?= $row['price'] ?>">

                                            <label class="small text-muted">Qty</label>

                                            <input type="number" id="qty<?= $row['item_id'] ?>" name="qty" value="1" min="1"
                                                class="form-control text-center rounded-pill mb-2"
                                                onchange="updatePrice(<?= $row['item_id'] ?>, <?= $row['price'] ?>)">

                                            <small class="text-muted d-block mb-2">
                                                Total:
                                                <span id="total<?= $row['item_id'] ?>">
                                                    ₹<?= number_format($row['price'], 2) ?>
                                                </span>
                                            </small>

                                            <?php if ($row['status'] == "available") { ?>

                                                <button class="btn btn-primary w-100 rounded-pill fw-semibold">
                                                    🛒 Add to Cart
                                                </button>

                                            <?php } else { ?>

                                                <button class="btn btn-secondary w-100 rounded-pill" disabled>
                                                    Out of Stock
                                                </button>

                                            <?php } ?>

                                        </form>

                                    </div>
                                </div>
                            </div>

                            <?php $i++; ?>

                        <?php } ?>

                    </div>
                </div>
            </div>

            <!-- FLOATING CART -->
            <a href="cart.php" class="floating-cart">
                🛒 Cart
                <span class="cart-badge"><?= $cartCount ?></span>
            </a>
            <script>
                document.getElementById("liveSearch").addEventListener("keyup", function () {
                    let value = this.value.toLowerCase();
                    let items = document.querySelectorAll(".food-item");

                    items.forEach(function (item) {
                        let name = item.getAttribute("data-name");

                        if (name.includes(value)) {
                            item.style.display = "block";
                        } else {
                            item.style.display = "none";
                        }
                    });
                });
            </script>
            <script src="../js/darkmode.js"></script>
            <script>
                window.addEventListener("load", () => {
                    document.body.classList.add("page-loaded");
                });
            </script>
            <script>
                function loadNotifications() {
                    fetch("fetch_notify.php")
                        .then(response => response.text())
                        .then(data => {
                            document.getElementById("notifBox").innerHTML = data;
                        });
                }

                // load once
                loadNotifications();

                // auto refresh every 5 seconds
                setInterval(loadNotifications, 5000);
            </script>
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>