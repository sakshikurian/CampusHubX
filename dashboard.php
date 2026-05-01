<?php
require_once 'includes/session.php';
include "includes/db.php";

// Check if the user is logged in
$userId = $_SESSION['user_id'] ?? 0;
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php"); // Redirect to login if not logged in
    exit();
}
$userId = $_SESSION['user_id'] ?? 0;

$countQuery = mysqli_query($conn, "
    SELECT COUNT(*) AS total 
    FROM notifications 
    WHERE user_id = $userId AND is_read = 0
");

$countData = mysqli_fetch_assoc($countQuery);
$notifCount = $countData['total'] ?? 0;
$userName = $_SESSION['user_name'] ?? "User";

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>CampusHubX Dashboard</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">

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

        .card-animate {
            opacity: 0;
            transform: translateY(30px);
            animation: fadeSlideUp 0.6s ease forwards;
        }

        /* delay for each card */
        .card-animate:nth-child(1) {
            animation-delay: 0.1s;
        }

        .card-animate:nth-child(2) {
            animation-delay: 0.3s;
        }

        .card-animate:nth-child(3) {
            animation-delay: 0.5s;
        }

        @keyframes fadeSlideUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }


        body.dark-mode {
            background-color: #3e3d3d !important;
            color: #ffffff !important;
        }

        /* HEADINGS */
        .dark-mode h1,
        .dark-mode h2,
        .dark-mode h3,
        .dark-mode h4,
        .dark-mode h5 {
            color: #ffffff !important;
        }

        /* PARAGRAPH / TEXT */
        .dark-mode p,
        .dark-mode small,
        .dark-mode span {
            color: #d1d1d1 !important;
        }

        /* CARDS */
        .dark-mode .card {
            background-color: #1e1e1e !important;
            color: #ffffff !important;
            border: 1px solid #2c2c2c;
        }

        /* CARD TEXT INSIDE */
        .dark-mode .card p {
            color: #e0e0e0 !important;
        }

        /* BADGES / LABELS */
        .dark-mode .badge {
            color: #fff !important;
        }

        /* NAVBAR */
        .dark-mode .navbar {
            background-color: #000 !important;
        }

        /* LINKS */
        .dark-mode a {
            color: #4dabf7;
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
       
.center-wrapper {
    min-height: calc(100vh - 90px);
    display: flex;
    align-items: center;
    justify-content: center;
}

/* move cards slightly up */
.center-wrapper .row {
    transform: translateY(-60px);
}
.badge {
    font-size: 11px;
    padding: 4px 6px;
    border-radius: 50%;
}
        .card-icon {
            font-size: 5rem;
            margin-bottom: 10px;
        }

        .card {
            border-radius: 1rem;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
        }

        .card:hover {
            transform: translateY(-5px) scale(1.02);
            transition: 0.3s ease;
        }
 * {
            font-family: "Manrope", sans-serif;
        }
        body {
            background-color: #cfe2f3;
        }
        /* 📱 MOBILE OPTIMIZATION */
@media (max-width: 768px) {

    .center-wrapper {
        align-items: flex-start;   /* move content up */
        padding-top: 30px;
    }

    .row {
        flex-direction: column;    /* stack cards */
        align-items: center;
    }

    .col-md-4 {
        width: 100%;
        max-width: 350px;          /* prevent full stretch */
    }

    .card {
        border-radius: 18px;
        padding: 10px;
    }

    .card-icon {
        font-size: 3rem;           /* smaller icons */
    }

    .card-title {
        font-size: 18px;
    }

    .card-text {
        font-size: 14px;
    }
}
    </style>

</head>

<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-shell py-3">
    <div class="container">

        <!-- LEFT SIDE (BRAND) -->
        <div class="d-flex align-items-center gap-3">
            <div class="brand-mark">CH</div>
            <div>
                <a class="navbar-brand fw-bold mb-0" href="dashboard.php">CampusHubX</a>
                <div class="small text-white-50">Learn, share, grow together</div>
            </div>
        </div>

        <!-- RIGHT SIDE -->
        <div class="d-flex align-items-center gap-3 text-white mt-3 mt-lg-0">

            <!-- 🌙 DARK MODE -->
            <button id="darkModeToggle" class="btn btn-outline-light me-2">
                🌙
            </button>

            <!-- 🔔 NOTIFICATIONS -->
            <div class="dropdown me-2">
                <button class="btn btn-outline-light position-relative" data-bs-toggle="dropdown">
                    🔔
                    <span class="badge bg-danger">
                        <?= $notifCount ?>
                    </span>
                </button>

                <ul class="dropdown-menu dropdown-menu-end p-2"
                    style="width:500px; max-height:400px; overflow-y:auto;">
                    
                    <div id="notifBox">
                        <li class="dropdown-item text-muted">Loading...</li>
                    </div>

                </ul>
            </div>

            <!-- 👤 USER DROPDOWN -->
            <div class="dropdown">
                <button class="btn btn-light dropdown-toggle rounded-pill px-3" type="button" data-bs-toggle="dropdown">
                    👤 <?= htmlspecialchars($userName) ?>
                </button>

                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <a class="dropdown-item" href="profile.php">Profile</a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item text-danger" href="logout.php">
                            Logout
                        </a>
                    </li>
                </ul>
            </div>

        </div>
    </div>
</nav>

    <!-- Dashboard -->

    <div class="container mt-5 text-center">

     

        <div class="center-wrapper">

    <div class="row justify-content-center g-4 w-100">

        <!-- Book Sharing -->
        <div class="col-md-4 card-animate">
            <a href="booksharing/index.php" class="text-decoration-none">
                <div class="card text-center shadow h-100 border-success">
                    <div class="card-body">
                        <div class="card-icon text-success">
                            <i class="fas fa-book-open"></i>
                        </div>
                        <h5 class="card-title text-dark fw-bold">Book Sharing</h5>
                        <p class="card-text text-muted">
                            Search, list, and share books with peers.
                        </p>
                    </div>
                </div>
            </a>
        </div>

        <!-- Canteen -->
        <div class="col-md-4 card-animate">
            <a href="canteen/index.php" class="text-decoration-none">
                <div class="card text-center shadow h-100 border-success">
                    <div class="card-body">
                        <div class="card-icon text-success">
                            <i class="fas fa-utensils"></i>
                        </div>
                        <h5 class="card-title text-dark fw-bold">Canteen Coupons</h5>
                        <p class="card-text text-muted">
                            Order food digitally & generate coupon
                        </p>
                    </div>
                </div>
            </a>
        </div>

        <!-- Navigation -->
        <div class="col-md-4 card-animate">
            <a href="navigationA/index.php" class="text-decoration-none">
                <div class="card text-center shadow h-100 border-success">
                    <div class="card-body">
                        <div class="card-icon text-success">
                            <i class="fas fa-map-marked-alt"></i>
                        </div>
                        <h5 class="card-title text-dark fw-bold">Campus Navigation</h5>
                        <p class="card-text text-muted">
                            Find buildings, classrooms, and routes.
                        </p>
                    </div>
                </div>
            </a>
        </div>

    </div>

</div>

    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const toggleBtn = document.getElementById("darkModeToggle");

        // load saved mode
        if (localStorage.getItem("darkMode") === "enabled") {
            document.body.classList.add("dark-mode");
            toggleBtn.textContent = '☀️';
        }

        toggleBtn.addEventListener("click", () => {
            document.body.classList.toggle("dark-mode");

            if (document.body.classList.contains("dark-mode")) {
                localStorage.setItem("darkMode", "enabled");
                toggleBtn.textContent = '☀️';
            } else {
                localStorage.setItem("darkMode", "disabled");
                toggleBtn.textContent = '🌙';
            }
        });
    </script>
    <script>
        function loadNotifications() {
            fetch("fetch_notifications.php")
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
</body>

</html>