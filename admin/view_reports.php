<?php
require_once '../includes/session.php';
include("../includes/db.php");

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../index.php");
    exit();
}

function ensureContentReportsTable($conn)
{
    mysqli_query($conn, "
        CREATE TABLE IF NOT EXISTS content_reports (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            type VARCHAR(20) NOT NULL,
            reference_id INT NOT NULL,
            reason TEXT NOT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_content_report_lookup (type, reference_id),
            INDEX idx_content_report_user (user_id)
        )
    ");
}

function h($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function renderFilePreview($path, $label)
{
    $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
    $safePath = h($path);
    $safeLabel = h($label);

    if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)) {
        return '<a href="' . $safePath . '" target="_blank"><img src="' . $safePath . '" alt="' . $safeLabel . '" class="reported-image"></a>';
    }

    return '<a href="' . $safePath . '" target="_blank" class="btn btn-outline-primary btn-sm rounded-pill">Open ' . $safeLabel . '</a>';
}

function renderReportedContent($conn, $type, $referenceId)
{
    $referenceId = (int) $referenceId;

    if ($type === 'post') {
        $result = mysqli_query($conn, "
            SELECT q.*, u.name
            FROM queries q
            JOIN users u ON q.user_id=u.id
            WHERE q.id=$referenceId
        ");
        $post = $result ? mysqli_fetch_assoc($result) : null;

        if (!$post) {
            return '<div class="text-danger fw-semibold">Post was already removed.</div>';
        }

        $html = '<div class="content-box">';
        $html .= '<div class="fw-bold mb-1">Post by ' . h($post['name']) . '</div>';
        $html .= '<div class="mb-2">' . nl2br(h($post['question'])) . '</div>';
        $html .= '<div class="small text-muted mb-2">Category: ' . h(strtoupper($post['category'] ?? 'general')) . '</div>';
        if (!empty($post['image'])) {
            $html .= '<div class="mt-2">' . renderFilePreview('../booksharing/uploads/questions/' . $post['image'], 'Post image') . '</div>';
        }
        $html .= '</div>';

        return $html;
    }

    if ($type === 'comment') {
        $result = mysqli_query($conn, "
            SELECT c.*, u.name AS commenter_name, q.question, q.image AS post_image, q.category, qu.name AS post_author
            FROM comments c
            JOIN users u ON c.user_id=u.id
            JOIN queries q ON c.query_id=q.id
            JOIN users qu ON q.user_id=qu.id
            WHERE c.id=$referenceId
        ");
        $comment = $result ? mysqli_fetch_assoc($result) : null;

        if (!$comment) {
            return '<div class="text-danger fw-semibold">Comment or reply was already removed.</div>';
        }

        $label = !empty($comment['parent_comment_id']) ? 'Reported reply' : 'Reported comment';
        $html = '<div class="content-box">';
        $html .= '<div class="small text-muted mb-1">Post by ' . h($comment['post_author']) . '</div>';
        $html .= '<div class="post-text mb-2">' . nl2br(h($comment['question'])) . '</div>';
        if (!empty($comment['post_image'])) {
            $html .= '<div class="mb-3">' . renderFilePreview('../booksharing/uploads/questions/' . $comment['post_image'], 'Post image') . '</div>';
        }
        $html .= '<div class="fw-bold mb-1">' . h($label) . ' by ' . h($comment['commenter_name']) . '</div>';
        $html .= '<div>' . nl2br(h($comment['comment'])) . '</div>';
        if (!empty($comment['file'])) {
            $html .= '<div class="mt-2">' . renderFilePreview('../booksharing/uploads/comments/' . $comment['file'], 'Comment attachment') . '</div>';
        }
        $html .= '</div>';

        return $html;
    }

    if ($type === 'resource') {
        $result = mysqli_query($conn, "
            SELECT r.*, u.name
            FROM resources r
            JOIN users u ON r.user_id=u.id
            WHERE r.id=$referenceId
        ");
        $resource = $result ? mysqli_fetch_assoc($result) : null;

        if (!$resource) {
            return '<div class="text-danger fw-semibold">Resource was already removed.</div>';
        }

        $filePath = '../booksharing/uploads/' . $resource['file_path'];
        $html = '<div class="content-box">';
        $html .= '<div class="fw-bold mb-1">' . h($resource['title']) . '</div>';
        $html .= '<div class="small text-muted mb-2">Uploaded by ' . h($resource['name']) . '</div>';
        $html .= '<div class="mb-2">' . h($resource['description'] ?? 'No description added') . '</div>';
        $html .= '<div class="d-flex gap-2 flex-wrap">';
        $html .= renderFilePreview($filePath, 'Resource');
        $html .= '<a href="' . h($filePath) . '" download class="btn btn-outline-secondary btn-sm rounded-pill">Download Resource</a>';
        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }

    return '<div class="text-danger fw-semibold">Unknown report type.</div>';
}

ensureContentReportsTable($conn);

if (isset($_GET['delete_type'])) {
    $type = mysqli_real_escape_string($conn, $_GET['delete_type']);
    $id = (int) $_GET['id'];

    if ($type === "post") {
        mysqli_query($conn, "DELETE FROM comments WHERE query_id=$id");
        mysqli_query($conn, "DELETE FROM queries WHERE id=$id");
    }

    if ($type === "comment") {
        mysqli_query($conn, "DELETE FROM comments WHERE parent_comment_id=$id");
        mysqli_query($conn, "DELETE FROM comments WHERE id=$id");
    }

    if ($type === "resource") {
        $res = mysqli_fetch_assoc(mysqli_query($conn, "SELECT file_path FROM resources WHERE id=$id"));
        if ($res) {
            $file = "../booksharing/uploads/" . $res['file_path'];
            if (file_exists($file)) {
                unlink($file);
            }
            mysqli_query($conn, "DELETE FROM resources WHERE id=$id");
        }
    }

    mysqli_query($conn, "DELETE FROM content_reports WHERE reference_id=$id AND type='$type'");
    header("Location:view_reports.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Reported Issues | Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --brand: #0f4c81;
            --brand-dark: #0a2f4f;
            --surface: rgba(255, 255, 255, 0.94);
            --text-main: #16324f;
            --border-soft: rgba(15, 76, 129, 0.12);
        }

        body {
            background: #cfe2f3;
            color: var(--text-main);
            opacity: 0;
            transition: opacity 0.4s ease-in-out;
        }

        body.page-loaded {
            opacity: 1;
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

        .report-card {
            background: var(--surface);
            border: 1px solid var(--border-soft);
            border-radius: 16px;
            box-shadow: 0 16px 36px rgba(15, 76, 129, 0.12);
            overflow: hidden;
        }

        .content-box {
            min-width: 280px;
            max-width: 520px;
        }

        .post-text {
            background: #f4f9ff;
            border: 1px solid var(--border-soft);
            border-radius: 10px;
            padding: 10px 12px;
        }

        .reported-image {
            max-width: 260px;
            max-height: 180px;
            object-fit: cover;
            border-radius: 10px;
            border: 1px solid var(--border-soft);
        }

        body.dark-mode {
            background-color: #3e3d3d !important;
            color: #ffffff !important;
        }

        .dark-mode .report-card,
        .dark-mode .table {
            background-color: #1e1e1e !important;
            color: #ffffff !important;
        }

        .dark-mode .post-text {
            background: #262626;
            border-color: #3a3a3a;
        }

        .dark-mode a {
            color: #4dabf7;
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark navbar-shell py-3">
        <div class="container">
            <div class="d-flex align-items-center gap-3">
                <div class="brand-mark">RI</div>
                <div>
                    <a class="navbar-brand fw-bold mb-0" href="dashboard.php">Reported Issues</a>
                    <div class="small text-white-50">View and manage reported content</div>
                </div>
            </div>

            <div class="d-flex align-items-center gap-3 text-white mt-3 mt-lg-0">
                <button id="darkModeToggle" class="btn btn-outline-light me-2" type="button">Dark</button>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="report-card table-responsive">
            <table class="table table-bordered table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Reported By</th>
                        <th>Type</th>
                        <th>Reason</th>
                        <th>Reported Content</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $q = mysqli_query($conn, "
                        SELECT r.*, u.name
                        FROM content_reports r
                        JOIN users u ON r.user_id=u.id
                        ORDER BY r.id DESC
                    ");

                    if (!$q || mysqli_num_rows($q) === 0) {
                        echo "<tr><td colspan='7' class='text-center py-4'>No reports</td></tr>";
                    }

                    while ($q && $row = mysqli_fetch_assoc($q)) {
                        $type = $row['type'];
                        $ref = (int) $row['reference_id'];
                        ?>
                        <tr>
                            <td><?= (int) $row['id'] ?></td>
                            <td><?= h($row['name']) ?></td>
                            <td><span class="badge text-bg-warning rounded-pill"><?= h(strtoupper($type)) ?></span></td>
                            <td><?= nl2br(h($row['reason'])) ?></td>
                            <td><?= renderReportedContent($conn, $type, $ref) ?></td>
                            <td><?= date("d M Y", strtotime($row['created_at'])) ?></td>
                            <td>
                                <a class="btn btn-danger btn-sm rounded-pill"
                                   href="?delete_type=<?= h($type) ?>&id=<?= $ref ?>&report=<?= (int) $row['id'] ?>"
                                   onclick="return confirm('Delete this content?')">
                                    Delete Content
                                </a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        const darkModeToggle = document.getElementById('darkModeToggle');
        const body = document.body;

        if (localStorage.getItem('darkMode') === 'enabled') {
            body.classList.add('dark-mode');
            darkModeToggle.textContent = 'Light';
        }

        darkModeToggle.addEventListener('click', () => {
            body.classList.toggle('dark-mode');
            if (body.classList.contains('dark-mode')) {
                localStorage.setItem('darkMode', 'enabled');
                darkModeToggle.textContent = 'Light';
            } else {
                localStorage.setItem('darkMode', 'disabled');
                darkModeToggle.textContent = 'Dark';
            }
        });

        window.addEventListener('load', () => {
            body.classList.add('page-loaded');
        });
    </script>
</body>
</html>
