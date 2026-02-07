<?php
session_start();
include "../includes/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php?mode=file");
    exit();

}

if (isset($_GET['id'])) {

    $id = intval($_GET['id']);
    $userId = $_SESSION['user_id'];

    $check = $conn->prepare("SELECT file_path FROM resources WHERE id=? AND user_id=?");
    $check->bind_param("ii", $id, $userId);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {

        $row = $result->fetch_assoc();
        $file = "uploads/" . $row['file_path'];

        if (file_exists($file)) {
            unlink($file);
        }

        $del = $conn->prepare("DELETE FROM resources WHERE id=? AND user_id=?");
        $del->bind_param("ii", $id, $userId);
        $del->execute();
    }
}

header("Location: index.php?mode=file&msg=deleted");
exit();

