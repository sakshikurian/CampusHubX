<?php
session_start();
include "../includes/db.php";

/* PROTECT */
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php?mode=file");
    exit();
}

$userId = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (empty($_POST['title'])) {
        header("Location: index.php?mode=file&error=notitle");
        exit();
    }

    $title = mysqli_real_escape_string($conn, $_POST['title']);

    if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {

        $fileName = $_FILES['file']['name'];
        $tmpName = $_FILES['file']['tmp_name'];
        $fileSize = $_FILES['file']['size'];

        /* ---------- VALIDATION ---------- */

        // Allow only these file types
        $allowed = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'jpg', 'jpeg', 'png', 'zip'];
        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed)) {
            header("Location: index.php?mode=file&error=type");
            exit();
        }

        // Max size = 10MB
        if ($fileSize > 10 * 1024 * 1024) {
            header("Location: index.php?mode=file&error=size");
            exit();
        }

        /* ---------- UPLOAD ---------- */

        $targetDir = __DIR__ . "/uploads/";

        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        $newName = time() . "_" . preg_replace("/[^a-zA-Z0-9.]/", "_", $fileName);
        $targetFile = $targetDir . $newName;
        $dbPath = $newName;

        if (move_uploaded_file($tmpName, $targetFile)) {

            mysqli_query($conn, "
                INSERT INTO resources (title, file_path, user_id, created_at)
                VALUES ('$title', '$dbPath', '$userId', NOW())
            ");

            // Stay in file mode
            header("Location: index.php?mode=file&msg=uploaded");
            exit();

        } else {
            header("Location: index.php?mode=file&error=uploadfail");
            exit();
        }

    } else {
        header("Location: index.php?mode=file&error=nofile");
        exit();
    }
}
?>