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
    $stats = [
        "questions" => 0,
        "resources" => 0,
        "comments" => 0,
        "sos" => 0
    ];

    $result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM queries WHERE user_id=$userId");
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        $stats["questions"] = (int) ($row['total'] ?? 0);
    }

    $result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM resources WHERE user_id=$userId");
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        $stats["resources"] = (int) ($row['total'] ?? 0);
    }

    $result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM comments WHERE user_id=$userId");
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        $stats["comments"] = (int) ($row['total'] ?? 0);
    }

    $result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM queries WHERE user_id=$userId AND LOWER(category)='sos'");
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        $stats["sos"] = (int) ($row['total'] ?? 0);
    }

    return $stats;
}

function getUserActivityTrend($conn, $userId, $days = 7)
{
    $days = max(1, (int) $days);
    $labels = [];
    $questions = [];
    $comments = [];
    $resources = [];
    $dateKeys = [];

    for ($i = $days - 1; $i >= 0; $i--) {
        $dateKey = date('Y-m-d', strtotime("-$i days"));
        $labels[] = date('d M', strtotime($dateKey));
        $dateKeys[] = $dateKey;
        $questions[$dateKey] = 0;
        $comments[$dateKey] = 0;
        $resources[$dateKey] = 0;
    }

    $sources = [
        'questions' => ['table' => 'queries', 'store' => &$questions],
        'comments' => ['table' => 'comments', 'store' => &$comments],
        'resources' => ['table' => 'resources', 'store' => &$resources],
    ];

    foreach ($sources as $source) {
        $result = mysqli_query($conn, "
            SELECT DATE(created_at) AS activity_date, COUNT(*) AS total
            FROM {$source['table']}
            WHERE user_id=$userId
              AND created_at >= DATE_SUB(CURDATE(), INTERVAL " . ($days - 1) . " DAY)
            GROUP BY DATE(created_at)
            ORDER BY activity_date ASC
        ");

        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $dateKey = $row['activity_date'];
                if (isset($source['store'][$dateKey])) {
                    $source['store'][$dateKey] = (int) $row['total'];
                }
            }
        }
    }

    return [
        'labels' => $labels,
        'questions' => array_values($questions),
        'comments' => array_values($comments),
        'resources' => array_values($resources),
    ];
}

function getQuestionCategoryBreakdown($conn, $userId)
{
    $categories = [
        'general' => 0,
        'coding' => 0,
        'lost' => 0,
        'sos' => 0,
    ];

    $result = mysqli_query($conn, "
        SELECT LOWER(category) AS category_name, COUNT(*) AS total
        FROM queries
        WHERE user_id=$userId
        GROUP BY LOWER(category)
    ");

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $categoryName = $row['category_name'] ?: 'general';
            $categories[$categoryName] = (int) $row['total'];
        }
    }

    return $categories;
}

function getDashboardHighlights($conn, $userId)
{
    $commentsReceived = 0;
    $result = mysqli_query($conn, "
        SELECT COUNT(*) AS total
        FROM comments c
        JOIN queries q ON c.query_id = q.id
        WHERE q.user_id=$userId
    ");
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        $commentsReceived = (int) ($row['total'] ?? 0);
    }

    $helpingOthers = 0;
    $result = mysqli_query($conn, "
        SELECT COUNT(*) AS total
        FROM comments c
        JOIN queries q ON c.query_id = q.id
        WHERE c.user_id=$userId AND q.user_id<>$userId
    ");
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        $helpingOthers = (int) ($row['total'] ?? 0);
    }

    $latestPostAt = null;
    $result = mysqli_query($conn, "
        SELECT MAX(activity_at) AS latest_activity
        FROM (
            SELECT created_at AS activity_at FROM queries WHERE user_id=$userId
            UNION ALL
            SELECT created_at AS activity_at FROM comments WHERE user_id=$userId
            UNION ALL
            SELECT created_at AS activity_at FROM resources WHERE user_id=$userId
        ) recent_activity
    ");
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        $latestPostAt = $row['latest_activity'] ?? null;
    }

    $resourceCount = 0;
    $result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM resources WHERE user_id=$userId");
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        $resourceCount = (int) ($row['total'] ?? 0);
    }

    return [
        'comments_received' => $commentsReceived,
        'helping_others' => $helpingOthers,
        'latest_activity' => $latestPostAt,
        'engagement_score' => $commentsReceived + $helpingOthers + ($resourceCount * 2),
    ];
}

function getRecentUserActivity($conn, $userId, $limit = 6)
{
    $limit = max(1, (int) $limit);
    $items = [];

    $result = mysqli_query($conn, "
        SELECT activity_type, activity_text, created_at
        FROM (
            SELECT 'Question' AS activity_type, question AS activity_text, created_at
            FROM queries
            WHERE user_id=$userId
            UNION ALL
            SELECT 'Comment' AS activity_type, comment AS activity_text, created_at
            FROM comments
            WHERE user_id=$userId
            UNION ALL
            SELECT 'Resource' AS activity_type, title AS activity_text, created_at
            FROM resources
            WHERE user_id=$userId
        ) activity_feed
        ORDER BY created_at DESC
        LIMIT $limit
    ");

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $items[] = $row;
        }
    }

    return $items;
}

