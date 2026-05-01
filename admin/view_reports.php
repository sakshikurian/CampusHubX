<?php
session_start();
include("../includes/db.php");

/* ALLOW ADMIN OR USER */
if (!isset($_SESSION['admin_id']) && !isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

/* DELETE CONTENT IF ADMIN TAKES ACTION */
if (isset($_GET['delete_type'])) {

    $type = $_GET['delete_type'];
    $id = intval($_GET['id']);
    $report = intval($_GET['report']);

    if ($type == "post") {
        mysqli_query($conn, "DELETE FROM queries WHERE id=$id");
    }

    if ($type == "comment") {
        mysqli_query($conn, "DELETE FROM comments WHERE id=$id");
    }

    if ($type == "resource") {

        $res = mysqli_fetch_assoc(
            mysqli_query($conn, "SELECT file_path FROM resources WHERE id=$id")
        );

        if ($res) {

            $file = "../booksharing/uploads/" . $res['file_path'];

            if (file_exists($file)) {
                unlink($file);
            }

            mysqli_query($conn, "DELETE FROM resources WHERE id=$id");
        }
    }

    /* REMOVE REPORT AFTER ACTION */
    mysqli_query($conn, "DELETE FROM content_reports WHERE reference_id=$id AND type='$type'");

    header("Location:view_reports.php");
    exit();
}
?>

<!DOCTYPE html>
<html>

<head>

    <title>Reported Issues | Admin</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: #cfe2f3;
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
        table {
            background: white;
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
            background-color: #1e1e1e !important;
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
                <div class="brand-mark">RI</div>
                <div>
                    <a class="navbar-brand fw-bold mb-0" href="dashboard.php">Reported Issues</a>
                    <div class="small text-white-50">View and manage reported content</div>
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

        <table class="table table-bordered table-hover">

            <thead class="table-dark">

                <tr>
                    <th>ID</th>
                    <th>User</th>
                    <th>Type</th>
                    <th>Reason</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>

            </thead>

            <tbody>

                <?php

                $q = mysqli_query($conn, "
SELECT r.*,u.name
FROM content_reports r
JOIN users u ON r.user_id=u.id
ORDER BY r.id DESC
");

                if (mysqli_num_rows($q) == 0) {

                    echo "<tr><td colspan='6' class='text-center'>No reports</td></tr>";

                }

                while ($row = mysqli_fetch_assoc($q)) {

                    $type = $row['type'];
                    $ref = $row['reference_id'];

                    ?>

                    <tr>

                        <td><?= $row['id'] ?></td>

                        <td><?= htmlspecialchars($row['name']) ?></td>

                        <td><?= strtoupper($type) ?></td>

                        <td><?= htmlspecialchars($row['reason']) ?></td>

                        <td><?= date("d M Y", strtotime($row['created_at'])) ?></td>

                        <td>

                            <!-- VIEW EXACT CONTENT -->

                            <a class="btn btn-primary btn-sm"
                                href="../booksharing/index.php?type=<?= $row['type'] ?>&id=<?= $row['reference_id'] ?>">
                                View
                            </a>

                            <!-- DELETE CONTENT -->

                            <a class="btn btn-danger btn-sm"
                                href="?delete_type=<?= $type ?>&id=<?= $ref ?>&report=<?= $row['id'] ?>"
                                onclick="return confirm('Delete this content?')">

                                Delete Content

                            </a>

                        </td>

                    </tr>

                <?php } ?>

            </tbody>

        </table>

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