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
            background: #cfe2f3;
        }
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
        /* DARK MODE */
        body.dark-mode {
            background-color: #3e3d3d !important;
            color: #ffffff !important;
        }

        .dark-mode .card {
            background-color: #1e1e1e !important;
            color: #ffffff !important;
            border: 1px solid #2c2c2c;
        }

        .dark-mode .navbar {
            background-color: #000 !important;
        }

        .dark-mode a {
            color: #4dabf7;
        }

        /* PAGE TRANSITION */
        body {
            opacity: 0;
            transition: opacity 0.4s ease-in-out;
        }

        body.page-loaded {
            opacity: 1;
        }
    </style>

</head>

<body>

  <nav class="navbar navbar-expand-lg navbar-dark navbar-shell py-3">
        <div class="container">
            <div class="d-flex align-items-center gap-3">
                <div class="brand-mark">UC</div>
                <div>
                    <a class="navbar-brand fw-bold mb-0" href="user_authentication.php">Upload CSV</a>
                    <div class="small text-white-50">Upload users from a CSV file</div>
                </div>
            </div>

            <div class="d-flex align-items-center gap-3 text-white mt-3 mt-lg-0">
                <button id="darkModeToggle" class="btn btn-outline-light me-2">
                    🌙
                </button>
                
            </div>
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

    <script>
        // DARK MODE TOGGLE
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

        // Page transition
        window.addEventListener('load', () => {
            body.classList.add('page-loaded');
        });
    </script>

</body>

</html>