function renderCommentItem($conn, $commentRow, $userId, $queryId)
{
    $commentId = (int) $commentRow['id'];
    $html = '<div class="comment-item p-3" data-comment-id="' . $commentId . '">';
    $html .= '<div class="d-flex justify-content-between align-items-start gap-2">';
    $html .= '<small><strong>' . htmlspecialchars($commentRow['name']) . ':</strong> ' . htmlspecialchars($commentRow['comment']) . '</small>';
    $html .= '<div class="d-flex align-items-center gap-2 flex-shrink-0">';
    $html .= '<button type="button" class="btn btn-link btn-sm text-decoration-none p-0 reply-toggle">Reply</button>';
    if ((int) $commentRow['user_id'] === $userId) {
        $html .= '<a href="delete_comment.php?id=' . $commentId . '&mode=question" class="btn btn-outline-danger btn-sm rounded-pill">Delete</a>';
    }
    $html .= '</div>';
    $html .= '</div>';
    $html .= '<form class="comment-form comment-reply-form d-none mt-3" action="post_comment.php" method="POST">';
    $html .= '<input type="hidden" name="query_id" value="' . (int) $queryId . '">';
    $html .= '<input type="hidden" name="parent_comment_id" value="' . $commentId . '">';
    $html .= '<div class="input-group input-group-sm">';
    $html .= '<input type="text" name="comment" class="form-control" placeholder="Reply to this comment..." required>';
    $html .= '<button class="btn btn-outline-primary submit-btn" type="submit">Send</button>';
    $html .= '</div>';
    $html .= '</form>';

    $replyResult = mysqli_query($conn, "
        SELECT c.id, c.comment, c.user_id, u.name
        FROM comments c
        JOIN users u ON c.user_id=u.id
        WHERE c.query_id=" . (int) $queryId . " AND c.parent_comment_id=$commentId
        ORDER BY c.id ASC
    ");

    $html .= '<div class="reply-box">
    <div class="comment-replies d-grid gap-2 mt-3">';
    while ($reply = mysqli_fetch_assoc($replyResult)) {
        $html .= '<div class="reply-item p-3" data-comment-id="' . (int) $reply['id'] . '">';
        $html .= '<div class="d-flex justify-content-between align-items-start gap-2">';
        $html .= '<small><strong>' . htmlspecialchars($reply['name']) . ':</strong> ' . htmlspecialchars($reply['comment']) . '</small>';
        if ((int) $reply['user_id'] === $userId) {
            $html .= '<a href="delete_comment.php?id=' . (int) $reply['id'] . '&mode=question" class="btn btn-outline-danger btn-sm rounded-pill">Delete</a>';
        }
        $html .= '</div>';
        $html .= '</div>';
    }
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';

    return $html;
}

/* AUTO DELETE FILES OLDER THAN 30 DAYS */
$oldFiles = mysqli_query($conn, "
    SELECT id, file_path
    FROM resources
    WHERE created_at < NOW() - INTERVAL 30 DAY
");

while ($f = mysqli_fetch_assoc($oldFiles)) {
    $file = "uploads/" . $f['file_path'];

    if (file_exists($file)) {
        unlink($file);
    }

    mysqli_query($conn, "DELETE FROM resources WHERE id=" . (int) $f['id']);
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = (int) $_SESSION['user_id'];
$userName = $_SESSION['user_name'];
$filter = strtolower($_GET['filter'] ?? "all");
$mode = $_GET['mode'] ?? "question";

ensureCommentReplySupport($conn);
$stats = getUserStats($conn, $userId);

$resourceTypes = [];
$typesRes = mysqli_query($conn, "SELECT file_path FROM resources WHERE user_id=$userId");
while ($typeRow = mysqli_fetch_assoc($typesRes)) {
    $ext = strtolower(pathinfo($typeRow['file_path'], PATHINFO_EXTENSION));
    $ext = $ext ?: "file";
    $resourceTypes[$ext] = ($resourceTypes[$ext] ?? 0) + 1;
}
arsort($resourceTypes);
$topType = !empty($resourceTypes) ? strtoupper(array_key_first($resourceTypes)) : "N/A";
$supportedFormats = ["PDF", "DOC", "DOCX", "PPT", "PPTX", "XLSX", "JPG", "PNG", "ZIP", "RAR"];
$activity7 = getUserActivityTrend($conn, $userId, 7);
$activity30 = getUserActivityTrend($conn, $userId, 30);
$categoryBreakdown = getQuestionCategoryBreakdown($conn, $userId);
$dashboardHighlights = getDashboardHighlights($conn, $userId);
$recentActivity = getRecentUserActivity($conn, $userId, 6);
$categoryTotal = array_sum($categoryBreakdown);
$topCategory = $categoryTotal > 0 ? strtoupper((string) array_search(max($categoryBreakdown), $categoryBreakdown, true)) : "N/A";
$latestActivityLabel = !empty($dashboardHighlights['latest_activity']) ? date("d M Y, h:i A", strtotime($dashboardHighlights['latest_activity'])) : "No activity yet";
$chartPayload = [
    'activity' => [
        '7' => $activity7,
        '30' => $activity30,
    ],
    'categories' => [
        'labels' => ['General', 'Coding', 'Lost & Found', 'SOS'],
        'values' => [
            (int) ($categoryBreakdown['general'] ?? 0),
            (int) ($categoryBreakdown['coding'] ?? 0),
            (int) ($categoryBreakdown['lost'] ?? 0),
            (int) ($categoryBreakdown['sos'] ?? 0),
        ],
    ],
];

$where = "";
if ($filter !== "all") {
    $filterSafe = mysqli_real_escape_string($conn, $filter);
    $where = "WHERE LOWER(q.category)='$filterSafe'";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ResourceHub Dashboard</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --brand: #0f4c81;
            --brand-dark: #0a2f4f;
            --brand-soft: #e9f4ff;
            --accent: #ff9f1c;
            --success-soft: #e9f8f1;
            --danger-soft: #ffe8ea;
            --surface: rgba(255, 255, 255, 0.9);
            --text-main: #16324f;
            --text-muted: #5f7488;
            --border-soft: rgba(15, 76, 129, 0.12);
            --shadow-soft: 0 18px 40px rgba(15, 76, 129, 0.12);
        }

        * {
            font-family: "Manrope", sans-serif;
        }

        body {
            min-height: 100vh;
            color: var(--text-main);
            background:
                radial-gradient(circle at top left, rgba(255, 159, 28, 0.18), transparent 24%),
                radial-gradient(circle at top right, rgba(15, 76, 129, 0.2), transparent 22%),
                linear-gradient(160deg, #eff7ff 0%, #f5fbff 45%, #eef4f8 100%);
        }

        .navbar-shell {
            background: linear-gradient(120deg, var(--brand-dark), var(--brand));
            box-shadow: 0 14px 30px rgba(10, 47, 79, 0.24);
        }

        .brand-mark {
            width: 44px;
            height: 44px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 14px;
            background: rgba(255, 255, 255, 0.12);
            font-size: 1.2rem;
        }

        .glass-card,
        .panel-card,
        .feed-card {
            border: 1px solid var(--border-soft);
            border-radius: 24px;
            background: var(--surface);
            backdrop-filter: blur(10px);
            box-shadow: var(--shadow-soft);
        }

        .hero-card {
            position: relative;
            overflow: hidden;
            border-radius: 30px;
            background: linear-gradient(135deg, rgba(15, 76, 129, 0.96), rgba(13, 110, 253, 0.76));
            color: #fff;
        }

        .hero-card::after {
            content: "";
            position: absolute;
            right: -80px;
            top: -60px;
            width: 220px;
            height: 220px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.08);
        }

        .hero-chip {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 14px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.16);
            font-size: 0.9rem;
        }

        .stat-card {
            height: 100%;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .stat-card:hover,
        .feed-card:hover,
        .panel-card:hover {
            transform: translateY(-4px);
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.15rem;
            font-weight: 800;
        }
.reply-box {
    max-height: 150px;   /* smaller than comments */
    overflow-y: auto;
    padding-right: 5px;
}

/* scrollbar styling */
.reply-box::-webkit-scrollbar {
    width: 5px;
}

.reply-box::-webkit-scrollbar-thumb {
    background: #bbb;
    border-radius: 10px;
}
        .bg-soft-primary { background: var(--brand-soft); color: var(--brand); }
        .bg-soft-warning { background: #fff3df; color: #ad6a00; }
        .bg-soft-success { background: var(--success-soft); color: #147a52; }
        .bg-soft-danger { background: var(--danger-soft); color: #b4232f; }

        .mode-switch .btn {
            border-radius: 999px;
            padding: 0.8rem 1.1rem;
            font-weight: 700;
        }

        .mode-switch .btn.active {
            background: var(--brand);
            border-color: var(--brand);
            color: #fff;
        }

        .section-heading {
            font-weight: 800;
            letter-spacing: -0.03em;
        }

        .feed-card {
            animation: riseUp 0.45s ease;
        }

        .query-card.sos-card {
            background: linear-gradient(135deg, rgba(220, 53, 69, 0.14), rgba(255, 255, 255, 0.96));
            border-color: rgba(220, 53, 69, 0.25);
        }

        .comment-item {
            border-radius: 16px;
            background: #f7fbff;
            border: 1px solid rgba(15, 76, 129, 0.08);
        }

        .comment-replies {
            margin-left: 1.25rem;
            padding-left: 1rem;
            border-left: 2px solid rgba(15, 76, 129, 0.12);
        }

        .reply-item {
            border-radius: 14px;
            background: #ffffff;
            border: 1px solid rgba(15, 76, 129, 0.08);
        }

        .resource-row {
            transition: background-color 0.2s ease;
        }

        .resource-row:hover {
            background: #f7fbff;
        }

        .resource-link {
            color: var(--text-main);
            text-decoration: none;
            font-weight: 700;
        }

        .resource-link:hover {
            color: var(--brand);
        }

        .search-input,
        .form-control,
        .form-select {
            border-radius: 16px;
            border-color: rgba(15, 76, 129, 0.14);
            min-height: 48px;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: rgba(13, 110, 253, 0.55);
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.14);
        }

        textarea.form-control {
            min-height: 118px;
        }

        .floating-panel {
            position: sticky;
            top: 24px;
        }

        .book-card {
            border: 1px solid rgba(15, 76, 129, 0.1);
            border-radius: 18px;
            background: linear-gradient(180deg, #fff, #f7fbff);
        }

        .format-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.45rem 0.8rem;
            border-radius: 999px;
            background: rgba(15, 76, 129, 0.08);
            color: var(--brand);
            font-size: 0.85rem;
            font-weight: 700;
            letter-spacing: 0.02em;
        }

        .mini-muted {
            color: var(--text-muted);
            font-size: 0.92rem;
        }

        .toast-wrap {
            z-index: 1080;
        }

        .empty-state {
            border: 1px dashed rgba(15, 76, 129, 0.2);
            border-radius: 22px;
            background: rgba(255, 255, 255, 0.55);
        }

        .btn-brand {
            background: var(--brand);
            border-color: var(--brand);
            color: #fff;
        }

        .btn-brand:hover {
            background: #0c3f6b;
            border-color: #0c3f6b;
            color: #fff;
        }

        .btn-soft {
            background: rgba(15, 76, 129, 0.08);
            color: var(--brand);
            border: 1px solid rgba(15, 76, 129, 0.08);
        }

        .btn-soft:hover {
            background: rgba(15, 76, 129, 0.14);
            color: var(--brand);
        }

        .analytics-card {
            position: relative;
            overflow: hidden;
        }

        .analytics-card::before {
            content: "";
            position: absolute;
            inset: auto -80px -80px auto;
            width: 180px;
            height: 180px;
            border-radius: 50%;
            background: rgba(15, 76, 129, 0.06);
        }

        .chart-stage {
            position: relative;
            height: 320px;
        }

        .chart-stage canvas {
            width: 100% !important;
            height: 100% !important;
        }

        .insight-chip {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 0.65rem 1rem;
            border-radius: 999px;
            background: rgba(15, 76, 129, 0.08);
            color: var(--brand);
            font-weight: 700;
        }

        .timeline-item {
            padding: 1rem 0;
            border-bottom: 1px solid rgba(15, 76, 129, 0.08);
        }

        .timeline-item:last-child {
            border-bottom: 0;
            padding-bottom: 0;
        }

        .timeline-badge {
            min-width: 84px;
        }

        @keyframes riseUp {
            from {
                opacity: 0;
                transform: translateY(18px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
 body.dark-mode {
            background-color: #3e3d3d !important;
            color: #ffffff !important;
        }

        .dark-mode .glass-card,
        .dark-mode .panel-card,
        .dark-mode .feed-card {
            background-color: #1e1e1e !important;
            color: #ffffff !important;
            border: 1px solid #2c2c2c;
        }

        .dark-mode .hero-card {
            background: linear-gradient(135deg, #1e1e1e, #2c2c2c);
        }

        .dark-mode .comment-item {
            background: #2a2a2a;
            border: 1px solid #444;
        }

        .dark-mode .reply-item {
            background: #1e1e1e;
            border: 1px solid #444;
        }

        .dark-mode .book-card {
            background: linear-gradient(180deg, #1e1e1e, #2a2a2a);
            border: 1px solid #444;
        }

        .dark-mode .navbar-shell {
            background: linear-gradient(120deg, #000, #1a1a1a);
        }

        .dark-mode .form-control,
        .dark-mode .form-select {
            background-color: #2a2a2a !important;
            color: #ffffff !important;
            border: 1px solid #444;
        }

        .dark-mode .btn-outline-primary {
            color: #4dabf7 !important;
            border-color: #4dabf7 !important;
        }

.comment-box {
    max-height: 250px;   /* control height */
    overflow-y: auto;    /* enable vertical scroll */
    padding-right: 5px;
}

/* optional scrollbar styling */
.comment-box::-webkit-scrollbar {
    width: 6px;
}

.comment-box::-webkit-scrollbar-thumb {
    background: #bbb;
    border-radius: 10px;
}.comment-more {
    max-height: 250px;
    overflow-y: auto;
}
        .dark-mode .btn-outline-primary:hover {
            background-color: #4dabf7 !important;
            color: #000 !important;
        }
        @media (max-width: 991.98px) {
            .floating-panel {
                position: static;
            }
        }
    </style>
</head>
<body data-active-mode="<?= htmlspecialchars($mode) ?>">
    <nav class="navbar navbar-expand-lg navbar-dark navbar-shell py-3">
        <div class="container">
            <div class="d-flex align-items-center gap-3">
                <div class="brand-mark">RH</div>
                <div>
                    <a class="navbar-brand fw-bold mb-0" href="../dashboard.php">ResourceHub</a>
                    <div class="small text-white-50">Share resources, solve doubts, help fast</div>
                </div>
            </div>

            <div class="d-flex align-items-center gap-3 text-white mt-3 mt-lg-0">
                <button id="darkModeToggle" class="btn btn-outline-light me-2">
                    🌙
                </button>
                <div class="text-end">
                    <div class="fw-semibold">Welcome back, <?= htmlspecialchars($userName) ?></div>
                    <div class="small text-white-50">Community learning dashboard</div>
                </div>
                <a href="../logout.php" class="btn btn-outline-light btn-sm rounded-pill px-3">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container py-4 py-lg-5">
        <div class="hero-card p-4 p-lg-5 mb-4">
            <div class="row g-4 align-items-center position-relative">
                <div class="col-lg-7">
                    <div class="hero-chip mb-3">Live campus collaboration</div>
                    <h1 class="display-6 fw-bold mb-3">A smarter space to ask, share, and discover useful study material.</h1>
                    <p class="mb-4 text-white-50">This upgraded dashboard now includes AJAX posting, cleaner Bootstrap sections, instant search, category filters, and curated book discovery without interrupting your workflow.</p>
                    <div class="d-flex flex-wrap gap-2">
                        <button class="btn btn-light rounded-pill px-4 fw-semibold mode-trigger" data-mode="question">Open Discussions</button>
                        <button class="btn btn-outline-light rounded-pill px-4 fw-semibold mode-trigger" data-mode="file">Browse Resources</button>
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="glass-card p-4 text-dark">
                        <div class="row g-3">
                            <div class="col-6">
                                <div class="stat-card p-3 h-100">
                                    <div class="stat-icon bg-soft-primary mb-3">Q</div>
                                    <div class="fs-3 fw-bold stats-questions"><?= $stats["questions"] ?></div>
                                    <div class="mini-muted">Your questions</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="stat-card p-3 h-100">
                                    <div class="stat-icon bg-soft-success mb-3">R</div>
                                    <div class="fs-3 fw-bold stats-resources"><?= $stats["resources"] ?></div>
                                    <div class="mini-muted">Your resources</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="stat-card p-3 h-100">
                                    <div class="stat-icon bg-soft-warning mb-3">C</div>
                                    <div class="fs-3 fw-bold stats-comments"><?= $stats["comments"] ?></div>
                                    <div class="mini-muted">Your comments</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="stat-card p-3 h-100">
                                    <div class="stat-icon bg-soft-danger mb-3">SOS</div>
                                    <div class="fs-3 fw-bold stats-sos"><?= $stats["sos"] ?></div>
                                    <div class="mini-muted">Your SOS posts</div>
                                </div>
                            </div>
                        </div>
                        
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-md-6 col-xl-3">
                <div class="panel-card stat-card p-4">
                    <div class="stat-icon bg-soft-primary mb-3">Q</div>
                    <div class="fw-bold fs-3 stats-questions"><?= $stats["questions"] ?></div>
                    <div class="mini-muted">Questions you posted</div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="panel-card stat-card p-4">
                    <div class="stat-icon bg-soft-success mb-3">F</div>
                    <div class="fw-bold fs-3 stats-resources"><?= $stats["resources"] ?></div>
                    <div class="mini-muted">Files you uploaded</div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="panel-card stat-card p-4">
                    <div class="stat-icon bg-soft-warning mb-3">TOP</div>
                    <div class="fw-bold fs-3"><?= htmlspecialchars($topType) ?></div>
                    <div class="mini-muted">Your top file type</div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="panel-card stat-card p-4">
                    <div class="stat-icon bg-soft-danger mb-3">SOS</div>
                    <div class="fw-bold fs-3 stats-sos"><?= $stats["sos"] ?></div>
                    <div class="mini-muted">Your urgent posts</div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-xl-8">
                <div class="panel-card analytics-card p-4 h-100">
                    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
                        <div>
                            <h2 class="section-heading fs-3 mb-1">Your Analytics Dashboard</h2>
                            <div class="mini-muted">Track how often you post, comment, and upload across the platform.</div>
                        </div>
                        <div class="btn-group" role="group" aria-label="Activity range">
                            <button type="button" class="btn btn-outline-primary chart-range-btn active" data-range="7">Last 7 days</button>
                            <button type="button" class="btn btn-outline-primary chart-range-btn" data-range="30">Last 30 days</button>
                        </div>
                    </div>
                    <div class="chart-stage">
                        <canvas id="activityChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-xl-4">
                <div class="panel-card analytics-card p-4 h-100">
                    <h3 class="section-heading fs-4 mb-1">Post Categories</h3>
                    <div class="mini-muted mb-4">See which type of questions you contribute most often.</div>
                    <div class="chart-stage" style="height: 260px;">
                        <canvas id="categoryChart"></canvas>
                    </div>
                    <div class="d-flex flex-wrap gap-2 mt-4">
                        <span class="insight-chip">Top category: <?= htmlspecialchars($topCategory) ?></span>
                        <span class="insight-chip">Latest activity: <?= htmlspecialchars($latestActivityLabel) ?></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-lg-4">
                <div class="panel-card p-4 h-100">
                    <h3 class="section-heading fs-5 mb-3">Engagement Snapshot</h3>
                    <div class="d-grid gap-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="mini-muted">Comments received on your posts</span>
                            <strong><?= (int) $dashboardHighlights['comments_received'] ?></strong>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="mini-muted">Helpful replies to other users</span>
                            <strong><?= (int) $dashboardHighlights['helping_others'] ?></strong>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="mini-muted">Engagement score</span>
                            <strong><?= (int) $dashboardHighlights['engagement_score'] ?></strong>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-8">
                <div class="panel-card p-4 h-100">
                    <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
                        <div>
                            <h3 class="section-heading fs-5 mb-1">Recent Activity Timeline</h3>
                            <div class="mini-muted">A quick view of your latest actions on the platform.</div>
                        </div>
                    </div>
                    <?php if (empty($recentActivity)) { ?>
                        <div class="empty-state p-4 text-center">
                            <div class="fw-semibold mb-1">No activity yet</div>
                            <div class="mini-muted mb-0">Start by posting a question, comment, or resource.</div>
                        </div>
                    <?php } else { ?>
                        <div>
                            <?php foreach ($recentActivity as $activityItem) { ?>
                                <div class="timeline-item d-flex flex-column flex-md-row gap-3 align-items-md-start">
                                    <span class="badge text-bg-light rounded-pill px-3 py-2 timeline-badge"><?= htmlspecialchars($activityItem['activity_type']) ?></span>
                                    <div class="flex-grow-1">
                                        <div class="fw-semibold"><?= htmlspecialchars(mb_strimwidth($activityItem['activity_text'] ?? '', 0, 100, '...')) ?></div>
                                        <div class="mini-muted"><?= date("d M Y, h:i A", strtotime($activityItem['created_at'])) ?></div>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>

        <div class="panel-card p-3 p-lg-4 mb-4">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
                <div>
                    <div class="section-heading fs-4">Workspace Modes</div>
                    <div class="mini-muted">Switch between discussions and resource-sharing without leaving the page.</div>
                </div>
                <div class="btn-group mode-switch" role="group">
                    <button type="button" class="btn btn-outline-primary mode-trigger" data-mode="question">Discussion Hub</button>
                    <button type="button" class="btn btn-outline-primary mode-trigger" data-mode="file">Resource Vault</button>
                </div>
            </div>
        </div>

        <div id="questionSection" class="mode-section">
            <div class="row g-4">
                <div class="col-lg-4">
                    <div class="floating-panel d-grid gap-4">
                        <div class="panel-card p-4">
                            <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                                <div>
                                    <h2 class="section-heading fs-4 mb-1">Post a Question</h2>
                                    <div class="mini-muted">Use AJAX to publish instantly and keep the conversation moving.</div>
                                </div>
                                <span class="badge text-bg-light rounded-pill px-3 py-2">Live</span>
                            </div>
                            <form id="postQueryForm" action="post_query.php" method="POST" enctype="multipart/form-data">
                                <label class="form-label fw-semibold">Category</label>
                                <select name="category" class="form-select mb-3">
                                    <option value="general">General</option>
                                    <option value="coding">Coding Queries</option>
                                    <option value="lost">Lost & Found</option>
                                    <option value="sos">SOS</option>
                                </select>
                                <label class="form-label fw-semibold">Question</label>
                                <textarea name="question" class="form-control mb-3" placeholder="Describe the issue clearly so others can help faster..." required></textarea>
                                <label class="form-label fw-semibold">Optional image</label>
                                <input type="file" name="image" class="form-control mb-3" accept=".jpg,.jpeg,.png,.gif,.webp">
                                <div class="d-flex justify-content-between align-items-center gap-3">
                                    <small class="mini-muted">Minimum 10 characters.</small>
                                    <button class="btn btn-brand rounded-pill px-4 submit-btn" type="submit">Post Question</button>
                                </div>
                            </form>
                        </div>

                        <div class="panel-card p-4">
                            <h3 class="section-heading fs-5 mb-3">Discussion Controls</h3>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Search questions</label>
                                <input type="search" id="questionSearch" class="form-control search-input" placeholder="Search by text, author, or category">
                            </div>
                            <div>
                                <label class="form-label fw-semibold">Category filter</label>
                                <select id="discussionFilter" class="form-select">
                                    <option value="all" <?= $filter === "all" ? "selected" : "" ?>>All discussions</option>
                                    <option value="general" <?= $filter === "general" ? "selected" : "" ?>>General</option>
                                    <option value="coding" <?= $filter === "coding" ? "selected" : "" ?>>Coding</option>
                                    <option value="lost" <?= $filter === "lost" ? "selected" : "" ?>>Lost & Found</option>
                                    <option value="sos" <?= $filter === "sos" ? "selected" : "" ?>>SOS</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-8">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-3">
                        <div>
                            <h2 class="section-heading fs-3 mb-1">Discussion Feed</h2>
                            <div class="mini-muted">Recent community updates with inline replies and priority SOS visibility.</div>
                        </div>
                        <span class="badge rounded-pill text-bg-light px-3 py-2" id="questionResultBadge">Showing all posts</span>
                    </div>

                    <div id="questionFeed">
                        <?php
                        $q = mysqli_query($conn, "
                            SELECT q.*, u.name
                            FROM queries q
                            JOIN users u ON q.user_id=u.id
                            $where
                            ORDER BY CASE WHEN LOWER(q.category)='sos' THEN 0 ELSE 1 END, q.id DESC
                        ");

                        if (mysqli_num_rows($q) === 0) {
                            ?>
                            <div class="empty-state p-5 text-center">
                                <h3 class="fw-bold mb-2">No discussions yet</h3>
                                <p class="mini-muted mb-0">Start the first conversation and build the community momentum.</p>
                            </div>
                            <?php
                        }

                        while ($row = mysqli_fetch_assoc($q)) {
                            $cid = (int) $row['id'];
                            $category = strtolower($row['category'] ?? "general");
                            $tagColor = "secondary";
                            $cardClass = "query-card";

                            if ($category === "coding") {
                                $tagColor = "primary";
                            } elseif ($category === "lost") {
                                $tagColor = "warning text-dark";
                            } elseif ($category === "sos") {
                                $tagColor = "danger";
                                $cardClass .= " sos-card";
                            } elseif ($category === "general") {
                                $tagColor = "info text-dark";
                            }

                            $countRes = mysqli_query($conn, "SELECT COUNT(*) AS total FROM comments WHERE query_id=$cid");
                            $totalComments = (int) (mysqli_fetch_assoc($countRes)['total'] ?? 0);
                            $topLevelRes = mysqli_query($conn, "SELECT COUNT(*) AS total FROM comments WHERE query_id=$cid AND parent_comment_id IS NULL");
                            $topLevelComments = (int) (mysqli_fetch_assoc($topLevelRes)['total'] ?? 0);
                            ?>
                            <div class="feed-card <?= $cardClass ?> p-4 mb-3 searchable-question"
                                data-category="<?= htmlspecialchars($category) ?>"
                                data-search="<?= htmlspecialchars(strtolower($row['question'] . ' ' . $row['name'] . ' ' . $category)) ?>">

                                <div class="d-flex justify-content-between gap-3 align-items-start mb-3">
                                    <div>
                                        <?php if ($category === "sos") { ?>
                                            <div class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3 py-2 mb-2">Emergency SOS Alert</div>
                                        <?php } ?>
                                        <p class="fs-5 fw-semibold mb-2"><?= nl2br(htmlspecialchars($row['question'])) ?></p>
                                        <div class="d-flex flex-wrap gap-2 align-items-center">
                                            <span class="badge bg-<?= $tagColor ?> rounded-pill px-3 py-2"><?= strtoupper($category) ?></span>
                                            <span class="mini-muted">Posted by <?= htmlspecialchars($row['name']) ?></span>
                                            <span class="mini-muted"><?= date("d M Y, h:i A", strtotime($row['created_at'])) ?></span>
                                        </div>
                                    </div>

                                    <div class="d-flex align-items-center gap-2">
                                        <?php if (!empty($row['image'])) { ?>
                                            <a href="uploads/questions/<?= htmlspecialchars($row['image']) ?>" target="_blank" class="btn btn-soft rounded-pill btn-sm">View Image</a>
                                        <?php } ?>
                                        <?php if ((int) $row['user_id'] === $userId) { ?>
                                            <a href="delete_query.php?id=<?= $cid ?>&mode=question" class="btn btn-outline-danger rounded-pill btn-sm" onclick="return confirm('Delete this question?')">Delete</a>
                                        <?php } ?>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div class="mini-muted"><span class="comment-total"><?= $totalComments ?></span> replies</div>
                                    <?php if ($totalComments > 2) { ?>
                                        <button type="button" class="btn btn-link text-decoration-none p-0 toggle-comments" data-target="#more-comments-<?= $cid ?>">See more comments</button>
                                    <?php } ?>
                                </div>

                                <form class="comment-form" action="post_comment.php" method="POST">
                                    <input type="hidden" name="query_id" value="<?= $cid ?>">
                                    <div class="input-group mb-3">
                                        <input type="text" name="comment" class="form-control" placeholder="Write a helpful reply..." required>
                                        <button class="btn btn-outline-primary submit-btn" type="submit">Reply</button>
                                    </div>
                                </form>

                                <div class="comment-box">
    <div class="comment-list d-grid gap-2">
                                    <?php
                                    $c = mysqli_query($conn, "
                                        SELECT c.id, c.comment, c.user_id, u.name
                                        FROM comments c
                                        JOIN users u ON c.user_id=u.id
                                        WHERE c.query_id=$cid AND c.parent_comment_id IS NULL
                                        ORDER BY c.id DESC
                                        LIMIT 2
                                    ");

                                    while ($com = mysqli_fetch_assoc($c)) {
                                        echo renderCommentItem($conn, $com, $userId, $cid);
                                    }
                                    ?>
                                </div>
                                </div>

                                <?php if ($topLevelComments > 2) { ?>
                                    <div id="more-comments-<?= $cid ?>" class="comment-more mt-2 d-none">
                                        <div class="d-grid gap-2">
                                            <?php
                                            $more = mysqli_query($conn, "
                                                SELECT c.id, c.comment, c.user_id, u.name
                                                FROM comments c
                                                JOIN users u ON c.user_id=u.id
                                                WHERE c.query_id=$cid AND c.parent_comment_id IS NULL
                                                ORDER BY c.id DESC
                                                LIMIT 100 OFFSET 2
                                            ");

                                            while ($m = mysqli_fetch_assoc($more)) {
                                                echo renderCommentItem($conn, $m, $userId, $cid);
                                            }
                                            ?>
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>
                            <?php
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>

        <div id="fileSection" class="mode-section d-none">
            <div class="row g-4">
                <div class="col-lg-4">
                    <div class="floating-panel d-grid gap-4">
                        <div class="panel-card p-4">
                            <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                                <div>
                                    <h2 class="section-heading fs-4 mb-1">Share Resources</h2>
                                    <div class="mini-muted">Upload documents with AJAX and keep study material organized.</div>
                                </div>
                                <span class="badge text-bg-success rounded-pill px-3 py-2">Boosted</span>
                            </div>
                            <form id="resourceUploadForm" action="post_resource.php" method="POST" enctype="multipart/form-data">
                                <label class="form-label fw-semibold">Description</label>
                                <textarea name="description" class="form-control mb-3" placeholder="Explain what this resource helps with..." required></textarea>
                                <label class="form-label fw-semibold">Files</label>
                                <input type="file" name="files[]" class="form-control mb-2" multiple required>
                                <small class="mini-muted d-block mb-3">Upload up to 5 files. PDFs, images, docs, slides, spreadsheets and archives are supported.</small>
                                <button class="btn btn-success rounded-pill px-4 submit-btn" type="submit">Upload Files</button>
                            </form>
                        </div>

                        <div class="panel-card p-4">
                            <h3 class="section-heading fs-5 mb-3">Discover Free Books</h3>
                            <div class="input-group mb-3">
                                <input type="text" id="bookQuery" class="form-control" placeholder="Search books like engineering, php, design">
                                <button class="btn btn-brand" type="button" id="loadBooksBtn">Search</button>
                            </div>
                            <div id="apiBooks" class="d-grid gap-3"></div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-8">
                    <div class="panel-card p-4">
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
                            <div>
                                <h2 class="section-heading fs-3 mb-1">Shared Resources</h2>
                                <div class="mini-muted">Live search makes it easier to find useful files fast.</div>
                            </div>
                            <div class="d-flex gap-2 w-100 w-md-auto">
                                <input type="search" id="resourceSearch" class="form-control search-input" placeholder="Search by title, uploader, or type">
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead>
                                    <tr class="table-light">
                                        <th class="rounded-start-4">Title</th>
                                        <th>Uploaded By</th>
                                        <th>Date</th>
                                        <th>Time</th>
                                        <th>Type</th>
                                        <th class="rounded-end-4 text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="resourceTableBody">
                                    <?php
                                    $r = mysqli_query($conn, "
                                        SELECT r.*, u.name
                                        FROM resources r
                                        JOIN users u ON r.user_id = u.id
                                        ORDER BY r.id DESC
                                    ");

                                    if (mysqli_num_rows($r) === 0) {
                                        echo '<tr><td colspan="6" class="py-5 text-center mini-muted">No files uploaded yet. Share the first resource and make this vault useful.</td></tr>';
                                    }

                                    while ($res = mysqli_fetch_assoc($r)) {
                                        $file = $res['file_path'];
                                        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                                        $badge = "secondary";

                                        if ($ext === "pdf") {
                                            $badge = "danger";
                                        } elseif (in_array($ext, ["doc", "docx"])) {
                                            $badge = "primary";
                                        } elseif (in_array($ext, ["ppt", "pptx"])) {
                                            $badge = "warning text-dark";
                                        } elseif (in_array($ext, ["jpg", "jpeg", "png", "gif", "webp"])) {
                                            $badge = "success";
                                        } elseif (in_array($ext, ["xls", "xlsx"])) {
                                            $badge = "info text-dark";
                                        }

                                        $date = date("d M Y", strtotime($res['created_at'] ?? "now"));
                                        $time = date("h:i A", strtotime($res['created_at'] ?? "now"));
                                        $searchData = strtolower(($res['title'] ?? '') . ' ' . ($res['name'] ?? '') . ' ' . $ext . ' ' . ($res['description'] ?? ''));
                                        ?>
                                        <tr class="resource-row searchable-resource" data-search="<?= htmlspecialchars($searchData) ?>">
                                            <td>
                                                <div class="fw-semibold"><?= htmlspecialchars($res['title']) ?></div>
                                                <div class="mini-muted"><?= htmlspecialchars($res['description'] ?? 'No description added') ?></div>
                                            </td>
                                            <td><?= htmlspecialchars($res['name']) ?></td>
                                            <td><?= $date ?></td>
                                            <td><?= $time ?></td>
                                            <td><span class="badge bg-<?= $badge ?> rounded-pill px-3 py-2"><?= strtoupper($ext ?: "FILE") ?></span></td>
                                            <td class="text-end">
                                                <div class="d-flex justify-content-end gap-2 flex-wrap">
                                                    <a href="uploads/<?= htmlspecialchars($file) ?>" target="_blank" class="btn btn-soft btn-sm rounded-pill">Open</a>
                                                    <a href="uploads/<?= htmlspecialchars($file) ?>" download class="btn btn-outline-primary btn-sm rounded-pill">Download</a>
                                                    <?php if ((int) $res['user_id'] === $userId) { ?>
                                                        <a href="delete_resource.php?id=<?= (int) $res['id'] ?>" onclick="return confirm('Delete this file?')" class="btn btn-outline-danger btn-sm rounded-pill">Delete</a>
                                                    <?php } ?>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="position-fixed top-0 end-0 p-3 toast-wrap">
        <div id="actionToast" class="toast align-items-center border-0 text-bg-dark" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body" id="toastMessage">Action completed.</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
    <script>
        const chartPayload = <?= json_encode($chartPayload, JSON_UNESCAPED_SLASHES) ?>;
        const actionToast = new bootstrap.Toast(document.getElementById('actionToast'));
        let activityChart;
        let categoryChart;

        function showToast(message, isError = false) {
            const toastEl = $('#actionToast');
            $('#toastMessage').text(message);
            toastEl.removeClass('text-bg-dark text-bg-danger text-bg-success')
                .addClass(isError ? 'text-bg-danger' : 'text-bg-success');
            actionToast.show();
        }

        function setMode(mode) {
            $('.mode-section').addClass('d-none');
            $('.mode-trigger').removeClass('active btn-light').addClass('btn-outline-primary');

            if (mode === 'file') {
                $('#fileSection').removeClass('d-none');
            } else {
                $('#questionSection').removeClass('d-none');
            }

            $('.mode-trigger[data-mode="' + mode + '"]').addClass('active');
            window.history.replaceState({}, '', 'index.php?mode=' + mode + '&filter=' + encodeURIComponent($('#discussionFilter').val() || 'all'));
        }

        function validateQuestionForm() {
            const question = $.trim($('#postQueryForm textarea[name="question"]').val());
            if (question.length < 10) {
                showToast('Question must be at least 10 characters long.', true);
                return false;
            }
            return true;
        }

        function validateUploadForm() {
            const files = $('#resourceUploadForm input[name="files[]"]')[0].files;
            if (files.length === 0) {
                showToast('Please choose at least one file.', true);
                return false;
            }
            if (files.length > 5) {
                showToast('You can upload a maximum of 5 files.', true);
                return false;
            }
            return true;
        }

        function filterQuestions() {
            const search = ($('#questionSearch').val() || '').toLowerCase();
            const category = $('#discussionFilter').val();
            let visibleCount = 0;

            $('.searchable-question').each(function() {
                const matchesCategory = category === 'all' || $(this).data('category') === category;
                const matchesSearch = ($(this).data('search') || '').includes(search);
                const show = matchesCategory && matchesSearch;
                $(this).toggle(show);
                if (show) {
                    visibleCount++;
                }
            });

            $('#questionResultBadge').text(visibleCount + ' discussion' + (visibleCount === 1 ? '' : 's') + ' visible');
            window.history.replaceState({}, '', 'index.php?mode=' + ($('body').data('active-mode') || 'question') + '&filter=' + encodeURIComponent(category));
        }

        function filterResources() {
            const search = ($('#resourceSearch').val() || '').toLowerCase();
            $('.searchable-resource').each(function() {
                $(this).toggle(($(this).data('search') || '').includes(search));
            });
        }

        function setButtonLoading($btn, loadingText) {
            $btn.data('original-text', $btn.html());
            $btn.prop('disabled', true).html(loadingText);
        }

        function resetButton($btn) {
            $btn.prop('disabled', false).html($btn.data('original-text'));
        }

        function updateStatsFromPayload(data) {
            if (!data || !data.stats) {
                return;
            }
            $('.stats-questions').text(data.stats.questions ?? $('.stats-questions').first().text());
            $('.stats-resources').text(data.stats.resources ?? $('.stats-resources').first().text());
            $('.stats-comments').text(data.stats.comments ?? $('.stats-comments').first().text());
            $('.stats-sos').text(data.stats.sos ?? $('.stats-sos').first().text());
        }

        function appendCommentToCard(queryId, commentHtml, totalComments, parentCommentId) {
            const $card = $('.comment-form input[name="query_id"][value="' + queryId + '"]').closest('.feed-card');
            if (parentCommentId && Number(parentCommentId) > 0) {
                const $parent = $card.find('.comment-item[data-comment-id="' + parentCommentId + '"] .comment-replies').first();
                $parent.append(commentHtml);
                $card.find('.comment-item[data-comment-id="' + parentCommentId + '"] .comment-reply-form').addClass('d-none');
            } else {
                $card.find('.comment-list').prepend(commentHtml);
            }
            $card.find('.comment-total').first().text(totalComments);
        }

        function initDashboardCharts() {
            if (typeof Chart === 'undefined' || !$('#activityChart').length || !$('#categoryChart').length) {
                return;
            }

            const activityCtx = document.getElementById('activityChart');
            const categoryCtx = document.getElementById('categoryChart');
            const initialActivity = chartPayload.activity['7'];

            activityChart = new Chart(activityCtx, {
                type: 'line',
                data: {
                    labels: initialActivity.labels,
                    datasets: [
                        {
                            label: 'Questions',
                            data: initialActivity.questions,
                            borderColor: '#0f4c81',
                            backgroundColor: 'rgba(15, 76, 129, 0.12)',
                            tension: 0.35,
                            fill: true
                        },
                        {
                            label: 'Comments',
                            data: initialActivity.comments,
                            borderColor: '#ff9f1c',
                            backgroundColor: 'rgba(255, 159, 28, 0.12)',
                            tension: 0.35,
                            fill: true
                        },
                        {
                            label: 'Resources',
                            data: initialActivity.resources,
                            borderColor: '#198754',
                            backgroundColor: 'rgba(25, 135, 84, 0.12)',
                            tension: 0.35,
                            fill: true
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    }
                }
            });

            categoryChart = new Chart(categoryCtx, {
                type: 'doughnut',
                data: {
                    labels: chartPayload.categories.labels,
                    datasets: [{
                        data: chartPayload.categories.values,
                        backgroundColor: ['#0f4c81', '#ff9f1c', '#20c997', '#dc3545'],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    },
                    cutout: '68%'
                }
            });
        }
// Dark mode toggle
        const darkModeToggle = document.getElementById('darkModeToggle');
        const body = document.body;

        // Check for saved dark mode preference
        if (localStorage.getItem('darkMode') === 'enabled') {
            body.classList.add('dark-mode');
            darkModeToggle.textContent = '☀️';
        }

        darkModeToggle.addEventListener('click', () => {
            body.classList.toggle('dark-mode');
            if (body.classList.contains('dark-mode')) {
                localStorage.setItem('darkMode', 'enabled');
                darkModeToggle.textContent = '☀️';
            } else {
                localStorage.setItem('darkMode', 'disabled');
                darkModeToggle.textContent = '🌙';
            }
        });
        function switchActivityRange(range) {
            if (!activityChart || !chartPayload.activity[range]) {
                return;
            }

            const dataset = chartPayload.activity[range];
            activityChart.data.labels = dataset.labels;
            activityChart.data.datasets[0].data = dataset.questions;
            activityChart.data.datasets[1].data = dataset.comments;
            activityChart.data.datasets[2].data = dataset.resources;
            activityChart.update();

            $('.chart-range-btn').removeClass('active');
            $('.chart-range-btn[data-range="' + range + '"]').addClass('active');
        }

        function loadBooks() {
            const query = $.trim($('#bookQuery').val()) || 'engineering';
            const $container = $('#apiBooks');
            $container.html('<div class="mini-muted">Loading book suggestions...</div>');

            $.getJSON('https://openlibrary.org/search.json', { q: query })
                .done(function(data) {
                    const docs = (data.docs || []).slice(0, 6);
                    if (!docs.length) {
                        $container.html('<div class="mini-muted">No books found for this search.</div>');
                        return;
                    }

                    const html = docs.map(function(book) {
                        const author = book.author_name && book.author_name.length ? book.author_name[0] : 'Unknown author';
                        const year = book.first_publish_year || 'Year N/A';
                        return `
                            <div class="book-card p-3">
                                <div class="fw-bold mb-1">${book.title}</div>
                                <div class="mini-muted mb-2">Author: ${author}</div>
                                <div class="mini-muted">First published: ${year}</div>
                            </div>
                        `;
                    }).join('');

                    $container.html(html);
                })
                .fail(function() {
                    $container.html('<div class="mini-muted text-danger">Book suggestions could not be loaded right now.</div>');
                });
        }

        $(function() {
            const initialMode = $('body').data('active-mode') === 'file' ? 'file' : 'question';
            $('body').attr('data-active-mode', initialMode);
            initDashboardCharts();
            setMode(initialMode);
            filterQuestions();
            filterResources();

            $(document).on('click', '.mode-trigger', function() {
                const mode = $(this).data('mode');
                $('body').attr('data-active-mode', mode);
                setMode(mode);
            });

            $(document).on('click', '.chart-range-btn', function() {
                switchActivityRange($(this).data('range').toString());
            });

            $('#questionSearch').on('input', filterQuestions);
            $('#discussionFilter').on('change', filterQuestions);
            $('#resourceSearch').on('input', filterResources);

            $(document).on('click', '.toggle-comments', function() {
                const target = $(this).data('target');
                $(target).toggleClass('d-none');
                $(this).text($(target).hasClass('d-none') ? 'See more comments' : 'Hide comments');
            });

            $(document).on('click', '.reply-toggle', function() {
                const $form = $(this).closest('.comment-item').find('.comment-reply-form').first();
                $form.toggleClass('d-none');
                if (!$form.hasClass('d-none')) {
                    $form.find('input[name="comment"]').trigger('focus');
                }
            });

            $('#postQueryForm').on('submit', function(e) {
                e.preventDefault();
                if (!validateQuestionForm()) {
                    return;
                }

                const $btn = $(this).find('.submit-btn');
                setButtonLoading($btn, 'Posting...');

                $.ajax({
                    url: $(this).attr('action'),
                    type: 'POST',
                    data: new FormData(this),
                    processData: false,
                    contentType: false,
                    dataType: 'json'
                }).done(function(response) {
                    if (response.success) {
                        $('#postQueryForm')[0].reset();
                        showToast(response.message || 'Question posted successfully.');
                        updateStatsFromPayload(response);
                        window.location.href = 'index.php?mode=question&filter=' + encodeURIComponent($('#discussionFilter').val() || 'all');
                    } else {
                        showToast(response.message || 'Unable to post question.', true);
                    }
                }).fail(function() {
                    showToast('Question post failed. Please try again.', true);
                }).always(function() {
                    resetButton($btn);
                });
            });

            $(document).on('submit', '.comment-form', function(e) {
                e.preventDefault();
                const form = this;
                const $btn = $(form).find('.submit-btn');
                const comment = $.trim($(form).find('input[name="comment"]').val());

                if (comment.length < 2) {
                    showToast('Reply must contain at least 2 characters.', true);
                    return;
                }

                setButtonLoading($btn, 'Sending...');

                $.ajax({
                    url: $(form).attr('action'),
                    type: 'POST',
                    data: $(form).serialize(),
                    dataType: 'json'
                }).done(function(response) {
                    if (response.success) {
                        form.reset();
                        appendCommentToCard(response.query_id, response.comment_html, response.total_comments, response.parent_comment_id);
                        updateStatsFromPayload(response);
                        showToast(response.message || 'Reply added.');
                    } else {
                        showToast(response.message || 'Reply failed.', true);
                    }
                }).fail(function() {
                    showToast('Reply failed. Please try again.', true);
                }).always(function() {
                    resetButton($btn);
                });
            });

            $('#resourceUploadForm').on('submit', function(e) {
                e.preventDefault();
                if (!validateUploadForm()) {
                    return;
                }

                const $btn = $(this).find('.submit-btn');
                setButtonLoading($btn, 'Uploading...');

                $.ajax({
                    url: $(this).attr('action'),
                    type: 'POST',
                    data: new FormData(this),
                    processData: false,
                    contentType: false,
                    dataType: 'json'
                }).done(function(response) {
                    if (response.success) {
                        $('#resourceUploadForm')[0].reset();
                        showToast(response.message || 'Files uploaded successfully.');
                        updateStatsFromPayload(response);
                        window.location.href = 'index.php?mode=file';
                    } else {
                        showToast(response.message || 'Upload failed.', true);
                    }
                }).fail(function() {
                    showToast('Upload failed. Please try again.', true);
                }).always(function() {
                    resetButton($btn);
                });
            });

            $('#loadBooksBtn').on('click', loadBooks);
            $('#bookQuery').on('keypress', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    loadBooks();
                }
            });
        });
    </script>
</body>
</html>
