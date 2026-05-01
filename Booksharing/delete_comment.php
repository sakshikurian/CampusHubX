<?php
session_start();
include "../includes/db.php";

$columnCheck = mysqli_query($conn, "SHOW COLUMNS FROM comments LIKE 'parent_comment_id'");
if ($columnCheck && mysqli_num_rows($columnCheck) === 0) {
    mysqli_query($conn, "ALTER TABLE comments ADD COLUMN parent_comment_id INT NULL DEFAULT NULL AFTER query_id");
}

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php?mode=question");
    exit();
}

if (isset($_GET['id'])) {

    $id = intval($_GET['id']);
    $userId = $_SESSION['user_id'];

    $del = $conn->prepare("DELETE FROM comments WHERE id=? AND user_id=?");
    $del->bind_param("ii", $id, $userId);
    $del->execute();

    $replyDel = $conn->prepare("DELETE FROM comments WHERE parent_comment_id=?");
    $replyDel->bind_param("i", $id);
    $replyDel->execute();
}

header("Location: index.php?mode=question");
exit();
?>
