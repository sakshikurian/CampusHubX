<?php
session_start();
include "db_connect.php";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = mysqli_real_escape_string($conn, trim($_POST['name'] ?? ''));
    $email = mysqli_real_escape_string($conn, trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm'] ?? '';

    if ($password !== $confirm) {
        $error = "Passwords do not match.";
    } else {
        $existing = mysqli_query($conn, "SELECT id FROM users WHERE email='$email'");
        if (mysqli_num_rows($existing) > 0) {
            $error = "An account with this email already exists.";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);

            mysqli_query($conn, "
                INSERT INTO users (name, email, password)
                VALUES ('$name', '$email', '$hash')
            ");

            header("Location: login.php");
            exit();
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --brand: #146c43;
            --brand-dark: #0d4a2d;
            --panel: rgba(255, 255, 255, 0.92);
        }

        body {
            min-height: 100vh;
            margin: 0;
            font-family: "Manrope", sans-serif;
            background:
                radial-gradient(circle at top right, rgba(20, 108, 67, 0.18), transparent 28%),
                radial-gradient(circle at bottom left, rgba(255, 159, 28, 0.16), transparent 26%),
                linear-gradient(160deg, #f0fbf4 0%, #f6fcf8 52%, #eef3ef 100%);
        }

        .auth-shell {
            min-height: 100vh;
        }

        .info-panel {
            color: white;
            border-radius: 32px;
            background: linear-gradient(135deg, var(--brand-dark), var(--brand));
            box-shadow: 0 24px 60px rgba(13, 74, 45, 0.22);
        }

        .form-panel {
            border: 1px solid rgba(20, 108, 67, 0.12);
            border-radius: 32px;
            background: var(--panel);
            box-shadow: 0 24px 60px rgba(20, 108, 67, 0.12);
        }

        .form-control {
            min-height: 52px;
            border-radius: 16px;
            border-color: rgba(20, 108, 67, 0.16);
        }

        .form-control:focus {
            border-color: rgba(25, 135, 84, 0.52);
            box-shadow: 0 0 0 0.25rem rgba(25, 135, 84, 0.14);
        }

        .status-list span {
            display: block;
            margin-bottom: 6px;
            font-size: 0.92rem;
        }
    </style>
</head>
<body>
    <div class="container auth-shell d-flex align-items-center py-4">
        <div class="row g-4 w-100 align-items-center">
            <div class="col-lg-5 order-lg-2">
                <div class="info-panel p-4 p-lg-5 h-100">
                    <div class="badge rounded-pill bg-white bg-opacity-10 px-3 py-2 mb-4">Create your account</div>
                    <h1 class="display-6 fw-bold mb-3">Join a more attractive and useful study-sharing platform.</h1>
                    <p class="text-white-50 mb-4">Your account lets you post questions, upload files, browse resources, and participate in the refreshed ResourceHub experience.</p>
                    <div class="bg-white bg-opacity-10 rounded-4 p-3">
                        <div class="fw-semibold mb-2">Password checklist</div>
                        <div id="passwordChecklist" class="status-list text-white-50">
                            <span data-rule="length">At least 8 characters</span>
                            <span data-rule="upper">One uppercase letter</span>
                            <span data-rule="lower">One lowercase letter</span>
                            <span data-rule="special">One special character</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-7 order-lg-1">
                <div class="form-panel p-4 p-lg-5">
                    <div class="mb-4">
                        <h2 class="fw-bold mb-2">Create account</h2>
                        <p class="text-muted mb-0">Set up your profile and start sharing knowledge with the community.</p>
                    </div>

                    <?php if ($error) { ?>
                        <div class="alert alert-danger rounded-4"><?= htmlspecialchars($error) ?></div>
                    <?php } ?>

                    <form method="POST" id="registerForm" novalidate>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Full name</label>
                                <input type="text" name="name" class="form-control" placeholder="Your name" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Email</label>
                                <input type="email" name="email" class="form-control" placeholder="name@example.com" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Password</label>
                                <input type="password" name="password" id="passwordField" class="form-control" placeholder="Create a strong password" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Confirm password</label>
                                <input type="password" name="confirm" class="form-control" placeholder="Repeat your password" required>
                            </div>
                        </div>

                        <div class="alert alert-warning rounded-4 mt-4 d-none" id="formWarning"></div>

                        <button class="btn btn-success w-100 py-3 rounded-pill fw-semibold mt-4">Register Now</button>
                    </form>

                    <p class="text-center text-muted mt-4 mb-0">Already have an account? <a href="login.php" class="fw-semibold text-decoration-none">Sign in</a></p>
                </div>
            </div>
        </div>
    </div>

    <script>
        const registerForm = document.getElementById('registerForm');
        const passwordField = document.getElementById('passwordField');
        const warningBox = document.getElementById('formWarning');
        const checklist = {
            length: document.querySelector('[data-rule="length"]'),
            upper: document.querySelector('[data-rule="upper"]'),
            lower: document.querySelector('[data-rule="lower"]'),
            special: document.querySelector('[data-rule="special"]')
        };

        function updateRule(element, passed) {
            element.classList.toggle('text-white', passed);
            element.classList.toggle('fw-semibold', passed);
            element.classList.toggle('text-white-50', !passed);
        }

        function validatePasswordRules(value) {
            const result = {
                length: value.length >= 8,
                upper: /[A-Z]/.test(value),
                lower: /[a-z]/.test(value),
                special: /[\W_]/.test(value)
            };

            updateRule(checklist.length, result.length);
            updateRule(checklist.upper, result.upper);
            updateRule(checklist.lower, result.lower);
            updateRule(checklist.special, result.special);

            return Object.values(result).every(Boolean);
        }

        passwordField.addEventListener('input', function() {
            validatePasswordRules(passwordField.value);
        });

        registerForm.addEventListener('submit', function(e) {
            const email = registerForm.querySelector('[name="email"]').value.trim();
            const password = registerForm.querySelector('[name="password"]').value;
            const confirm = registerForm.querySelector('[name="confirm"]').value;
            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

            warningBox.classList.add('d-none');

            if (!emailPattern.test(email)) {
                e.preventDefault();
                warningBox.textContent = 'Please enter a valid email address.';
                warningBox.classList.remove('d-none');
                return;
            }

            if (!validatePasswordRules(password)) {
                e.preventDefault();
                warningBox.textContent = 'Password must match all checklist rules.';
                warningBox.classList.remove('d-none');
                return;
            }

            if (password !== confirm) {
                e.preventDefault();
                warningBox.textContent = 'Passwords do not match.';
                warningBox.classList.remove('d-none');
            }
        });
    </script>
</body>
</html>
