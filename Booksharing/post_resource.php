<?php
session_set_cookie_params([
    'path' => '/'
]);
session_start();
include "../includes/db.php";

if (!isset($_SESSION['user_id'])) {
    exit();
}

$userId = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    if (!empty($_FILES['files']['name'][0])) {

        $totalFiles = count($_FILES['files']['name']);

        if ($totalFiles > 5) {
            die("You can upload maximum 5 files only.");
        }

        $allowed = [
            "pdf",
            "jpg",
            "jpeg",
            "png",
            "gif",
            "ppt",
            "pptx",
            "doc",
            "docx",
            "xls",
            "xlsx",
            "txt",
            "zip",
            "rar"
        ];

        for ($i = 0; $i < $totalFiles; $i++) {

            $originalName = $_FILES["files"]["name"][$i];
            $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

            if (!in_array($ext, $allowed)) {
                continue; // skip invalid file
            }

            $newFileName = time() . "_" . $originalName;
            $targetDir = "uploads/";
            $targetFile = $targetDir . $newFileName;

            if (move_uploaded_file($_FILES["files"]["tmp_name"][$i], $targetFile)) {

                $title = pathinfo($originalName, PATHINFO_FILENAME);

                mysqli_query($conn, "
                INSERT INTO resources (user_id, title, description, file_path)
                    VALUES ($userId, '$title','$description', '$newFileName')
                ");
                // 🔥 get inserted resource id
                $resId = mysqli_insert_id($conn);

                // 🔥 get all users
                $users = mysqli_query($conn, "SELECT id FROM users");

                while ($u = mysqli_fetch_assoc($users)) {

                    $message = "📁 New resource uploaded: $title";

                    // 🔥 IMPORTANT: link to exact resource
                    $link = "/campushubx/booksharing/index.php?type=resource&id=$resId";
                    mysqli_query($conn, "
        INSERT INTO notifications (user_id, message, link)
        VALUES ({$u['id']}, '$message', '$link')
    ");
                }
            }
        }

        header("Location: index.php?mode=file&msg=uploaded");
        exit();
    }
} else {
    die("Upload failed.");
}


?>