<?php
session_start();
include "../includes/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php?mode=question");
    exit();
}

$userId = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $queryId = intval($_POST['query_id']);
    $comment = mysqli_real_escape_string($conn, $_POST['comment']);
    $fileName = NULL;

    /* FILE UPLOAD */
    if (!empty($_FILES['file']['name'])) {

        $allowed = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'gif', 'webp', 'txt', 'ppt', 'pptx'];
        $ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));

        if (in_array($ext, $allowed)) {

            $uploadDir = __DIR__ . "/uploads/comments/";
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $fileName = time() . "_" . basename($_FILES['file']['name']);
            $targetFile = $uploadDir . $fileName;

            move_uploaded_file($_FILES['file']['tmp_name'], $targetFile);
        }
    }

    mysqli_query($conn, "
        INSERT INTO comments (query_id, comment, file, user_id)
        VALUES ('$queryId', '$comment', '$fileName', '$userId')
    ");

    header("Location: index.php?mode=question");
    exit();
}
?>