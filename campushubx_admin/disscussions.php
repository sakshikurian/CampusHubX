<?php
include("includes/auth.php");
include("includes/db.php");

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    mysqli_query($conn, "DELETE FROM discussions WHERE id=$id");
    header("Location: discussions.php");
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Manage Discussions</title>
    <link rel="stylesheet" href="css/admin.css">
</head>

<body>

    <?php include("sidebar.php"); ?>

    <div class="main">
        <?php include("header.php"); ?>

        <h2>All Discussions</h2>

        <table border="1" width="100%">
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>User ID</th>
                <th>Category</th>
                <th>Action</th>
            </tr>

            <?php
            $result = mysqli_query($conn, "SELECT * FROM discussions");

            while ($row = mysqli_fetch_assoc($result)) {
                echo "<tr>
            <td>" . $row['id'] . "</td>
            <td>" . $row['title'] . "</td>
            <td>" . $row['user_id'] . "</td>
            <td>" . $row['category'] . "</td>
            <td>
                <a href='discussions.php?delete=" . $row['id'] . "'>Delete</a>
            </td>
          </tr>";
            }
            ?>

        </table>

    </div>
</body>

</html>