<?php
include('includes/db.php');
session_start();
$message = '';

/* CHECK IF ADMIN EXISTS */

$adminCheck = $conn->query("SELECT id FROM admins LIMIT 1");
$adminExists = $adminCheck->num_rows > 0;

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = trim($conn->real_escape_string($_POST['name']));
    $email = trim($conn->real_escape_string($_POST['email']));
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = $_POST['role'] ?? 'student';

    /* VALIDATIONS */

    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {

        $message = "<div class='alert alert-danger'>All fields are required!</div>";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $message = "<div class='alert alert-danger'>Invalid email format!</div>";

    } elseif (!preg_match("/^[a-zA-Z0-9._%+-]+@comp\.fcrit\.ac\.in$/", $email)) {

        $message = "<div class='alert alert-danger'>
        Only college email (@comp.fcrit.ac.in) is allowed!
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

        $message = "<div class='alert alert-danger'>Passwords do not match!</div>";

    } else {

        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        /* ADMIN REGISTRATION */

        if ($role == "admin") {

            if ($adminExists) {

                $message = "<div class='alert alert-danger'>Admin already registered!</div>";

            } else {

                $checkEmail = $conn->prepare("SELECT id FROM admins WHERE email=?");
                $checkEmail->bind_param("s", $email);
                $checkEmail->execute();
                $checkEmail->store_result();

                if ($checkEmail->num_rows > 0) {

                    $message = "<div class='alert alert-danger'>Admin email already exists!</div>";

                } else {

                    $stmt = $conn->prepare("INSERT INTO admins (name,email,password) VALUES (?,?,?)");
                    $stmt->bind_param("sss", $name, $email, $hashed_password);

                    if ($stmt->execute()) {

                        $message = "<div class='alert alert-success'>Admin registered successfully!</div>";

                    } else {

                        $message = "<div class='alert alert-danger'>Registration failed!</div>";

                    }

                }

            }

        }

        /* STUDENT REGISTRATION */ else {

            $checkEmail = $conn->prepare("SELECT id FROM users WHERE email=?");
            $checkEmail->bind_param("s", $email);
            $checkEmail->execute();
            $checkEmail->store_result();

            $checkPending = $conn->prepare("SELECT id FROM pending_users WHERE email=?");
            $checkPending->bind_param("s", $email);
            $checkPending->execute();
            $checkPending->store_result();

            if ($checkEmail->num_rows > 0) {

                $message = "<div class='alert alert-danger'>Email already registered!</div>";

            } elseif ($checkPending->num_rows > 0) {

                $message = "<div class='alert alert-warning'>
                Your registration is already pending admin approval.
                </div>";

            } else {

                $stmt = $conn->prepare("INSERT INTO pending_users (name,email,password) VALUES (?,?,?)");
                $stmt->bind_param("sss", $name, $email, $hashed_password);

                if ($stmt->execute()) {

                    $message = "<div class='alert alert-success'>
                    Registration successful! Your account is waiting for admin approval.
                    </div>";

                } else {

                    $message = "<div class='alert alert-danger'>Registration failed!</div>";

                }

            }

        }

    }

}
?>

<!DOCTYPE html>
<html>

<head>

    <title>CampusHubX Registration</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: linear-gradient(135deg, #dbeafe, #bfdbfe);
            font-family: 'Poppins', sans-serif;
        }

        .register-container {
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 20px 20px rgba(0, 0, 0, 0.1);
            background-color: #e6eff9ff;
        }
    </style>

</head>

<body>

    <div class="register-container">

        <div class="card shadow-lg p-4" style="width:400px;">

            <h3 class="text-center mb-4 text-success">Create Account</h3>

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

                <?php if (!$adminExists) { ?>

                    <div class="mb-3">

                        <label class="form-label">Register As</label>

                        <select name="role" class="form-control">

                            <option value="student">Student</option>
                            <option value="admin">Admin</option>

                        </select>

                    </div>

                <?php } ?>

                <button type="submit" class="btn btn-success w-100 mb-3">Register</button>

            </form>

            <div class="text-center">

                Already have account?
                <a href="index.php">Login</a>

            </div>

        </div>

    </div>

</body>

</html>