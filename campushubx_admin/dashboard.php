<?php
include("includes/auth.php");
include("includes/db.php");
?>

<!DOCTYPE html>
<html>

<head>
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="css/admin.css">
</head>

<body>

    <?php include("sidebar.php"); ?>

    <div class="main">

        <h1>Dashboard</h1>

        <div class="card">
            <h3>Total Users</h3>
            <?php
            $res = mysqli_query($conn, "SELECT COUNT(*) AS total FROM users");
            $row = mysqli_fetch_assoc($res);
            echo $row['total'];
            ?>
        </div>

        <div class="card">
            <h3>Total Resources</h3>
            <?php
            $res = mysqli_query($conn, "SELECT COUNT(*) AS total FROM resources");
            $row = mysqli_fetch_assoc($res);
            echo $row['total'];
            ?>
        </div>

        <div class="card">
            <h3>Total Orders</h3>
            <?php
            $res = mysqli_query($conn, "SELECT COUNT(*) AS total FROM orders");
            $row = mysqli_fetch_assoc($res);
            echo $row['total'];
            ?>
        </div>

    </div>
</body>

</html>