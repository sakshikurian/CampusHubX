<?php
require_once '../includes/session.php';
include "../includes/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php?mode=question");
    exit();
}

if (isset($_GET['id'])) {

    $id = intval($_GET['id']);
    $userId = $_SESSION['user_id'];

    // Delete replies first (foreign key safety)
    $delReplies = $conn->prepare("DELETE FROM comments WHERE query_id=?");
    $delReplies->bind_param("i", $id);
    $delReplies->execute();

    // Delete question (only owner)
    $delQuery = $conn->prepare("DELETE FROM queries WHERE id=? AND user_id=?");
    $delQuery->bind_param("ii", $id, $userId);
    $delQuery->execute();
}

header("Location: index.php?mode=question");
exit();
?>
