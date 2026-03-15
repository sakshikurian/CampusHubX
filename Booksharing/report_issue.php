<?php
session_start();
include "../includes/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

$type = $_GET['type'];
$reference_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $reason = $_POST['reason'];

    $stmt = $conn->prepare("INSERT INTO content_reports
    (user_id,type,reference_id,reason)
    VALUES (?,?,?,?)");

    $stmt->bind_param("isis", $user_id, $type, $reference_id, $reason);
    $stmt->execute();

    $message = "<div class='alert alert-success'>Issue reported successfully.</div>";
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Report Issue</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body style="background:#cfe2f3;">
    <nav class="navbar navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="../booksharing/index.php">⬅ CampusHubX</a>

        </div>
    </nav>
    <div class="container mt-5">

        <div class="card shadow p-4">

            <h4>Report Issue</h4>

            <?php echo $message; ?>

            <form method="POST">

                <div class="mb-3">

                    <label class="form-label">Reason</label>

                    <textarea name="reason" class="form-control" required></textarea>

                </div>

                <button class="btn btn-danger">Submit Report</button>

            </form>

        </div>

    </div>

</body>

</html>