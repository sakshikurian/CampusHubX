<?php
session_start();
// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php"); // Redirect to login if not logged in
    exit();
}

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

        body {
            background-color: #cfe2f3;
        }
    </style>

</head>

<body>

    <!-- Navbar -->

    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">

        <div class="container-fluid">

            <a class="navbar-brand fw-bold" href="#">CampusHubX</a>

            <div class="ms-auto d-flex align-items-center">
                <!-- DARK MODE BUTTON -->
                <button id="darkModeToggle" class="btn btn-outline-light me-2">
                    🌙
                </button>
                <div class="dropdown me-2">
                    <button class="btn btn-outline-light position-relative" data-bs-toggle="dropdown">
                        🔔
                        <span class="position-absolute top-0 start-100 translate-middle badge bg-danger">
                            3
                        </span>
                    </button>

                    <ul class="dropdown-menu dropdown-menu-end p-2"
                        style="width:300px; max-height:400px; overflow-y:auto;">

                        <!-- Notifications will come here -->
                        <div id="notifBox">
                            <li class="dropdown-item text-muted">Loading...</li>
                        </div>

                    </ul>
                </div>
                <div class="dropdown">
                    <button class="btn btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        👤 Welcome, <?= htmlspecialchars($userName) ?>
                    </button>

                    <ul class="dropdown-menu dropdown-menu-end">

                        <li>
                            <a class="dropdown-item" href="profile.php">Profile</a>
                        </li>

                        <li>
                            <hr class="dropdown-divider">
                        </li>

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

        <h2 class="fw-bold mb-4">Campus Services Hub</h2>

        <p class="text-muted mb-5">Select a service module to proceed</p>

        <div class="row justify-content-center">

            <!-- Book Sharing -->

            <div class="col-md-4 mb-4 card-animate">

                <a href="booksharing/index.php" class="text-decoration-none">

                    <div class="card text-center shadow h-100 border-success">

                        <div class="card-body">

                            <div class="card-icon text-success">
                                <i class="fas fa-book-open"></i>
                            </div>

                            <h5 class="card-title text-dark fw-bold"> Book Sharing</h5>

                            <p class="card-text text-muted">
                                Search, list, and share books with peers.
                            </p>

                        </div>

                    </div>

                </a>

            </div>

            <!-- Canteen Coupons -->

            <div class="col-md-4 mb-4 card-animate">

                <a href="canteen/index.php" class="text-decoration-none">

                    <div class="card text-center shadow h-100 border-success">

                        <div class="card-body">

                            <div class="card-icon text-success">
                                <i class="fas fa-utensils"></i>
                            </div>

                            <h5 class="card-title text-dark fw-bold"> Canteen Coupons</h5>

                            <p class="card-text text-muted">
                                Order food digitally & generate coupon
                            </p>

                        </div>

                    </div>

                </a>

            </div>

            <!-- Campus Navigation -->

            <div class="col-md-4 mb-4 card-animate">

                <a href="navigationA/index.php" class="text-decoration-none">

                    <div class="card text-center shadow h-100 border-success">

                        <div class="card-body">

                            <div class="card-icon text-success">
                                <i class="fas fa-map-marked-alt"></i>
                            </div>

                            <h5 class="card-title text-dark fw-bold"> Campus Navigation</h5>

                            <p class="card-text text-muted">
                                Find buildings, classrooms, and routes.
                            </p>

                        </div>

                    </div>

                </a>

            </div>

        </div>

    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const toggleBtn = document.getElementById("darkModeToggle");

        // load saved mode
        if (localStorage.getItem("darkMode") === "on") {
            document.body.classList.add("dark-mode");
        }

        toggleBtn.addEventListener("click", () => {
            document.body.classList.toggle("dark-mode");

            if (document.body.classList.contains("dark-mode")) {
                localStorage.setItem("darkMode", "on");
            } else {
                localStorage.setItem("darkMode", "off");
            }
        });
    </script>
    <script>
        document.querySelector('[data-bs-toggle="dropdown"]').addEventListener("click", () => {

            fetch("fetch_notifications.php")
                .then(res => res.text())
                .then(data => {
                    document.getElementById("notifBox").innerHTML = data;
                })
                .catch(() => {
                    document.getElementById("notifBox").innerHTML =
                        "<li class='dropdown-item text-danger'>Failed to load</li>";
                });

        });
    </script>
</body>

</html>