<?php
require_once 'includes/session.php';
include("includes/db.php");

if (isset($_SESSION['admin_id'])) {
    header("Location: admin/dashboard.php");
    exit();
}

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $role = $_POST['role'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    if ($role == "admin") {

        $stmt = $conn->prepare("SELECT * FROM admins WHERE email=?");
        $stmt->bind_param("s", $email);

    } else {

        /* CHECK IF USER IS STILL PENDING APPROVAL */

        $pending = $conn->prepare("SELECT id FROM pending_users WHERE email=?");
        $pending->bind_param("s", $email);
        $pending->execute();
        $pendingResult = $pending->get_result();

        if ($pendingResult->num_rows > 0) {

            $error = "<div class='alert alert-warning'>
            Your account is waiting for admin approval.
            </div>";

        } else {

            $stmt = $conn->prepare("SELECT * FROM users WHERE email=?");
            $stmt->bind_param("s", $email);

        }
    }

    if (isset($stmt)) {

        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user && password_verify($password, $user['password'])) {

            if ($role == "admin") {

                $_SESSION['admin_id'] = $user['id'];
                $_SESSION['admin_name'] = $user['name'];

                header("Location: admin/dashboard.php");
                exit();

            } else {

                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];

                header("Location: dashboard.php");
                exit();
            }

        } else {

            if ($error == "") {
                $error = "<div class='alert alert-danger'>Invalid login credentials</div>";
            }

        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login | ResourceHub</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --brand: #0f4c81;
            --brand-dark: #092942;
            --soft: rgba(255, 255, 255, 0.9);
        }

        body {
            min-height: 100vh;
            margin: 0;
            font-family: "Manrope", sans-serif;
            background:
                radial-gradient(circle at top left, rgba(255, 159, 28, 0.18), transparent 25%),
                radial-gradient(circle at bottom right, rgba(15, 76, 129, 0.22), transparent 28%),
                linear-gradient(160deg, #eff7ff 0%, #f7fbff 55%, #eef2f7 100%);
        }

        .auth-shell {
            min-height: 100vh;
        }

        .auth-card-col {
            max-width: 520px;
        }

        .form-panel {
            border: 1px solid rgba(15, 76, 129, 0.12);
            border-radius: 32px;
            background: var(--soft);
            backdrop-filter: blur(8px);
            box-shadow: 0 24px 60px rgba(15, 76, 129, 0.12);
            width: 100%;
        }

        .form-control {
    min-height: 52px;
    border-radius: 16px;
    background: #eef5fb;
    border: 1px solid rgba(15, 76, 129, 0.2);
    color: #16324f;
}

.form-control:focus {
    background: #ffffff;
    border-color: #0f4c81;
    box-shadow: 0 0 0 0.2rem rgba(15, 76, 129, 0.15);
}
        .btn-primary {
    background: linear-gradient(135deg, #0f4c81, #092942);
    border: none;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #0c3f6b, #072033);
}

.btn-check:checked + .btn {
    background: linear-gradient(135deg, #0f4c81, #092942);
    color: white;
    border: none;
}

.btn-outline-primary{
    border-radius: 12px;
    border: 1px solid rgba(15, 76, 129, 0.3);
    color: #0f4c81;
    width: 50%;
}
.btn-outline-danger {
    border-radius: 12px;
    border: 1px solid #dc3545;
    color: #dc3545;
    width: 50%;
    background: transparent;
}

.btn-outline-danger:hover {
    background: #dc3545;
    color: white;
}

body.dark-mode {
    color: #ffffff;
    background: #3e3d3d !important;
}

body.dark-mode .form-panel {
    background: #1e1e1e;
    border-color: #2c2c2c;
    color: #ffffff;
}

body.dark-mode .form-control {
    background: #2f2f2f;
    border-color: #444;
    color: #ffffff;
}

body.dark-mode .text-muted {
    color: #cfcfcf !important;
}

@media (max-width: 992px) {
    .form-panel {
        border-radius: 20px;
        padding: 25px !important;
    }

    .auth-shell {
        padding: 20px 12px;
    }

    .btn-group {
        display: flex;
    }

    .btn-group .btn {
        font-size: 14px;
        padding: 10px;
    }
}
@media (max-width: 576px) {

    h2 {
        font-size: 22px;
    }

    .form-control {
        min-height: 45px;
    }

    .btn-primary {
        padding: 12px;
        font-size: 16px;
    }

    .form-panel {
        border-radius: 18px;
    }
}
    </style>
</head>
<body>
    <div class="container auth-shell d-flex align-items-center justify-content-center py-4">
        <div class="row g-4 w-100 align-items-center justify-content-center">
            <div class="col-12 auth-card-col">
                <div class="form-panel p-4 p-lg-5">
                    <div class="mb-4">
                        <h2 class="fw-bold mb-2">Welcome back</h2>
                        <p class="text-muted mb-0">Use your account to enter the ResourceHub dashboard.</p>
                    </div>

                    <?= $error ?>

                    <form method="POST" novalidate>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Email address</label>
                            <input type="email" name="email" class="form-control" placeholder="name@example.com" required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">Password</label>
                            <input type="password" name="password" class="form-control" placeholder="Enter your password" required>
                        </div>
                        <div class="mb-3 text-center">

                            <label class="form-label fw-semibold">Login As</label>

                            <div class="btn-group w-100" role="group">

                                <input type="radio" class="btn-check" name="role" id="student" value="student" checked>

                                <label class="btn btn-outline-primary" for="student">Student</label>

                                <input type="radio" class="btn-check" name="role" id="admin" value="admin">

                                <label class="btn btn-outline-danger" for="admin">Admin</label>

                            </div>

                        </div>

                        <button class="btn btn-primary w-100 py-3 rounded-pill fw-semibold">Sign In</button>
                    </form>

                    <p class="text-center text-muted mt-4 mb-0">New here? <a href="register.php" class="fw-semibold text-decoration-none">Create an account</a></p>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // PAGE TRANSITION
        window.addEventListener('load', () => {
            document.body.classList.add('page-loaded');
        });

        // DARK MODE TOGGLE (optional for login, but add if wanted)
        // For login, perhaps no toggle, but support dark mode if set
        if (localStorage.getItem("darkMode") === "on") {
            document.body.classList.add("dark-mode");
        }
    </script>

</body>
</html>
