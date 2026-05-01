<?php
session_set_cookie_params([
    'path' => '/'
]);
session_start();
include("includes/db.php");

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

        .hero-panel {
            color: white;
            border-radius: 32px;
            background: linear-gradient(135deg, var(--brand-dark), var(--brand));
            box-shadow: 0 24px 60px rgba(9, 41, 66, 0.24);
        }

        .form-panel {
            border: 1px solid rgba(15, 76, 129, 0.12);
            border-radius: 32px;
            background: var(--soft);
            backdrop-filter: blur(8px);
            box-shadow: 0 24px 60px rgba(15, 76, 129, 0.12);
        }

        .badge-soft {
            display: inline-flex;
            border-radius: 999px;
            padding: 8px 14px;
            background: rgba(255, 255, 255, 0.14);
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
#studentContent, #adminContent {
    transition: all 0.35s ease;
}

.hidden {
    opacity: 0;
    transform: translateY(10px);
    pointer-events: none;
    position: absolute;
}

.show {
    opacity: 1;
    transform: translateY(0);
    position: relative;
}
@media (max-width: 992px) {
   
    .hero-panel {
        display: none; /* hide left panel on mobile */
    }

    .form-panel {
        border-radius: 20px;
        padding: 25px !important;
    }

    .auth-shell {
        padding: 20px;
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
}
.toggle-wrapper {
    display: flex;
}

.toggle-bg {
    position: relative;
    background: rgba(255,255,255,0.15);
    border-radius: 50px;
    padding: 5px;
    display: flex;
    width: 180px;
}

.toggle-bg button {
    flex: 1;
    border: none;
    background: transparent;
    color: white;
    font-weight: 500;
    z-index: 2;
    padding: 6px 0;
    border-radius: 50px;
}

.toggle-slider {
    position: absolute;
    top: 5px;
    left: 5px;
    width: 50%;
    height: calc(100% - 10px);
    background: white;
    border-radius: 50px;
    transition: all 0.35s ease;
    z-index: 1;
}

/* active text color */
.toggle-bg button.active {
    color: #0f4c81;
}
    </style>
</head>
<body>
    <div class="container auth-shell d-flex align-items-center py-4">
        <div class="row g-4 w-100 align-items-center">
            <!-- LEFT PANEL -->


            <div class="col-lg-6">
                <div class="form-panel p-4 p-lg-5">
                    <div class="mb-4">
                        <h2 class="fw-bold mb-2">Welcome back</h2>
                        <p class="text-muted mb-0">Use your account to enter the ResourceHub dashboard.</p>
                    </div>

                    <?php if ($error) { ?>
                        <div class="alert alert-danger rounded-4"><?= htmlspecialchars($error) ?></div>
                    <?php } ?>

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

   <script>
function showStudent() {
    const student = document.getElementById("studentContent");
    const admin = document.getElementById("adminContent");

    student.classList.add("show");
    student.classList.remove("hidden");

    admin.classList.add("hidden");
    admin.classList.remove("show");

    document.getElementById("toggleSlider").style.left = "5px";

    document.getElementById("studentTab").classList.add("active");
    document.getElementById("adminTab").classList.remove("active");

    // sync form
    document.getElementById("student").checked = true;
}

function showAdmin() {
    const student = document.getElementById("studentContent");
    const admin = document.getElementById("adminContent");

    admin.classList.add("show");
    admin.classList.remove("hidden");

    student.classList.add("hidden");
    student.classList.remove("show");

    document.getElementById("toggleSlider").style.left = "50%";

    document.getElementById("adminTab").classList.add("active");
    document.getElementById("studentTab").classList.remove("active");

    // sync form
    document.getElementById("admin").checked = true;
}

// default load
window.addEventListener("load", () => {
    showStudent();
});
</script>
</body>
</html>