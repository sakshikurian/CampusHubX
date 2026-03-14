<?php
session_start();
include "../includes/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php?mode=question");
    exit();
}

$userId = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $question = mysqli_real_escape_string($conn, $_POST['question']);
    $category = mysqli_real_escape_string($conn, $_POST['category'] ?? "general");

    $imageName = NULL;

    if (!empty($_FILES['image']['name'])) {

        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));

        if (in_array($ext, $allowed)) {

            $dir = __DIR__ . "/uploads/questions/";
            if (!is_dir($dir))
                mkdir($dir, 0777, true);

            $imageName = time() . "_" . basename($_FILES['image']['name']);
            move_uploaded_file($_FILES['image']['tmp_name'], $dir . $imageName);
        }
    }

    mysqli_query($conn, "
    INSERT INTO queries (question,image,category,user_id,created_at)
    VALUES ('$question','$imageName','$category','$userId',NOW())
    ");

    header("Location: index.php?mode=question");
    exit();
}
?>