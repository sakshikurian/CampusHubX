<?php

session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php"); // Redirect to login if not logged in
    exit();
}

$userName = $_SESSION['user_name'];
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
                <span class="navbar-text me-3">Welcome, <?= htmlspecialchars($userName); ?>!</span>
                <a href="logout.php" class="btn btn-outline-light btn-sm">Logout</a>
            </div>
        </div>
    </nav>

    <!-- Dashboard -->
    <div class="container mt-5 text-center">
        <h2 class="fw-bold mb-4">Campus Services Hub</h2>
        <p class="text-muted mb-5">Select a service module to proceed:</p>

        <div class="row justify-content-center">
            <!-- Book Sharing -->
            <div class="col-md-4 mb-4">
                <a href="booksharing/view_books.php" class="text-decoration-none">
                    <div class="card text-center shadow h-100 border-primary">
                        <div class="card-body">
                            <div class="card-icon text-primary"><i class="fas fa-book-open"></i></div>
                            <h5 class="card-title text-dark fw-bold">📚 Book Sharing</h5>
                            <p class="card-text text-muted">Search, list, and share books with peers.</p>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Canteen Coupons -->
            <div class="col-md-4 mb-4">
                <div class="card text-center shadow h-100 border-secondary opacity-50">
                    <div class="card-body">
                        <div class="card-icon text-secondary"><i class="fas fa-utensils"></i></div>
                        <h5 class="card-title text-dark fw-bold">🍴 Canteen Coupons</h5>
                        <p class="card-text text-muted">Order food digitally (Coming Soon)</p>
                    </div>
                </div>
            </div>

            <!-- Campus Navigation -->
            <div class="col-md-4 mb-4">
                <div class="card text-center shadow h-100 border-secondary opacity-50">
                    <div class="card-body">
                        <div class="card-icon text-secondary"><i class="fas fa-map-marked-alt"></i></div>
                        <h5 class="card-title text-dark fw-bold">🧭 Campus Navigation</h5>
                        <p class="card-text text-muted">Find your way around campus (Coming Soon)</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>


</html>