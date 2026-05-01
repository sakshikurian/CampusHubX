<?php
session_start();
include "../includes/db.php";

function isAjaxRequest()
{
    return (
        (!empty($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]) === "xmlhttprequest") ||
        (isset($_SERVER["HTTP_ACCEPT"]) && strpos($_SERVER["HTTP_ACCEPT"], "application/json") !== false)
    );
}

function sendJson($payload, $statusCode = 200)
{
    http_response_code($statusCode);
    header("Content-Type: application/json");
    echo json_encode($payload);
    exit();
}

if (!isset($_SESSION["user_id"])) {
    if (isAjaxRequest()) {
        sendJson(["success" => false, "message" => "Please log in first."], 401);
    }
    header("Location: ../index.php");
    exit();
}

$userId = (int) $_SESSION["user_id"];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $type = mysqli_real_escape_string($conn, trim($_POST["type"] ?? ""));
    $referenceId = (int) ($_POST["reference_id"] ?? 0);
    $reason = mysqli_real_escape_string($conn, trim($_POST["reason"] ?? ""));

    if (!in_array($type, ["post", "comment", "resource"], true)) {
        if (isAjaxRequest()) {
            sendJson(["success" => false, "message" => "Invalid content type."], 422);
        }
        header("Location: index.php");
        exit();
    }

    if ($referenceId <= 0) {
        if (isAjaxRequest()) {
            sendJson(["success" => false, "message" => "Invalid content reference."], 422);
        }
        header("Location: index.php");
        exit();
    }

    if (strlen($reason) < 5 || strlen($reason) > 500) {
        if (isAjaxRequest()) {
            sendJson(["success" => false, "message" => "Report reason must be between 5 and 500 characters."], 422);
        }
        header("Location: index.php");
        exit();
    }

    $tableMap = ["post" => "queries", "comment" => "comments", "resource" => "resources"];
    $table = $tableMap[$type];
    
    $checkQuery = mysqli_query($conn, "SELECT id FROM $table WHERE id=$referenceId");
    if (!$checkQuery || mysqli_num_rows($checkQuery) === 0) {
        if (isAjaxRequest()) {
            sendJson(["success" => false, "message" => "Content not found."], 404);
        }
        header("Location: index.php");
        exit();
    }

    $existingReport = mysqli_query($conn, "
        SELECT id FROM content_reports 
        WHERE user_id=$userId AND type='$type' AND reference_id=$referenceId
    ");

    if ($existingReport && mysqli_num_rows($existingReport) > 0) {
        if (isAjaxRequest()) {
            sendJson(["success" => false, "message" => "You have already reported this content."], 422);
        }
        header("Location: index.php");
        exit();
    }

    $insertReport = mysqli_query($conn, "
        INSERT INTO content_reports (user_id, type, reference_id, reason, created_at)
        VALUES ($userId, '$type', $referenceId, '$reason', NOW())
    ");

    if ($insertReport) {
        if (isAjaxRequest()) {
            sendJson(["success" => true, "message" => "Report submitted successfully. Thank you!"], 200);
        }
        header("Location: index.php?msg=reported");
        exit();
    } else {
        if (isAjaxRequest()) {
            sendJson(["success" => false, "message" => "Failed to submit report. Please try again."], 500);
        }
        header("Location: index.php?error=report_failed");
        exit();
    }
}

if ($_SERVER["REQUEST_METHOD"] === "GET") {
    $type = $_GET["type"] ?? null;
    $referenceId = $_GET["id"] ?? null;

    if (!$type || !$referenceId) {
        if (isAjaxRequest()) {
            sendJson(["success" => false, "message" => "Invalid parameters."], 422);
        }
        header("Location: index.php");
        exit();
    }

    $reportForm = [
        "success" => true,
        "form" => [
            "type" => htmlspecialchars($type),
            "reference_id" => (int) $referenceId,
            "reasons" => [
                "inappropriate" => "Inappropriate Content",
                "spam" => "Spam",
                "harassment" => "Harassment or Abuse",
                "copyright" => "Copyright Violation",
                "misleading" => "Misleading Information",
                "other" => "Other"
            ]
        ]
    ];

    if (isAjaxRequest()) {
        sendJson($reportForm);
    }

    header("Location: index.php");
    exit();
}

header("Location: index.php");
exit();
?>
