<?php
include("includes/auth.php");
include("includes/db.php");

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    mysqli_query($conn, "DELETE FROM orders WHERE id=$id");
    header("Location: orders.php");
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Canteen Orders</title>
    <link rel="stylesheet" href="css/admin.css">
</head>

<body>

    <?php include("sidebar.php"); ?>

    <div class="main">
        <?php include("header.php"); ?>

        <h2>All Orders</h2>

        <table border="1" width="100%">
            <tr>
                <th>ID</th>
                <th>User ID</th>
                <th>Item</th>
                <th>Status</th>
                <th>Action</th>
            </tr>

            <?php
            $result = mysqli_query($conn, "SELECT * FROM orders");

            while ($row = mysqli_fetch_assoc($result)) {
                echo "<tr>
            <td>" . $row['id'] . "</td>
            <td>" . $row['user_id'] . "</td>
            <td>" . $row['item_name'] . "</td>
            <td>" . $row['status'] . "</td>
            <td>
                <a href='orders.php?delete=" . $row['id'] . "'>Delete</a>
            </td>
          </tr>";
            }
            ?>

        </table>

    </div>
</body>

</html>