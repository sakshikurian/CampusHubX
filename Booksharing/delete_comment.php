<?php
session_start();
include "../includes/db.php";

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
}

header("Location: index.php?mode=question");
exit();
?>