<?php
session_start();
include("../includes/db.php");

/* CHECK ADMIN LOGIN */
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../index.php");
    exit();
}

/* DASHBOARD STATS */
$totalUsers = $conn->query("
SELECT COUNT(*) as total 
FROM users
")->fetch_assoc()['total'];

$pendingUsers = $conn->query("
SELECT COUNT(*) as total 
FROM pending_users
")->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html>

<head>
    <title>Admin Dashboard</title>

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
        body {
            background: #cfe2f3;
            font-family: 'Poppins', sans-serif;
        }

        /* Navbar */
        .navbar {
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.15);
        }

        /* Dashboard container */
        .dashboard-wrapper {
            max-width: 900px;
            margin: auto;
        }

        /* Cards */
        .dashboard-box {
            border: none;
            border-radius: 15px;
          
    min-height: 120px;
    height: auto;
    padding: 20px;

            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            padding: 40px;
            font-weight: 500;
            transition: 0.3s;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .dashboard-box:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 25px rgba(0, 0, 0, 0.2);
        }

        /* Colors */

        .box-users {
            background: #0d6efd;
            color: white;
        }

        .box-pending {
            font-family: Poppins', ' 'Franklin Gothic Medium', 'Arial Narrow', Arial, 'sans-serif';
            font-size: 25px;
            background: #ffc107;
            color: black;
        }

        .box-auth {
            background: #a09b9b;
            color: white;
            text-decoration: none;
        }

        .box-reports {
            background: #a09b9b;
            color: white;
            text-decoration: none;
        }

        /* Numbers */

        .dashboard-number {
            font-size: 35px;
            font-weight: 700;
            margin-top: 5px;
        }

        /* DARK MODE */
        body.dark-mode {
            background-color: #3e3d3d !important;
            color: #ffffff !important;
        }

        .dark-mode .card {
            background-color: #1e1e1e !important;
            color: #ffffff !important;
            border: 1px solid #2c2c2c;
        }

        .dark-mode h1,
        .dark-mode h2,
        .dark-mode h3,
        .dark-mode h4,
        .dark-mode h5 {
            color: #ffffff !important;
        }

        .dark-mode p,
        .dark-mode small,
        .dark-mode span {
            color: #d1d1d1 !important;
        }

        .dark-mode .navbar {
            background-color: #000 !important;
        }

        .dark-mode a {
            color: #4dabf7;
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

    .dashboard-number {
        font-size: 28px;
    }

    .dashboard-box {
        padding: 20px;
    }

    .dashboard-box div:first-child {
        font-size: 14px;
    }

    .navbar .container {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }

    .navbar .btn {
        width: 100%;
    }
    .dashboard-wrapper {
        padding: 0 10px;
    }

}
    </style>
</head>

<body>

   <nav class="navbar navbar-expand-lg navbar-dark navbar-shell py-3">
        <div class="container">
            <div class="d-flex align-items-center gap-3">
                <div class="brand-mark">AD</div>
                <div>
                    <a class="navbar-brand fw-bold mb-0" href="dashboard.php">Admin Dashboard</a>
                    <div class="small text-white-50">Manage your application</div>
                </div>
            </div>

            <div class="d-flex align-items-center gap-3 text-white mt-3 mt-lg-0">
                <button id="darkModeToggle" class="btn btn-outline-light me-2">
                    🌙
                </button>
                 <a href="../logout.php" class="btn btn-outline-light btn-sm rounded-pill px-3">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-5 dashboard-wrapper">

        <div class="row g-4">

            <!-- TOTAL USERS -->
            
<div class="col-12 col-md-6">

                <div class="dashboard-box box-users">
                    <div>Total Users</div>
                    <div class="dashboard-number"><?= $totalUsers ?></div>
                </div>
            </div>

            <!-- PENDING USERS -->
            
<div class="col-12 col-md-6">

                <div class="dashboard-box box-pending">
                    <div>Pending Users</div>
                    <div class="dashboard-number"><?= $pendingUsers ?></div>
                </div>
            </div>

            <!-- USER AUTH -->
       
<div class="col-12 col-md-6">

                <a href="user_authentication.php" class="dashboard-box box-auth">
                    User Authentication
                </a>
            </div>

            <!-- REPORTED ISSUES -->
           
<div class="col-12 col-md-6">

                <a href="view_reports.php" class="dashboard-box box-reports">
                    Reported Issues
                </a>
            </div>

        </div>

    </div>

    <script>
        // DARK MODE TOGGLE
        const darkModeToggle = document.getElementById('darkModeToggle');
        const body = document.body;

        // Check for saved dark mode preference
        if (localStorage.getItem('darkMode') === 'enabled') {
            body.classList.add('dark-mode');
            darkModeToggle.textContent = '☀️';
        }

        darkModeToggle.addEventListener('click', () => {
            body.classList.toggle('dark-mode');
            if (body.classList.contains('dark-mode')) {
                localStorage.setItem('darkMode', 'enabled');
                darkModeToggle.textContent = '☀️';
            } else {
                localStorage.setItem('darkMode', 'disabled');
                darkModeToggle.textContent = '🌙';
            }
        });

        // Page transition
        window.addEventListener('load', () => {
            body.classList.add('page-loaded');
        });
    </script>

</body>

</html>