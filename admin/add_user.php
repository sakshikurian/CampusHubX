<?php
session_start();
include("../includes/db.php");

/* CHECK ADMIN LOGIN */

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../index.php");
    exit();
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    /* VALIDATIONS */

    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {

        $message = "<div class='alert alert-danger'>All fields are required.</div>";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $message = "<div class='alert alert-danger'>Invalid email format.</div>";

    } elseif (!preg_match("/^[a-zA-Z0-9._%+-]+@comp\.fcrit\.ac\.in$/", $email)) {

        $message = "<div class='alert alert-danger'>
        Only college email (@comp.fcrit.ac.in) is allowed.
        </div>";

    } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*[\W_]).{8,20}$/', $password)) {

        $message = "<div class='alert alert-danger'>
        Password must:
        <ul class='mb-0'>
        <li>Be between 8 and 20 characters</li>
        <li>Contain at least 1 uppercase letter</li>
        <li>Contain at least 1 lowercase letter</li>
        <li>Contain at least 1 special character</li>
        </ul>
        </div>";

    } elseif ($password !== $confirm_password) {

        $message = "<div class='alert alert-danger'>Passwords do not match.</div>";

    } else {

        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        /* CHECK IF EMAIL EXISTS */

        $check = $conn->prepare("SELECT id FROM users WHERE email=?");
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {

            $message = "<div class='alert alert-danger'>User already exists.</div>";

        } else {

            $stmt = $conn->prepare("INSERT INTO users (name,email,password) VALUES (?,?,?)");
            $stmt->bind_param("sss", $name, $email, $hashed_password);

            if ($stmt->execute()) {

                $message = "<div class='alert alert-success'>User added successfully.</div>";

            } else {

                $message = "<div class='alert alert-danger'>Failed to add user.</div>";

            }

        }

    }

}
?>

<!DOCTYPE html>
<html>

<head>

    <title>Add User</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

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
        body {
            background: #cfe2f3;
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
                <div class="brand-mark">AU</div>
                <div>
                    <a class="navbar-brand fw-bold mb-0" href="user_authentication.php">Add User</a>
                    <div class="small text-white-50">Manually add a new user</div>
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

            <h4>Add User Manually</h4>

            <?php echo $message; ?>

            <form method="POST">

                <div class="mb-3">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="name" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Password</label>

                    <input type="password" name="password" class="form-control"
                        pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*[\W_]).{8,20}$"
                        title="Password must contain uppercase, lowercase, special character and be 8–20 characters long"
                        required>

                </div>

                <div class="mb-3">
                    <label class="form-label">Confirm Password</label>
                    <input type="password" name="confirm_password" class="form-control" required>
                </div>

                <button type="submit" class="btn btn-success">Add User</button>

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