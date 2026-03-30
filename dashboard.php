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
        body.dark-mode {
            background-color: #121212 !important;
            color: #ffffff;
        }

        .dark-mode .card {
            background-color: #1e1e1e;
            color: white;
        }

        .dark-mode .navbar {
            background-color: #000 !important;
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
                            <a class="dropdown-item" href="../profile.php">
                                Profile
                            </a>
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

            <div class="col-md-4 mb-4">

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

            <div class="col-md-4 mb-4">

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

            <div class="col-md-4 mb-4">

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