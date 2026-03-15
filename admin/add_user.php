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

</body>

</html>