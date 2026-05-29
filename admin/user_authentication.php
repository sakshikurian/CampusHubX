<?php
require_once '../includes/session.php';
include("../includes/db.php");

/* CHECK ADMIN LOGIN */

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../index.php");
    exit();
}

/* FETCH PENDING USERS */

$result = $conn->query("
SELECT * FROM pending_users
ORDER BY id DESC
");

$approvalMessage = $_SESSION['approval_message'] ?? null;
unset($_SESSION['approval_message']);

?>

<!DOCTYPE html>
<html>

<head>

    <title>User Authentication</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: #cfe2f3;
            font-family: 'Poppins', sans-serif;
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
        .card {
            border-radius: 12px;
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

        .dark-mode .table {
            color: #ffffff !important;
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
    </style>

</head>

<body>
<nav class="navbar navbar-expand-lg navbar-dark navbar-shell py-3">
        <div class="container">
            <div class="d-flex align-items-center gap-3">
                <div class="brand-mark">UA</div>
                <div>
                    <a class="navbar-brand fw-bold mb-0" href="dashboard.php">User Authentication</a>
                    <div class="small text-white-50">Manage pending user accounts</div>
                </div>
            </div>

            <div class="d-flex align-items-center gap-3 text-white mt-3 mt-lg-0">
                <button id="darkModeToggle" class="btn btn-outline-light me-2">
                    🌙
                </button>
                
            </div>
        </div>
    </nav>

    <div class="container mt-4">

        <h3>User Authentication</h3>

        <?php if ($approvalMessage) { ?>
            <div class="alert alert-<?= htmlspecialchars($approvalMessage['type']) ?> rounded-4">
                <?= htmlspecialchars($approvalMessage['text']) ?>
            </div>
        <?php } ?>

        <div class="mb-3">

            <a href="add_user.php" class="btn btn-success">Add User Manually</a>

            <a href="upload_users.php" class="btn btn-primary">Upload CSV</a>

        </div>

        <div class="card shadow">

            <div class="card-body">

                <table class="table table-bordered">

                    <thead>

                        <tr>

                            <th>Name</th>
                            <th>Email</th>
                            <th>Action</th>

                        </tr>

                    </thead>

                    <tbody>

                        <?php while ($row = $result->fetch_assoc()) { ?>

                            <tr>

                                <td>
                                    <?= $row['name'] ?>
                                </td>

                                <td>
                                    <?= $row['email'] ?>
                                </td>

                                <td>

                                    <a href="approve_user.php?id=<?= $row['id'] ?>" class="btn btn-success btn-sm">
                                        Approve
                                    </a>

                                    <a href="reject_user.php?id=<?= $row['id'] ?>" class="btn btn-danger btn-sm">
                                        Reject
                                    </a>

                                </td>

                            </tr>

                        <?php } ?>

                    </tbody>

                </table>

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
