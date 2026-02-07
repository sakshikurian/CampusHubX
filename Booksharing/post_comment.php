<?php
session_start();
include "../includes/db.php";

// protect
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$query_id = $_POST['query_id'];
$comment = $_POST['comment'];

mysqli_query(
    $conn,
    "INSERT INTO comments (query_id, user_id, comment)
     VALUES ('$query_id', '$user_id', '$comment')"
);

header("Location: index.php?mode=question&msg=posted");
exit();

?>