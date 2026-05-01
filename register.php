<?php
require_once 'includes/session.php';
include('includes/db.php');

$message = '';

$adminCheck = $conn->query("SELECT id FROM admins LIMIT 1");
$adminExists = $adminCheck->num_rows > 0;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($conn->real_escape_string($_POST['name']));
    $email = trim($conn->real_escape_string($_POST['email']));
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = $_POST['role'] ?? 'student';

    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        $message = "<div class='alert alert-danger rounded-4'>All fields are required.</div>";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "<div class='alert alert-danger rounded-4'>Invalid email format.</div>";
    } elseif (!preg_match("/^[a-zA-Z0-9._%+-]+@comp\.fcrit\.ac\.in$/", $email)) {
        $message = "<div class='alert alert-danger rounded-4'>Only college email (@comp.fcrit.ac.in) is allowed.</div>";
    } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*[\W_]).{8,20}$/', $password)) {
        $message = "<div class='alert alert-danger rounded-4'>
            Password must be 8-20 characters and include uppercase, lowercase, and a special character.
        </div>";
    } elseif ($password !== $confirm_password) {
        $message = "<div class='alert alert-danger rounded-4'>Passwords do not match.</div>";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        if ($role == "admin") {
            if ($adminExists) {
                $message = "<div class='alert alert-danger rounded-4'>Admin already registered.</div>";
            } else {
                $checkEmail = $conn->prepare("SELECT id FROM admins WHERE email=?");
                $checkEmail->bind_param("s", $email);
                $checkEmail->execute();
                $checkEmail->store_result();

                if ($checkEmail->num_rows > 0) {
                    $message = "<div class='alert alert-danger rounded-4'>Admin email already exists.</div>";
                } else {
                    $stmt = $conn->prepare("INSERT INTO admins (name,email,password) VALUES (?,?,?)");
                    $stmt->bind_param("sss", $name, $email, $hashed_password);

                    if ($stmt->execute()) {
                        $message = "<div class='alert alert-success rounded-4'>Admin registered successfully.</div>";
                    } else {
                        $message = "<div class='alert alert-danger rounded-4'>Registration failed.</div>";
                    }
                }
            }
        } else {
            $checkEmail = $conn->prepare("SELECT id FROM users WHERE email=?");
            $checkEmail->bind_param("s", $email);
            $checkEmail->execute();
            $checkEmail->store_result();

            $checkPending = $conn->prepare("SELECT id FROM pending_users WHERE email=?");
            $checkPending->bind_param("s", $email);
            $checkPending->execute();
            $checkPending->store_result();

            if ($checkEmail->num_rows > 0) {
                $message = "<div class='alert alert-danger rounded-4'>Email already registered.</div>";
            } elseif ($checkPending->num_rows > 0) {
                $message = "<div class='alert alert-warning rounded-4'>Your registration is already pending admin approval.</div>";
            } else {
                $stmt = $conn->prepare("INSERT INTO pending_users (name,email,password) VALUES (?,?,?)");
                $stmt->bind_param("sss", $name, $email, $hashed_password);

                if ($stmt->execute()) {
                    $message = "<div class='alert alert-success rounded-4'>
                        Registration successful. Your account is waiting for admin approval.
                    </div>";
                } else {
                    $message = "<div class='alert alert-danger rounded-4'>Registration failed.</div>";
                }
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
    <title>Register | ResourceHub</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        :root {
            --brand: #0f4c81;
            --brand-dark: #092942;
            --soft: rgba(255, 255, 255, 0.9);
            --text-main: #16324f;
            --text-muted: #5f7488;
        }

        body {
            min-height: 100vh;
            margin: 0;
            font-family: "Manrope", sans-serif;
            color: var(--text-main);
            opacity: 0;
            transition: opacity 0.4s ease-in-out;
            background:
                radial-gradient(circle at top left, rgba(255, 159, 28, 0.18), transparent 25%),
                radial-gradient(circle at bottom right, rgba(15, 76, 129, 0.22), transparent 28%),
                linear-gradient(160deg, #eff7ff 0%, #f7fbff 55%, #eef2f7 100%);
        }

        body.page-loaded {
            opacity: 1;
        }

        .auth-shell {
            min-height: 100vh;
        }

        .auth-card-col {
            max-width: 560px;
        }

        .form-panel {
            width: 100%;
            border: 1px solid rgba(15, 76, 129, 0.12);
            border-radius: 32px;
            background: var(--soft);
            backdrop-filter: blur(8px);
            box-shadow: 0 24px 60px rgba(15, 76, 129, 0.12);
        }

        .form-control,
        .form-select {
            min-height: 52px;
            border-radius: 16px;
            background: #eef5fb;
            border: 1px solid rgba(15, 76, 129, 0.2);
            color: var(--text-main);
        }

        .form-control:focus,
        .form-select:focus {
            background: #ffffff;
            border-color: var(--brand);
            box-shadow: 0 0 0 0.2rem rgba(15, 76, 129, 0.15);
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--brand), var(--brand-dark));
            border: none;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #0c3f6b, #072033);
        }

        .helper-text {
            color: var(--text-muted);
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

        body.dark-mode .form-control,
        body.dark-mode .form-select {
            background: #2f2f2f;
            border-color: #444;
            color: #ffffff;
        }

        body.dark-mode .helper-text,
        body.dark-mode .text-muted {
            color: #cfcfcf !important;
        }

        @media (max-width: 992px) {
            .auth-shell {
                padding: 20px 12px;
            }

            .form-panel {
                border-radius: 20px;
                padding: 25px !important;
            }
        }

        @media (max-width: 576px) {
            h2 {
                font-size: 22px;
            }

            .form-control,
            .form-select {
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
                        <h2 class="fw-bold mb-2">Create account</h2>
                        <p class="helper-text mb-0">Use your college email to request access to ResourceHub.</p>
                    </div>

                    <?= $message ?>

                    <form method="POST" novalidate>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Full name</label>
                            <input type="text" name="name" class="form-control" placeholder="Enter your full name" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Email address</label>
                            <input type="email" name="email" class="form-control" placeholder="name@comp.fcrit.ac.in" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Password</label>
                            <input type="password" name="password" class="form-control"
                                placeholder="Create a strong password"
                                pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*[\W_]).{8,20}$"
                                title="Password must contain uppercase, lowercase, special character and be 8-20 characters long"
                                required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Confirm password</label>
                            <input type="password" name="confirm_password" class="form-control" placeholder="Re-enter your password" required>
                        </div>

                        <?php if (!$adminExists) { ?>
                            <div class="mb-4">
                                <label class="form-label fw-semibold">Register as</label>
                                <select name="role" class="form-select">
                                    <option value="student">Student</option>
                                    <option value="admin">Admin</option>
                                </select>
                            </div>
                        <?php } ?>

                        <button type="submit" class="btn btn-primary w-100 py-3 rounded-pill fw-semibold">Create Account</button>
                    </form>

                    <p class="text-center text-muted mt-4 mb-0">Already have an account? <a href="index.php" class="fw-semibold text-decoration-none">Sign in</a></p>
                </div>
            </div>
        </div>
    </div>

    <script>
        window.addEventListener('load', () => {
            document.body.classList.add('page-loaded');
        });

        if (localStorage.getItem("darkMode") === "on" || localStorage.getItem("darkMode") === "enabled") {
            document.body.classList.add("dark-mode");
        }
    </script>
</body>

</html>
