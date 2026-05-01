<?php
session_start();
include "../includes/db.php";

function ensureCommentReplySupport($conn)
{
    $columnCheck = mysqli_query($conn, "SHOW COLUMNS FROM comments LIKE 'parent_comment_id'");
    if ($columnCheck && mysqli_num_rows($columnCheck) === 0) {
        mysqli_query($conn, "ALTER TABLE comments ADD COLUMN parent_comment_id INT NULL DEFAULT NULL AFTER query_id");
    }
}

function getUserStats($conn, $userId)
{
    return [
        "questions" => (int) (mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM queries WHERE user_id=$userId"))['total'] ?? 0),
        "resources" => (int) (mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM resources WHERE user_id=$userId"))['total'] ?? 0),
        "comments" => (int) (mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM comments WHERE user_id=$userId"))['total'] ?? 0),
        "sos" => (int) (mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM queries WHERE user_id=$userId AND LOWER(category)='sos'"))['total'] ?? 0)
    ];
}

function renderAjaxCommentHtml($commentId, $queryId, $comment, $userName, $parentCommentId)
{
    $commentId = (int) $commentId;
    $queryId = (int) $queryId;
    $parentCommentId = (int) $parentCommentId;

    if ($parentCommentId > 0) {
        return '<div class="reply-item p-3" data-comment-id="' . $commentId . '">'
            . '<div class="d-flex justify-content-between align-items-start gap-2">'
            . '<small><strong>' . htmlspecialchars($userName) . ':</strong> ' . htmlspecialchars($comment) . '</small>'
            . '<a href="delete_comment.php?id=' . $commentId . '&mode=question" class="btn btn-outline-danger btn-sm rounded-pill">Delete</a>'
            . '</div>'
            . '</div>';
    }

    return '<div class="comment-item p-3" data-comment-id="' . $commentId . '">'
        . '<div class="d-flex justify-content-between align-items-start gap-2">'
        . '<small><strong>' . htmlspecialchars($userName) . ':</strong> ' . htmlspecialchars($comment) . '</small>'
        . '<div class="d-flex align-items-center gap-2 flex-shrink-0">'
        . '<button type="button" class="btn btn-link btn-sm text-decoration-none p-0 reply-toggle">Reply</button>'
        . '<a href="delete_comment.php?id=' . $commentId . '&mode=question" class="btn btn-outline-danger btn-sm rounded-pill">Delete</a>'
        . '</div>'
        . '</div>'
        . '<form class="comment-form comment-reply-form d-none mt-3" action="post_comment.php" method="POST">'
        . '<input type="hidden" name="query_id" value="' . $queryId . '">'
        . '<input type="hidden" name="parent_comment_id" value="' . $commentId . '">'
        . '<div class="input-group input-group-sm">'
        . '<input type="text" name="comment" class="form-control" placeholder="Reply to this comment..." required>'
        . '<button class="btn btn-outline-primary submit-btn" type="submit">Send</button>'
        . '</div>'
        . '</form>'
        . '<div class="comment-replies d-grid gap-2 mt-3"></div>'
        . '</div>';
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
$userName = $_SESSION['user_name'] ?? 'You';
ensureCommentReplySupport($conn);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $queryId = (int) ($_POST['query_id'] ?? 0);
    $parentCommentId = (int) ($_POST['parent_comment_id'] ?? 0);
    $comment = trim($_POST['comment'] ?? '');

    if ($queryId <= 0 || strlen($comment) < 2) {
        if (isAjaxRequest()) {
            sendJson(["success" => false, "message" => "Reply is too short."], 422);
        }
        header("Location: index.php?mode=question");
        exit();
    }

    $commentSafe = mysqli_real_escape_string($conn, $comment);
    $fileName = "NULL";

    if (!empty($_FILES['file']['name'])) {
        $allowed = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'gif', 'webp', 'txt', 'ppt', 'pptx'];
        $ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));

        if (in_array($ext, $allowed, true)) {
            $uploadDir = __DIR__ . "/uploads/comments/";
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $storedName = time() . "_" . basename($_FILES['file']['name']);
            $targetFile = $uploadDir . $storedName;

            if (move_uploaded_file($_FILES['file']['tmp_name'], $targetFile)) {
                $fileName = "'" . mysqli_real_escape_string($conn, $storedName) . "'";
            }
        }
    }

    mysqli_query($conn, "
        INSERT INTO comments (query_id, parent_comment_id, comment, file, user_id)
        VALUES ('$queryId', " . ($parentCommentId > 0 ? "'$parentCommentId'" : "NULL") . ", '$commentSafe', $fileName, '$userId')
    ");

    $newCommentId = (int) mysqli_insert_id($conn);

    if (isAjaxRequest()) {
        $commentHtml = renderAjaxCommentHtml($newCommentId, $queryId, $comment, $userName, $parentCommentId);

        $totalComments = (int) (mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM comments WHERE query_id=$queryId"))['total'] ?? 0);
        $stats = getUserStats($conn, $userId);

        sendJson([
            "success" => true,
            "message" => "Reply added successfully.",
            "query_id" => $queryId,
            "parent_comment_id" => $parentCommentId,
            "total_comments" => $totalComments,
            "comment_html" => $commentHtml,
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
