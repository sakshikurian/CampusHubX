<?php
require_once 'includes/session.php';
include "includes/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

$userId = $_SESSION['user_id'];

$q = mysqli_query($conn, "SELECT * FROM users WHERE id=$userId");
$user = mysqli_fetch_assoc($q);
?>

<!DOCTYPE html>
<html>

<head>
    <title>Profile | CampusHubX</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: #cfe2f3;
        }

        .profile-card {
            max-width: 500px;
            margin: auto;
            margin-top: 60px;
            border-radius: 20px;
        }

        .profile-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: #0d6efd;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 30px;
            margin: auto;
        }

        /* DARK MODE */
        body.dark-mode {
            background-color: #3e3d3d !important;
            color: #ffffff !important;
        }

        body {
            opacity: 0;
            transition: opacity 0.4s ease-in-out;
        }

        body.page-loaded {
            opacity: 1;
        }
    </style>
</head>

<body>

    <!-- NAVBAR -->
    <nav class="navbar navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="dashboard.php">⬅ CampusHubX</a>
            <div class="d-flex align-items-center gap-2">
                <span class="text-white">
                    Welcome,
                    <?= htmlspecialchars($_SESSION['user_name']); ?>!
                </span>
                <button id="darkModeToggle" class="btn btn-outline-light">
                    🌙
                </button>
                <button class="btn btn-outline-light position-relative" data-bs-toggle="dropdown">
                    🔔
                    <span class="position-absolute top-0 start-100 translate-middle badge bg-danger">
                        3
                    </span>
                </button>

            </div>
        </div>
    </nav>

    <div class="container">

        <div class="card shadow profile-card p-4 text-center">

            <!-- Avatar -->
            <div class="profile-avatar mb-3">
                <?= strtoupper($user['name'][0]) ?>
            </div>

            <!-- User Info -->
            <h4>
                <?= htmlspecialchars($user['name']) ?>
            </h4>
            <p class="text-muted">
                <?= htmlspecialchars($user['email']) ?>
            </p>

            <hr>

            <!-- Extra Info -->
            <p><b>User ID:</b>
                <?= $user['id'] ?>
            </p>

            <!-- Buttons -->
            <div class="mt-3">
                <a href="edit_profile.php" class="btn btn-primary">Edit Profile</a>
                <a href="../logout.php" class="btn btn-danger">Logout</a>
            </div>

        </div>

    </div>


    <script src="../js/darkmode.js"></script>
    <script>
        window.addEventListener("load", () => {
            document.body.classList.add("page-loaded");
        });
    </script>
</body>

</html>