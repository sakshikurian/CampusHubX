<?php
session_start();
include "../includes/db.php";

/* AUTO DELETE FILES OLDER THAN 7 DAYS */
$oldFiles = mysqli_query($conn, "
    SELECT id, file_path 
    FROM resources 
    WHERE created_at < NOW() - INTERVAL 7 DAY
");

while ($f = mysqli_fetch_assoc($oldFiles)) {

    $file = "uploads/" . $f['file_path'];

    if (file_exists($file)) {
        unlink($file);   // delete from folder
    }

    mysqli_query($conn, "DELETE FROM resources WHERE id=" . $f['id']); // delete from DB
}
if (!isset($_SESSION['user_id']) && !isset($_SESSION['admin_id'])) {
    header("Location: ../index.php");
    exit();
}

$userId = $_SESSION['user_id'] ?? null;
$userName = $_SESSION['admin_name'] ?? $_SESSION['user_name'] ?? "Guest";

$filter = $_GET['filter'] ?? "all";
?>

<!DOCTYPE html>
<html>

<head>

    <title>Book Sharing & Discussion | CampusHubX</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .three-dots {
            border: none;
            font-size: 18px;
            background: transparent;
        }

        .resource-link {
            text-decoration: none;
            color: #212529;
            font-weight: 500;
        }

        .resource-link:hover {
            text-decoration: underline;
            color: #0d6efd;
            cursor: pointer;
        }
    </style>
    <script>
        function toggleComments(id) {
            let box = document.getElementById("more-comments-" + id);
            let btn = document.getElementById("toggle-btn-" + id);

            if (box.style.display === "none") {
                box.style.display = "block";
                btn.innerText = "Hide comments";
            } else {
                box.style.display = "none";
                btn.innerText = "See more comments";
            }
        }

        function showMode(mode) {
            document.getElementById("questionSection").style.display = "none";
            document.getElementById("fileSection").style.display = "none";

            if (mode === "question") document.getElementById("questionSection").style.display = "block";
            else if (mode === "file") document.getElementById("fileSection").style.display = "block";
        }
    </script>

</head>

<body style="background:#cfe2f3;">

    <nav class="navbar navbar-dark bg-primary">
        <div class="container-fluid">
            <?php
            if (isset($_SESSION['admin_id'])) {
                $backLink = "../admin/view_reports.php";
            } else {
                $backLink = "../dashboard.php";
            }
            ?>

            <a class="navbar-brand fw-bold" href="<?= $backLink ?>">⬅ CampusHubX</a>
            <span class="text-white">Welcome, <?= htmlspecialchars($userName) ?>!</span>
        </div>
    </nav>

    <div class="container mt-4">

        <!-- MODE SELECT -->
        <div class="card shadow-sm mb-4 text-center">
            <div class="card-body">
                <h5 class="fw-bold mb-3">What would you like to do?</h5>
                <button class="btn btn-primary me-2" onclick="showMode('question')">💬 Ask a Question</button>
                <button class="btn btn-success" onclick="showMode('file')"> Share a File</button>
            </div>
        </div>


        <!-- ================= QUESTION MODE ================= -->

        <div id="questionSection">

            <?php if (isset($_SESSION['user_id'])) { ?>
                <!-- POST QUESTION -->
                <div class="card mb-4 shadow-sm">
                    <div class="card-body">

                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h5 class="fw-bold mb-0">Post a Question</h5>

                            <select name="category" form="postQueryForm" class="form-select" style="width:200px;">
                                <option value="general">General</option>
                                <option value="coding">Coding Queries</option>
                                <option value="lost">Lost & Found</option>
                                <option value="sos">SOS</option>
                            </select>
                        </div>

                        <form id="postQueryForm" action="post_query.php" method="POST" enctype="multipart/form-data">
                            <textarea name="question" class="form-control mb-2" placeholder="Ask a question..."
                                required></textarea>
                            <input type="file" name="image" class="form-control mb-2" accept=".jpg,.jpeg,.png,.gif,.webp">
                            <button class="btn btn-primary">Post</button>
                        </form>

                    </div>
                </div>
            <?php } ?>

            <!-- DISCUSSIONS + FILTER -->
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h5 class="fw-bold">💬 Discussions</h5>

                <form method="GET">
                    <select name="filter" class="form-select" onchange="this.form.submit()">
                        <option value="all" <?= $filter == "all" ? "selected" : "" ?>>All</option>
                        <option value="coding" <?= $filter == "coding" ? "selected" : "" ?>>Coding</option>
                        <option value="lost" <?= $filter == "lost" ? "selected" : "" ?>>Lost & Found</option>
                        <option value="sos" <?= $filter == "sos" ? "selected" : "" ?>>SOS</option>
                    </select>
                </form>
            </div>


            <?php
            $where = "";
            if ($filter != "all") {
                $filterSafe = mysqli_real_escape_string($conn, $filter);
                $where = "WHERE LOWER(q.category)='$filterSafe'";
            }

            $q = mysqli_query($conn, "
SELECT q.*, u.name
FROM queries q
JOIN users u ON q.user_id=u.id
$where
ORDER BY CASE WHEN LOWER(q.category)='sos' THEN 0 ELSE 1 END, q.id DESC
");


            while ($row = mysqli_fetch_assoc($q)) {

                $cid = $row['id'];
                $category = strtolower($row['category'] ?? "general");

                $tagColor = "secondary";
                $cardStyle = "";

                if ($category == "coding")
                    $tagColor = "primary";
                elseif ($category == "lost")
                    $tagColor = "warning";
                elseif ($category == "sos") {
                    $tagColor = "danger";
                    $cardStyle = "border border-danger border-3 bg-danger bg-opacity-25 shadow";
                }

                $countRes = mysqli_query($conn, "SELECT COUNT(*) total FROM comments WHERE query_id=$cid");
                $totalComments = mysqli_fetch_assoc($countRes)['total'];
                ?>
                <div id="post-<?= $cid ?>" class="card mb-3 <?= $cardStyle ?>">
                    <div class="card-body">

                        <div class="d-flex justify-content-between align-items-center">

                            <div>
                                <p class="mb-1"><?= htmlspecialchars($row['question']) ?></p>
                                <span class="badge bg-<?= $tagColor ?>"><?= strtoupper($category) ?></span>
                            </div>

                            <!-- FIXED RIGHT SIDE ICONS -->
                            <div class="d-flex align-items-center gap-2">

                                <?php if (!empty($row['image'])) { ?>
                                    <a href="uploads/questions/<?= htmlspecialchars($row['image']) ?>" target="_blank"
                                        style="text-decoration:none; font-size:18px;">
                                        👁
                                    </a>
                                <?php } ?>

                                <div class="dropdown">
                                    <button class="btn btn-sm three-dots" data-bs-toggle="dropdown">⋯</button>

                                    <ul class="dropdown-menu dropdown-menu-end">

                                        <?php if ($row['user_id'] == $userId) { ?>
                                            <li>
                                                <a class="dropdown-item text-danger"
                                                    href="delete_query.php?id=<?= $cid ?>&mode=question"
                                                    onclick="return confirm('Delete this question?')">
                                                    Delete
                                                </a>
                                            </li>
                                        <?php } ?>

                                        <li>
                                            <a class="dropdown-item text-warning"
                                                href="report_issue.php?type=post&id=<?= $cid ?>">
                                                Report Issue
                                            </a>
                                        </li>

                                    </ul>
                                </div>

                            </div>

                        </div>

                        <?php if ($category == "sos") { ?>
                            <div class="text-danger fw-bold mb-1"> EMERGENCY SOS ALERT</div>
                        <?php } ?>


                        <small class="text-muted">
                            Posted by <?= htmlspecialchars($row['name']) ?> |
                            <?= date("d M Y, h:i A", strtotime($row['created_at'])) ?>
                        </small>

                        <div><small><?= $totalComments ?> comments</small></div>

                        <!-- REPLY BOX -->
                        <form action="post_comment.php" method="POST" class="d-flex mt-2 mb-2">
                            <input type="hidden" name="query_id" value="<?= $cid ?>">
                            <input type="text" name="comment" class="form-control me-2" placeholder="Write a reply..."
                                required>
                            <button class="btn btn-outline-primary btn-sm">Reply</button>
                        </form>

                        <!-- FIRST 2 COMMENTS -->
                        <?php
                        $c = mysqli_query($conn, "
SELECT c.id,c.comment,c.user_id,u.name
FROM comments c
JOIN users u ON c.user_id=u.id
WHERE c.query_id=$cid
ORDER BY c.id DESC
LIMIT 2
");

                        while ($com = mysqli_fetch_assoc($c)) { ?>
                            <div id="comment-<?= $com['id'] ?>" class="d-flex justify-content-between ms-3 mb-1">
                                <small>💬 <b><?= htmlspecialchars($com['name']) ?>:</b>
                                    <?= htmlspecialchars($com['comment']) ?></small>

                                <div class="dropdown">
                                    <button class="btn btn-sm three-dots" data-bs-toggle="dropdown">⋯</button>

                                    <ul class="dropdown-menu dropdown-menu-end">

                                        <?php if ($com['user_id'] == $userId) { ?>
                                            <li>
                                                <a class="dropdown-item text-danger"
                                                    href="delete_comment.php?id=<?= $com['id'] ?>&mode=question">
                                                    Delete
                                                </a>
                                            </li>
                                        <?php } ?>

                                        <li>
                                            <a class="dropdown-item text-warning"
                                                href="report_issue.php?type=comment&id=<?= $com['id'] ?>">
                                                Report Issue
                                            </a>
                                        </li>

                                    </ul>
                                </div>
                            </div>
                        <?php } ?>


                        <!-- MORE COMMENTS -->
                        <?php if ($totalComments > 2) { ?>

                            <div id="more-comments-<?= $cid ?>" style="display:none;">

                                <?php
                                $more = mysqli_query($conn, "
SELECT c.id,c.comment,c.user_id,u.name
FROM comments c
JOIN users u ON c.user_id=u.id
WHERE c.query_id=$cid
ORDER BY c.id DESC
LIMIT 100 OFFSET 2
");

                                while ($m = mysqli_fetch_assoc($more)) { ?>
                                    <div id="comment-<?= $m['id'] ?>" class="d-flex justify-content-between ms-3 mb-1">
                                        <small>💬 <b><?= htmlspecialchars($m['name']) ?>:</b>
                                            <?= htmlspecialchars($m['comment']) ?></small>

                                        <?php if ($m['user_id'] == $userId) { ?>
                                            <a href="delete_comment.php?id=<?= $m['id'] ?>&mode=question"
                                                class="btn btn-outline-primary btn-sm">Delete</a>
                                        <?php } ?>
                                    </div>
                                <?php } ?>
                            </div>

                            <button id="toggle-btn-<?= $cid ?>" class="btn btn-link btn-sm ms-3"
                                onclick="toggleComments(<?= $cid ?>)">
                                See more comments
                            </button>

                        <?php } ?>

                    </div>
                </div>

            <?php } ?>

        </div>



        <!-- ================= FILE MODE ================= -->

        <div id="fileSection" style="display:none;">

            <?php if (isset($_SESSION['user_id'])) { ?>
                <!-- Upload Card -->
                <div class="card mb-4 shadow-sm">
                    <div class="card-body">
                        <h5 class="fw-bold"> Share Books / Resources</h5>

                        <form action="post_resource.php" method="POST" enctype="multipart/form-data">
                            <textarea name="description" class="form-control mb-2"
                                placeholder="Enter description about this resource..." required></textarea>

                            <input type="file" name="files[]" class="form-control mb-2" multiple required>
                            <small class="text-muted">You can upload maximum 5 files</small>

                            <button class="btn btn-success">Upload File</button>
                        </form>
                    </div>
                </div>
            <?php } ?>
            <!-- Resources Table -->
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="fw-bold mb-3"> Shared Resources</h5>

                    <table class="table table-bordered table-hover align-middle text-center">
                        <thead class="table-primary">
                            <tr>
                                <th>Title</th>
                                <th>Uploaded By</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Type</th>
                            </tr>
                        </thead>
                        <tbody>

                            <?php
                            $r = mysqli_query($conn, "
                           SELECT r.*, u.name
                           FROM resources r
                           LEFT JOIN users u ON r.user_id = u.id
                           ORDER BY r.id DESC
                           ");

                            if (mysqli_num_rows($r) == 0) {
                                echo '<tr><td colspan="6" class="text-muted">No files uploaded yet 📁</td></tr>';
                            }

                            while ($res = mysqli_fetch_assoc($r)) {

                                $file = $res['file_path'];
                                $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));

                                $badge = "secondary";
                                if ($ext == "pdf")
                                    $badge = "danger";
                                elseif (in_array($ext, ["doc", "docx"]))
                                    $badge = "primary";
                                elseif (in_array($ext, ["ppt", "pptx"]))
                                    $badge = "warning";
                                elseif (in_array($ext, ["jpg", "jpeg", "png"]))
                                    $badge = "success";

                                $date = date("d M Y", strtotime($res['created_at'] ?? "now"));
                                $time = date("h:i A", strtotime($res['created_at'] ?? "now"));
                                ?>

                                <tr id="resource-<?= $res['id'] ?>">
                                    <td class="text-start position-relative resource-title">

                                        <div class="d-flex justify-content-between align-items-center">

                                            <?php
                                            $fileUrl = "uploads/" . htmlspecialchars($file);
                                            $previewable = ["pdf", "jpg", "jpeg", "png", "gif", "webp", "txt", "html"];
                                            ?>

                                            <a href="<?= $fileUrl ?>" target="_blank" class="resource-link">
                                                <?= htmlspecialchars($res['title']) ?>
                                            </a>

                                            <div class="dropdown">
                                                <button class="btn btn-sm three-dots" data-bs-toggle="dropdown">
                                                    ⋯
                                                </button>

                                                <ul class="dropdown-menu dropdown-menu-end">

                                                    <!-- DESCRIPTION -->
                                                    <li class="px-3 py-2 text-muted small">
                                                        <?= htmlspecialchars($res['description'] ?? 'No description') ?>
                                                    </li>

                                                    <li>
                                                        <hr class="dropdown-divider">
                                                    </li>

                                                    <!-- DOWNLOAD -->
                                                    <li>
                                                        <a class="dropdown-item"
                                                            href="uploads/<?= htmlspecialchars($file) ?>" download>
                                                            Download
                                                        </a>
                                                    </li>

                                                    <!-- DELETE (ONLY OWNER) -->
                                                    <?php if ($res['user_id'] == $userId) { ?>
                                                        <li>
                                                            <a class="dropdown-item text-danger"
                                                                href="delete_resource.php?id=<?= $res['id'] ?>"
                                                                onclick="return confirm('Delete this file?')">
                                                                Delete
                                                            </a>
                                                        </li>
                                                    <?php } ?>
                                                    <li>
                                                        <a class="dropdown-item text-warning"
                                                            href="report_issue.php?type=resource&id=<?= $res['id'] ?>">
                                                            Report Issue
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>

                                        </div>

                                    </td>

                                    <td>
                                        <?= htmlspecialchars($res['name']) ?>
                                    </td>

                                    <td>
                                        <?= $date ?>
                                    </td>

                                    <td>
                                        <?= $time ?>
                                    </td>

                                    <td>
                                        <span class="badge bg-<?= $badge ?>">
                                            <?= strtoupper($ext ?: "FILE") ?>
                                        </span>
                                    </td>


                                </tr>

                            <?php } ?>

                        </tbody>
                    </table>
                </div>
            </div>

        </div>


    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <?php
    $type = $_GET['type'] ?? '';
    $id = $_GET['id'] ?? '';
    ?>

    <script>

        let type = "<?= $type ?>";
        let id = "<?= $id ?>";

        /* OPEN CORRECT SECTION FIRST */
        if (type === "resource") {
            document.getElementById("questionSection").style.display = "none";
            document.getElementById("fileSection").style.display = "block";
        }

        /* NOW SCROLL TO THE REPORTED CONTENT */
        if (type && id) {

            let element = document.getElementById(type + "-" + id);

            if (element) {

                element.scrollIntoView({
                    behavior: "smooth",
                    block: "center"
                });

                element.style.border = "3px solid red";
                element.style.backgroundColor = "#ffe6e6";

            }

        }

    </script>
</body>

</html>