<?php
session_start();
include "../includes/db.php";

if (!isset($_SESSION['user_id'])) {
    exit();
}

$userId = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {

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
                    INSERT INTO resources (user_id, title, file_path)
                    VALUES ($userId, '$title', '$newFileName')
                ");
            }
        }

        header("Location: index.php?mode=file&msg=uploaded");
        exit();
    }
} else {
    die("Upload failed.");
}


?>