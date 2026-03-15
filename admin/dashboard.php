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
        body {
            background: #f4f6f9;
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
            height: 140px;
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
    </style>
</head>

<body>

    <!-- NAVBAR -->
    <nav class="navbar navbar-dark bg-dark">
        <div class="container-fluid">

            <span class="navbar-brand fw-bold">
                CampusHubX Admin Dashboard
            </span>

            <a href="../logout.php" class="btn btn-light">
                Logout
            </a>

        </div>
    </nav>

    <div class="container mt-5 dashboard-wrapper">

        <div class="row g-4">

            <!-- TOTAL USERS -->
            <div class="col-md-6">
                <div class="dashboard-box box-users">
                    <div>Total Users</div>
                    <div class="dashboard-number"><?= $totalUsers ?></div>
                </div>
            </div>

            <!-- PENDING USERS -->
            <div class="col-md-6">
                <div class="dashboard-box box-pending">
                    <div>Pending Users</div>
                    <div class="dashboard-number"><?= $pendingUsers ?></div>
                </div>
            </div>

            <!-- USER AUTH -->
            <div class="col-md-6">
                <a href="user_authentication.php" class="dashboard-box box-auth">
                    User Authentication
                </a>
            </div>

            <!-- REPORTED ISSUES -->
            <div class="col-md-6">
                <a href="view_reports.php" class="dashboard-box box-reports">
                    Reported Issues
                </a>
            </div>

        </div>

    </div>

</body>

</html>