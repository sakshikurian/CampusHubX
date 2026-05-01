<?php
session_start();
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
    $question = trim($_POST['question'] ?? '');
    $category = mysqli_real_escape_string($conn, $_POST['category'] ?? "general");

    if (strlen($question) < 10) {
        if (isAjaxRequest()) {
            sendJson(["success" => false, "message" => "Question must be at least 10 characters long."], 422);
        }
        header("Location: index.php?mode=question");
        exit();
    }

    $questionSafe = mysqli_real_escape_string($conn, $question);
    $imageName = null;

    if (!empty($_FILES['image']['name'])) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));

        if (in_array($ext, $allowed, true)) {
            $dir = __DIR__ . "/uploads/questions/";
            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }

            $imageName = time() . "_" . basename($_FILES['image']['name']);
            move_uploaded_file($_FILES['image']['tmp_name'], $dir . $imageName);
        }
    }

    $imageValue = $imageName ? "'" . mysqli_real_escape_string($conn, $imageName) . "'" : "NULL";

    mysqli_query($conn, "
        INSERT INTO queries (question, image, category, user_id, created_at)
        VALUES ('$questionSafe', $imageValue, '$category', '$userId', NOW())
    ");

    if (isAjaxRequest()) {
        $stats = getUserStats($conn, $userId);

        sendJson([
            "success" => true,
            "message" => "Question posted successfully.",
            "stats" => $stats
        ]);
    }

    header("Location: index.php?mode=question");
    exit();
}

if (isAjaxRequest()) {
    sendJson(["success" => false, "message" => "Invalid request."], 405);
}

header("Location: index.php?mode=question");
exit();
?>
