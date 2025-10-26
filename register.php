<?php
include('includes/db.php'); // Include database connection
session_start();

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // 1. Check if passwords match
    if ($password !== $confirm_password) {
        $message = "<div class='alert alert-danger'>Passwords do not match!</div>";
    } else {
        // 2. Hash the password securely (Crucial step!)
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // 3. Insert user into database
        $sql = "INSERT INTO users (name, email, password) VALUES (?, ?, ?)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $name, $email, $hashed_password);

        if ($stmt->execute()) {
            $message = "<div class='alert alert-success'>Registration successful! You can now <a href='index.php'>login</a>.</div>";
            // Optional: Auto-login the user immediately
            // $_SESSION['user_id'] = $conn->insert_id;
            // $_SESSION['user_name'] = $name;
            // header("Location: dashboard.php");
            // exit();
        } else {
            // This usually catches the 'email already exists' error due to the UNIQUE constraint
            $message = "<div class='alert alert-danger'>Registration failed. Email might already be in use.</div>";
        }
        $stmt->close();
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CampusHubX Registration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .register-container {
            height: auto;
            /* Adjust height for scrollable content */
            padding: 40px 0;
            background-color: #f8f9fa;
        }
    </style>
</head>

<body>

    <div class="register-container">
        <div class="card shadow-lg p-4 mx-auto" style="width: 380px;">
            <h3 class="text-center mb-4 text-success">Create Account</h3>

            <?php echo $message; ?>

            <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <div class="mb-3">
                    <label for="name" class="form-label">Full Name</label>
                    <input type="text" class="form-control" id="name" name="name" required autocomplete="off">
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" class="form-control" id="email" name="email" required autocomplete="off">
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required minlength="8">
                    <div class="form-text">Min 8 characters recommended.</div>
                </div>
                <div class="mb-3">
                    <label for="confirm_password" class="form-label">Confirm Password</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                </div>

                <button type="submit" class="btn btn-success w-100 mb-3">Register</button>
            </form>

            <div class="text-center mt-2">
                <p class="mb-0">Already have an account?</p>
                <a href="index.php" class="text-decoration-none">Login Here</a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>