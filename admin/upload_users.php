<?php
session_start();
include("../includes/db.php");

/* CHECK ADMIN LOGIN */

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../index.php");
    exit();
}

$message = "";
$errorFile = "";

/* DOWNLOAD TEMPLATE */

if (isset($_GET['download_template'])) {

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="students_template.csv"');

    $output = fopen("php://output", "w");

    fputcsv($output, ["name", "email", "password"]);
    fputcsv($output, ["Rahul Sharma", "rahul@comp.fcrit.ac.in", "Campus@123"]);
    fputcsv($output, ["Priya Singh", "priya@comp.fcrit.ac.in", "Secure@123"]);

    fclose($output);
    exit();
}

/* CSV UPLOAD */

if (isset($_POST['upload'])) {

    if (isset($_FILES['csv_file']['tmp_name'])) {

        $file = fopen($_FILES['csv_file']['tmp_name'], "r");

        /* SKIP HEADER */

        fgetcsv($file);

        $added = 0;
        $errors = [];

        while (($data = fgetcsv($file, 1000, ",")) !== FALSE) {

            $name = trim($data[0] ?? "");
            $email = trim($data[1] ?? "");
            $password = trim($data[2] ?? "");

            /* CHECK EMPTY DATA */

            if ($name == "" || $email == "" || $password == "") {
                $errors[] = [$name, $email, "Missing data"];
                continue;
            }

            /* EMAIL FORMAT */

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = [$name, $email, "Invalid email format"];
                continue;
            }

            /* COLLEGE EMAIL CHECK */

            if (!preg_match("/^[a-zA-Z0-9._%+-]+@comp\.fcrit\.ac\.in$/", $email)) {
                $errors[] = [$name, $email, "Must use @comp.fcrit.ac.in email"];
                continue;
            }

            /* PASSWORD VALIDATION */

            if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*[\W_]).{8,20}$/', $password)) {
                $errors[] = [$name, $email, "Weak password (8‑20 chars, upper, lower, special required)"];
                continue;
            }

            /* EMAIL EXISTS CHECK */

            $check = $conn->prepare("SELECT id FROM users WHERE email=?");
            $check->bind_param("s", $email);
            $check->execute();
            $check->store_result();

            if ($check->num_rows > 0) {
                $errors[] = [$name, $email, "Email already exists"];
                continue;
            }

            /* INSERT USER */

            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("INSERT INTO users (name,email,password) VALUES (?,?,?)");
            $stmt->bind_param("sss", $name, $email, $hashed_password);
            $stmt->execute();

            $added++;
        }

        fclose($file);

        $message = "<div class='alert alert-success'>$added users added successfully.</div>";

        /* CREATE ERROR REPORT */

        if (!empty($errors)) {

            $errorFile = "error_report_" . time() . ".csv";

            $fp = fopen($errorFile, 'w');

            fputcsv($fp, ["Name", "Email", "Error"]);

            foreach ($errors as $err) {
                fputcsv($fp, $err);
            }

            fclose($fp);

            $message .= "<div class='alert alert-warning'>
            Some users were not added.<br>
            <a href='$errorFile' download class='btn btn-warning btn-sm mt-2'>
            Download Error Report
            </a>
            </div>";
        }
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Upload Users CSV</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: #f4f6f9;
        }
    </style>

</head>

<body>

    <nav class="navbar navbar-dark bg-dark">
        <div class="container-fluid">
            <span class="navbar-brand">CampusHubX Admin</span>
            <a href="user_authentication.php" class="btn btn-light">Back</a>
        </div>
    </nav>

    <div class="container mt-5">

        <div class="card shadow p-4">

            <h4>Upload Users via CSV</h4>

            <?php echo $message; ?>

            <a href="?download_template=true" class="btn btn-success mb-3">
                Download CSV Template
            </a>

            <form method="POST" enctype="multipart/form-data">

                <div class="mb-3">

                    <label class="form-label">Select CSV File</label>

                    <input type="file" name="csv_file" class="form-control" accept=".csv" required>

                </div>

                <button type="submit" name="upload" class="btn btn-primary">
                    Upload CSV
                </button>

            </form>

        </div>

    </div>

</body>

</html>