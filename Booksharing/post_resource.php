<?php
require_once '../includes/session.php';
include "../includes/db.php";

function getUserStats($conn, $userId)
{
    return [
        "questions" => (int) (mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM queries WHERE user_id=$userId"))['total'] ?? 0),
        "resources" => (int) (mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM resources WHERE user_id=$userId"))['total'] ?? 0),
        "comments" => (int) (mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM comments WHERE user_id=$userId"))['total'] ?? 0),
        "sos" => (int) (mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM queries WHERE user_id=$userId AND LOWER(category)='sos'"))['total'] ?? 0)
    ];
}

function isAjaxRequest()
{
    return (
        (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') ||
        (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)
    );
}

function sendJson($payload, $statusCode = 200)
{
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($payload);
    exit();
}

if (!isset($_SESSION['user_id'])) {
    if (isAjaxRequest()) {
        sendJson(["success" => false, "message" => "Please log in first."], 401);
    }
    header("Location: login.php");
    exit();
}

$userId = (int) $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (empty($_FILES['files']['name'][0])) {
        if (isAjaxRequest()) {
            sendJson(["success" => false, "message" => "Please choose at least one file."], 422);
        }
        header("Location: index.php?mode=file");
        exit();
    }

    $totalFiles = count($_FILES['files']['name']);
    if ($totalFiles > 5) {
        if (isAjaxRequest()) {
            sendJson(["success" => false, "message" => "You can upload a maximum of 5 files only."], 422);
        }
        die("You can upload maximum 5 files only.");
    }

    $description = mysqli_real_escape_string($conn, trim($_POST['description'] ?? ''));
    $allowed = [
        "pdf", "jpg", "jpeg", "png", "gif", "ppt", "pptx", "doc", "docx",
        "xls", "xlsx", "txt", "zip", "rar", "webp"
    ];
    $uploadedCount = 0;

    for ($i = 0; $i < $totalFiles; $i++) {
        $originalName = $_FILES["files"]["name"][$i];
        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

     if ($_FILES["files"]["size"][$i] > 5 * 1024 * 1024) {
    continue; // skip this file
}

        $newFileName = uniqid() . "_" . preg_replace('/[^a-zA-Z0-9.]/', '_', $originalName);
       $targetDir = __DIR__ . "/uploads/";
$targetFile = $targetDir . $newFileName;

        if (move_uploaded_file($_FILES["files"]["tmp_name"][$i], $targetFile)) {
            $title = mysqli_real_escape_string($conn, pathinfo($originalName, PATHINFO_FILENAME));
            $inserted = mysqli_query($conn, "
                INSERT INTO resources (user_id, title, file_path, description)
                VALUES ($userId, '$title', '$newFileName', '$description')
            ");

            if (!$inserted) {
                $inserted = mysqli_query($conn, "
                    INSERT INTO resources (user_id, title, file_path)
                    VALUES ($userId, '$title', '$newFileName')
                ");
            }

            if ($inserted) {
                $uploadedCount++;
            }
        }
    }

    if (isAjaxRequest()) {
        $stats = getUserStats($conn, $userId);

        sendJson([
            "success" => $uploadedCount > 0,
            "message" => $uploadedCount > 0 ? "Resource upload completed." : "No valid files were uploaded.",
            "stats" => $stats
        ], $uploadedCount > 0 ? 200 : 422);
    }

    header("Location: index.php?mode=file&msg=uploaded");
    exit();
}

if (isAjaxRequest()) {
    sendJson(["success" => false, "message" => "Invalid request."], 405);
}

header("Location: index.php?mode=file");
exit();
?>
