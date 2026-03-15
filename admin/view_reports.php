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
    mysqli_query($conn, "DELETE FROM content_reports WHERE id=$report");

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
            background: #f4f6f9;
        }

        table {
            background: white;
        }
    </style>

</head>

<body>

    <nav class="navbar navbar-dark bg-dark">

        <div class="container-fluid">

            <span class="navbar-brand">

                <a href="dashboard.php" class="btn text-light">
                    ⬅ Admin Dashboard
                </a>

            </span>

        </div>

    </nav>


    <div class="container mt-4">

        <h4 class="mb-3">⚠ Reported Issues</h4>

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
                                href="../booksharing/index.php?type=<?= $type ?>&id=<?= $ref ?>" target="_blank">

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

</body>

</html>