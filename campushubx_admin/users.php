<?php
include("includes/auth.php");
include("includes/db.php");

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    mysqli_query($conn, "DELETE FROM users WHERE id=$id");
    header("Location: users.php");
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Manage Users</title>
    <link rel="stylesheet" href="css/admin.css">
</head>

<body>

    <?php include("sidebar.php"); ?>

    <div class="main">
        <?php include("header.php"); ?>

        <h2>All Users</h2>

        <table border="1" width="100%">
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Action</th>
            </tr>

            <?php
            $result = mysqli_query($conn, "SELECT * FROM users");

            while ($row = mysqli_fetch_assoc($result)) {
                echo "<tr>
            <td>" . $row['id'] . "</td>
            <td>" . $row['name'] . "</td>
            <td>" . $row['email'] . "</td>
            <td>
                <a href='users.php?delete=" . $row['id'] . "'>Delete</a>
            </td>
          </tr>";
            }
            ?>

        </table>

    </div>
</body>

</html>