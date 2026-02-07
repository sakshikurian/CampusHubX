<?php
session_start();
$mode = $_GET['mode'] ?? '';

include "../includes/db.php";

/* PROTECT MODULE */
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

$userId = $_SESSION['user_id'];
$userName = $_SESSION['user_name'];
?>

<!DOCTYPE html>
<html>

<head>
    <title>Book Sharing & Discussion | CampusHubX</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <script>
        function showMode(mode) {

            // DO NOT hide selector (important)
            // document.getElementById("modeSelector").style.display = "none";

            document.getElementById("questionSection").style.display = "none";
            document.getElementById("fileSection").style.display = "none";

            if (mode === "question") {
                document.getElementById("questionSection").style.display = "block";
            }
            else if (mode === "file") {
                document.getElementById("fileSection").style.display = "block";
            }
        }
    </script>


</head>

<body style="  background-color: #cfe2f3;">

    <!-- NAVBAR -->
    <nav class="navbar navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="../dashboard.php"> CampusHubX</a>
            <span class="text-white me-3">Welcome,
                <?= htmlspecialchars($_SESSION['user_name']); ?>!
            </span>

        </div>
    </nav>

    <div class="container mt-4">

        <!-- MODE SELECTOR -->
        <div id="modeSelector" class="card shadow-sm mb-4">
            <div class="card-body text-center">
                <h5 class="fw-bold mb-3">What would you like to do?</h5>

                <button class="btn btn-primary me-2" onclick="showMode('question')">
                    💬 Ask a Question
                </button>

                <button class="btn btn-success" onclick="showMode('file')">
                    📁 Share a File
                </button>
            </div>
        </div>

        <!-- ================= QUESTION MODE ================= -->
        <div id="questionSection" style="display:none;">

            <!-- ASK QUERY -->
            <div class="card mb-4 shadow-sm">
                <div class="card-body">
                    <h5 class="fw-bold">Post a Question</h5>

                    <form action="post_query.php" method="POST">
                        <textarea name="question" class="form-control mb-2"
                            placeholder="Ask a question or request/share a book..." required></textarea>
                        <button class="btn btn-primary">Post</button>
                    </form>
                </div>
            </div>

            <!-- DISCUSSIONS -->
            <h5 class="fw-bold mb-3">💬 Discussions</h5>

            <?php
            $q = mysqli_query($conn, "
                SELECT q.id, q.question, q.created_at, u.name
                FROM queries q
                JOIN users u ON q.user_id = u.id
                ORDER BY q.id DESC
            ");

            while ($row = mysqli_fetch_assoc($q)) {
                ?>
                <div class="card mb-3 shadow-sm">
                    <div class="card-body">
                        <h6 class="fw-bold"><?= htmlspecialchars($row['name']) ?></h6>
                        <p><?= htmlspecialchars($row['question']) ?></p>

                        <form action="post_comment.php" method="POST" class="d-flex mb-2">
                            <input type="hidden" name="query_id" value="<?= $row['id'] ?>">
                            <input type="text" name="comment" class="form-control me-2" placeholder="Write a comment..."
                                required>
                            <button class="btn btn-outline-primary btn-sm">Reply</button>
                        </form>

                        <?php
                        $cid = $row['id'];
                        $c = mysqli_query($conn, "
                            SELECT c.comment, u.name
                            FROM comments c
                            JOIN users u ON c.user_id = u.id
                            WHERE c.query_id = $cid
                        ");

                        while ($com = mysqli_fetch_assoc($c)) {
                            echo "<small class='d-block'>💬 <b>"
                                . htmlspecialchars($com['name']) . ":</b> "
                                . htmlspecialchars($com['comment']) .
                                "</small>";
                        }
                        ?>
                    </div>
                </div>
            <?php } ?>
        </div>

        <!-- ================= FILE MODE ================= -->
        <div id="fileSection" style="display:none;">

            <!-- UPLOAD FILE -->
            <div class="card mb-4 shadow-sm">
                <div class="card-body">
                    <h5 class="fw-bold">📁 Share Books / Resources</h5>

                    <form action="post_resource.php" method="POST" enctype="multipart/form-data">
                        <input type="text" name="title" class="form-control mb-2" placeholder="Book / Resource Name"
                            required>
                        <input type="file" name="file" class="form-control mb-2" required>
                        <button class="btn btn-success">Upload File</button>
                    </form>
                </div>
            </div>

            <!-- TABLE -->
            <h5 class="fw-bold mb-3">📂 Shared Resources</h5>

            <div class="card shadow-sm">
                <div class="card-body p-0">

                    <table class="table table-hover align-middle mb-0 text-center">
                        <thead class="table-primary">
                            <tr>
                                <th>Title</th>
                                <th>Uploaded By</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Type</th>
                                <th>Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php
                            $r = mysqli_query($conn, "
                                SELECT r.*, u.name
                                FROM resources r
                                JOIN users u ON r.user_id = u.id
                                ORDER BY r.id DESC
                            ");

                            if (mysqli_num_rows($r) == 0) {
                                echo "<tr><td colspan='6' class='text-muted'>No files uploaded yet</td></tr>";
                            }

                            while ($res = mysqli_fetch_assoc($r)) {

                                $file = $res['file_path'];
                                $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));

                                $date = date("d M Y", strtotime($res['created_at']));
                                $time = date("h:i A", strtotime($res['created_at']));
                                ?>

                                <tr>
                                    <td>📄 <?= htmlspecialchars($res['title']) ?></td>
                                    <td><?= htmlspecialchars($res['name']) ?></td>
                                    <td><?= $date ?></td>
                                    <td><?= $time ?></td>
                                    <td><span class="badge bg-secondary"><?= strtoupper($ext) ?></span></td>
                                    <td>

                                        <!-- DOWNLOAD -->
                                        <a href="uploads/<?= htmlspecialchars($file) ?>" target="_blank"
                                            class="btn btn-sm btn-primary">📥</a>

                                        <!-- DELETE -->
                                        <?php if ($res['user_id'] == $_SESSION['user_id']) { ?>
                                            <a href="delete_resource.php?id=<?= $res['id'] ?>" class="btn btn-sm btn-danger"
                                                onclick="return confirm('Delete this file?')">🗑</a>
                                        <?php } ?>

                                    </td>
                                </tr>

                            <?php } ?>
                        </tbody>
                    </table>

                </div>
            </div>

        </div>
    </div>

    <!-- AUTO OPEN MODE -->
    <script>
        document.addEventListener("DOMContentLoaded", function () {

            const params = new URLSearchParams(window.location.search);
            const mode = params.get("mode");

            if (mode === "file") {
                showMode("file");
            }
            else if (mode === "question") {
                showMode("question");
            }
        });
    </script>



</body>

</html>