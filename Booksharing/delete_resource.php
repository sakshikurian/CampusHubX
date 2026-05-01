<?php
session_start();
include "../includes/db.php";

/* ================= AUTO DELETE FILES OLDER THAN 7 DAYS ================= */

$oldFiles = mysqli_query($conn, "
    SELECT id, file_path 
    FROM resources 
    WHERE created_at < NOW() - INTERVAL 7 DAY
");

while ($f = mysqli_fetch_assoc($oldFiles)) {
    $file = "uploads/" . $f['file_path'];

    if (file_exists($file)) {
        unlink($file);   // delete from folder
    }

    mysqli_query($conn, "DELETE FROM resources WHERE id=" . $f['id']);
}


/* ================= MANUAL DELETE BY USER ================= */

if (isset($_GET['id']) && isset($_SESSION['user_id'])) {

    $id = intval($_GET['id']);
    $userId = $_SESSION['user_id'];

    // Check if file belongs to logged-in user
    $check = mysqli_query($conn, "
        SELECT file_path 
        FROM resources 
        WHERE id=$id AND user_id=$userId
    ");

    if (mysqli_num_rows($check) > 0) {

        $row = mysqli_fetch_assoc($check);
        $file = "uploads/" . $row['file_path'];

        if (file_exists($file)) {
            unlink($file);  // delete file
        }

        mysqli_query($conn, "DELETE FROM resources WHERE id=$id");
    }
}

header("Location: index.php");
exit();
