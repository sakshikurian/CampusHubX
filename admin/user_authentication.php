<?php
session_start();
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

?>

<!DOCTYPE html>
<html>

<head>

    <title>User Authentication</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: #f4f6f9;
            font-family: 'Poppins', sans-serif;
        }

        .card {
            border-radius: 12px;
        }
    </style>

</head>

<body>

    <nav class="navbar navbar-dark bg-dark">

        <div class="container-fluid">

            <span class="navbar-brand">

                <a href="dashboard.php" class="btn text-light"> ⬅️ Admin Dashboard</a>
            </span>



        </div>

    </nav>

    <div class="container mt-4">

        <h3>User Authentication</h3>

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

</body>

</html>