<?php
require_once '../includes/session.php';
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
    $price = (float) ($_POST['price'] ?? 0);
    $qty = max(1, (int) ($_POST['qty'] ?? 1));

    if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    $found = false;

    foreach ($_SESSION['cart'] as &$cartItem) {
        if ($cartItem['item'] == $item) {
            $cartItem['qty'] = (int) ($cartItem['qty'] ?? 0) + $qty;
            $cartItem['price'] = $price;
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
$where = "WHERE 1";

if ($search != "") {
    $where .= " AND item_name LIKE '%$search%'";
}

if ($category != "") {
    $where .= " AND category='$category'";
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
$where
ORDER BY o.total_orders DESC
");
/* ---------- CART COUNT ---------- */
$cartCount = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
$canteenNotice = $_SESSION['canteen_notice'] ?? '';
unset($_SESSION['canteen_notice']);
?>

<!DOCTYPE html>
<html>

<head>
     <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta charset="UTF-8">
    <title>Canteen</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
html {
    scroll-behavior: smooth;
}

body {
    background-color: #cfe2f3;
    overflow-x: hidden;
    min-height: 100vh;
}

/* GLOBAL */
input, button {
    min-height: 45px;
}

:root {
    --brand: #0f4c81;
    --brand-dark: #0a2f4f;
    --dark-bg: #101820;
    --dark-surface: #172633;
    --dark-surface-2: #203442;
    --dark-text: #f5fbff;
    --dark-muted: #b8c7d3;
    --dark-accent: #ffb703;
    --dark-border: rgba(255,255,255,0.14);
}

/* NAVBAR */
.navbar-shell {
    background: linear-gradient(120deg, var(--brand-dark), var(--brand));
    box-shadow: 0 14px 30px rgba(10, 47, 79, 0.24);
    position: sticky;
    top: 0;
    width: 100%;
    z-index: 1000;
}

.brand-mark {
    width: 46px;
    height: 46px;
    border-radius: 12px;
    display: grid;
    place-items: center;
    flex: 0 0 auto;
    background: rgba(255,255,255,0.16);
    color: #fff;
    font-weight: 800;
    letter-spacing: 0;
}

.navbar-shell .container {
    gap: 16px;
}

.navbar-brand {
    white-space: normal;
    line-height: 1.15;
}

.nav-actions {
    flex-wrap: wrap;
    justify-content: flex-end;
}

.page-shell {
    padding: 24px 18px 92px;
}

/* SIDEBAR */
.category-sidebar {
    align-self: flex-start;
    position: sticky;
    top: 104px;
}

.category-container {
    padding: 10px;
    background: rgba(255,255,255,0.45);
    border-radius: 14px;
}

/* CATEGORY LINKS */
.category-link {
    padding: 10px 15px;
    border-radius: 12px;
    display: block;
    margin-bottom: 8px;
    text-decoration: none;
    color: #333;
    transition: 0.3s;
}

.category-link:hover {
    background: #0d6efd;
    color: white;
    transform: translateX(6px);
}

/* FOOD CARDS */
.food-card {
    border-radius: 14px;
    padding: 10px;
    transition: 0.3s;
    min-height: 100%;
}

.food-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 20px 40px rgba(0,0,0,0.15);
}

/* POPULAR BADGE */
.popular-badge {
    position: absolute;
    top: 10px;
    right: 10px;
    background: #454541;
    color: white;
    padding: 5px 10px;
    font-size: 12px;
    border-radius: 20px;
}

/* CART BUTTON */
.floating-cart {
    position: fixed;
    bottom: 25px;
    right: 25px;
    background: white;
    color: #0d6efd;
    padding: 12px 22px;
    border-radius: 30px;
    display: flex;
    align-items: center;
    gap: 10px;
    font-weight: bold;
    box-shadow: 0 8px 25px rgba(0,0,0,0.2);
    z-index: 1000;
    text-decoration: none;
}

.cart-badge {
    min-width: 26px;
    height: 26px;
    border-radius: 999px;
    display: inline-grid;
    place-items: center;
    background: #0d6efd;
    color: #fff;
    font-size: 13px;
}

/* SEARCH BAR */
.search-wrap {
    position: sticky;
    top: 88px;
    z-index: 10;
    background: #cfe2f3;
    padding-bottom: 12px;
}

/* SCROLL AREA */
.menu-scroll {
    max-height: calc(100vh - 178px);
    overflow-y: auto;
    padding-bottom: 18px;
}

.menu-scroll::-webkit-scrollbar {
    display: none;
}

body.dark-mode {
    background:
        radial-gradient(circle at top left, rgba(255, 183, 3, 0.14), transparent 28%),
        linear-gradient(145deg, #101820 0%, #15222d 48%, #0d151c 100%) !important;
    color: var(--dark-text) !important;
}

body.dark-mode .navbar-shell {
    background: linear-gradient(120deg, #08111a, #16384c);
    box-shadow: 0 16px 38px rgba(0,0,0,0.42);
}

body.dark-mode .search-wrap {
    background: transparent;
}

body.dark-mode #liveSearch,
body.dark-mode .form-control {
    background: #f7fbff;
    color: #10202b;
    border-color: rgba(255, 183, 3, 0.35);
}

body.dark-mode .category-container,
body.dark-mode .mobile-menu-box {
    background: rgba(23, 38, 51, 0.94);
    border: 1px solid var(--dark-border);
    box-shadow: 0 18px 42px rgba(0,0,0,0.3);
}

body.dark-mode .food-card {
    background: linear-gradient(180deg, var(--dark-surface), var(--dark-surface-2));
    color: var(--dark-text);
    border: 1px solid var(--dark-border) !important;
    box-shadow: 0 18px 36px rgba(0,0,0,0.24);
}

body.dark-mode .food-card h5,
body.dark-mode .bullet-title,
body.dark-mode .text-dark {
    color: var(--dark-text) !important;
}

body.dark-mode .food-card .text-primary {
    color: var(--dark-accent) !important;
}

body.dark-mode .food-card .text-muted,
body.dark-mode .small.text-muted {
    color: var(--dark-muted) !important;
}

body.dark-mode .category-link {
    color: var(--dark-text);
}

body.dark-mode .category-link:hover {
    background: var(--dark-accent);
    color: #17202a;
}

body.dark-mode .popular-badge {
    background: var(--dark-accent);
    color: #17202a;
}

body.dark-mode .floating-cart {
    background: var(--dark-accent);
    color: #17202a;
    box-shadow: 0 16px 38px rgba(0,0,0,0.36);
}

body.dark-mode .cart-badge {
    background: #172633;
    color: var(--dark-accent);
}

body.dark-mode .toast {
    background: #172633;
    color: var(--dark-text);
    border: 1px solid rgba(255,183,3,0.35);
}

.canteen-toast {
    z-index: 2100;
}

/* ANIMATION */
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

/* MOBILE MENU OVERLAY */
.mobile-menu {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.3);
    display: none;
    z-index: 2000;
}

.mobile-menu-box {
    background: white;
    margin: 82px 15px 15px;
    padding: 20px;
    border-radius: 16px;
    animation: slideDown 0.25s ease;
    max-height: calc(100vh - 105px);
    overflow-y: auto;
}

@keyframes slideDown {
    from { transform: translateY(-20px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}

/* MOBILE FIX */
@media (max-width: 768px) {
    .navbar-shell {
        position: sticky;
    }

    .navbar-shell .container {
        align-items: flex-start;
    }

    .navbar-shell .navbar-brand {
        font-size: 1rem;
    }

    .navbar-shell .small {
        font-size: 0.76rem;
    }

    .nav-actions {
        width: 100%;
        justify-content: space-between;
        gap: 8px !important;
    }

    .nav-user {
        flex: 1 1 100%;
        order: 5;
        text-align: left !important;
    }

    .brand-mark {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        font-size: 0.86rem;
    }

    .category-sidebar {
        display: none;
    }

    .page-shell {
        padding: 16px 12px 92px;
    }

    .search-wrap {
        position: static;
        padding: 8px 0 12px;
    }

    .menu-scroll {
        max-height: none;
        overflow: visible;
        width: 100%;
    }

    .food-card {
        border-radius: 12px;
    }

    .floating-cart {
        left: 12px;
        right: 12px;
        bottom: 14px;
        justify-content: center;
        border-radius: 16px;
    }
}

@media (min-width: 769px) and (max-width: 1199px) {
    .food-grid > .food-item {
        width: 50%;
    }
}

@media (min-width: 1200px) {
    .food-grid > .food-item {
        width: 33.333333%;
    }
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
        <div class="container d-flex flex-wrap justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-3">
                <div class="brand-mark">DCC</div>
                <div>
                    <a class="navbar-brand fw-bold mb-0" href="../dashboard.php">Digital Canteen Coupon</a>
                    <div class="small text-white-50">Add cart, scan Qr code, avoid long queue. </div>
                </div>
            </div>
            <div class="d-flex align-items-center gap-3 text-white mt-3 mt-lg-0 nav-actions">
                <button class="btn btn-outline-light d-md-none" id="menuToggle" type="button" aria-label="Open categories">
    ⋮
</button>
                   <button id="darkModeToggle" class="btn btn-outline-light me-2">
                    🌙
                </button>
                <div class="text-end nav-user">
                    <div class="fw-semibold">Welcome back, <?= htmlspecialchars($userName) ?></div>
                    <div class="small text-white-50">Community learning dashboard</div>
                </div>
                <a href="../logout.php" class="btn btn-outline-light btn-sm rounded-pill px-3">Logout</a>
            </div>
        </div>
    </nav>

<!-- MOBILE CATEGORY MENU -->
<div id="mobileMenu" class="mobile-menu d-md-none">
    <div class="mobile-menu-box">
        <h6 class="fw-bold mb-3">Categories</h6>

        <a href="?category=Breakfast" class="category-link">Breakfast</a>
        <a href="?category=Lunch" class="category-link">Lunch</a>
        <a href="?category=Snacks" class="category-link">Snacks</a>
        <a href="?category=Drinks" class="category-link">Drinks</a>
        <a href="index.php" class="category-link">All</a>

        <a href="order_history.php" class="category-link fw-bold">My Orders</a>
    </div>
</div>
    <!-- PAGE CONTENT -->
    <main class="container-fluid page-shell">
        <div class="row g-4 align-items-start">

            <!-- LEFT SIDEBAR -->
            <div class="col-12 col-md-2 mb-3 category-sidebar">

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

            <!-- RIGHT CONTENT -->
            <div class="col-12 col-md-10">

                <!-- SEARCH -->
                <div class="search-wrap mb-4">
                    <form method="GET">
                        <input type="text" id="liveSearch" name="search" value="<?= htmlspecialchars($search) ?>"
                            class="form-control rounded-pill shadow-sm px-4" placeholder="🔍 Search food..."
                            style="height:45px;">
                    </form>
                </div>

                <!-- FOOD GRID -->

                <div class="row g-4 menu-scroll food-grid" >

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
        </div>
    </main>

            <!-- FLOATING CART -->
            <a href="cart.php" class="floating-cart">
                🛒 Cart
                <span class="cart-badge"><?= $cartCount ?></span>
            </a>
            <?php if ($canteenNotice !== '') { ?>
                <div class="toast-container position-fixed top-0 end-0 p-3 canteen-toast">
                    <div id="canteenNoticeToast" class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
                        <div class="d-flex">
                            <div class="toast-body">
                                <?= htmlspecialchars($canteenNotice) ?>
                                <a href="cart.php" class="link-light fw-semibold ms-2">View cart</a>
                            </div>
                            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                        </div>
                    </div>
                </div>
            <?php } ?>
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
            <script>
                window.addEventListener("load", () => {
                    document.body.classList.add("page-loaded");
                });
            </script>
            <script>
                function loadNotifications() {
                    const notifBox = document.getElementById("notifBox");
                    if (!notifBox) {
                        return;
                    }

                    fetch("fetch_notify.php")
                        .then(response => response.text())
                        .then(data => {
                            notifBox.innerHTML = data;
                        });
                }

                // load once
                loadNotifications();

                // auto refresh every 5 seconds
                setInterval(loadNotifications, 5000);
            </script>
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
            <script>
const canteenNoticeToast = document.getElementById("canteenNoticeToast");
if (canteenNoticeToast) {
    new bootstrap.Toast(canteenNoticeToast, { delay: 6000 }).show();
}

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

const menuToggleBtn = document.getElementById("menuToggle");
const mobileMenu = document.getElementById("mobileMenu");

// OPEN MENU
if (menuToggleBtn && mobileMenu) {
    menuToggleBtn.addEventListener("click", () => {
        mobileMenu.style.display = "block";
    });

// CLOSE WHEN CLICK OUTSIDE
    mobileMenu.addEventListener("click", (e) => {
        if (e.target === mobileMenu) {
            mobileMenu.style.display = "none";
        }
    });

// CLOSE WHEN CLICKING ANY LINK
    document.querySelectorAll("#mobileMenu a").forEach(link => {
        link.addEventListener("click", () => {
            mobileMenu.style.display = "none";
        });
    });
}

</script>
</body>

</html>
