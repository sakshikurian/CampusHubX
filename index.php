<?php
session_start();
include("includes/db.php");

$message = "";

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

            $message = "<div class='alert alert-warning'>
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

            if ($message == "") {
                $message = "<div class='alert alert-danger'>Invalid login credentials</div>";
            }

        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>CampusHubX Login</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        .login-container {
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #cfe2f3;
        }
    </style>

</head>

<body>

    <div class="login-container">

        <div class="card shadow-lg p-4" style="width:380px">

            <h3 class="text-center mb-4 text-primary">CampusHubX Login</h3>

            <!-- MESSAGE BOX -->

            <div id="approvalMessage">
                                <?php echo $message; ?>
            </div>

            <form method="POST">

                <div class="mb-3">

                    <label class="form-label">Email Address</label>

                    <input type="email" class="form-control" name="email" required autocomplete="off">

                </div>

                <div class="mb-3">

                    <label class="form-label">Password</label>

                    <input type="password" class="form-control" name="password" required>

                </div>

                <div class="mb-3 text-center">

                    <label class="form-label d-block">Login As</label>

                    <div class="btn-group w-100" role="group">

                        <input type="radio" class="btn-check" name="role" id="student" value="student" checked>

                        <label class="btn btn-outline-primary" for="student">Student</label>

                        <input type="radio" class="btn-check" name="role" id="admin" value="admin">

                        <label class="btn btn-outline-danger" for="admin">Admin</label>

                    </div>

                </div>

                <button type="submit" class="btn btn-primary w-100 mb-3">Sign In</button>

            </form>

            <div class="text-center mt-2">

                <p class="mb-0">Don't have an account?</p>

                <a href="register.php" class="text-decoration-none">Create Account Here</a>

            </div>

        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>

        const studentBtn = document.getElementById("student");
        const adminBtn = document.getElementById("admin");
        const messageBox = document.getElementById("approvalMessage");

        function toggleMessage() {

            if (adminBtn.checked) {
                messageBox.style.display = "none";
            } else {
                messageBox.style.display = "block";
            }

        }

        studentBtn.addEventListener("change", toggleMessage);
        adminBtn.addEventListener("change", toggleMessage);

    </script>

</body>

</html>