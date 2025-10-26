<?php
// Start the session to manage user login state
session_start();

// Check if the user is already logged in (optional but good practice)
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

include('includes/db.php'); // Include the database connection

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];

    // 1. Select user from database
    $sql = "SELECT id, name, password FROM users WHERE email = ?";

    // Use prepared statements for security (highly recommended!)
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();

        // 2. Verify password (assuming you will hash passwords in register.php)
        if (password_verify($password, $user['password'])) {
            // Password is correct, start session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];

            header("Location: dashboard.php"); // Redirect to dashboard
            exit();
        } else {
            $message = "<div class='alert alert-danger'>Invalid email or password.</div>";
        }
    } else {
        $message = "<div class='alert alert-danger'>Invalid email or password.</div>";
    }
    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CampusHubX Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Simple CSS to center the card */
        .login-container {
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #f3dfe1ff;
        }
    </style>
</head>

<body>

    <div class="login-container">
        <div class="card shadow-lg p-4" style="width: 380px, background-color:">
            <h3 class="text-center mb-4 text-primary">CampusHubX Login</h3>

            <?php echo $message; ?>

            <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <div class="mb-3">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" class="form-control" id="email" name="email" required autocomplete="off">
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
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
</body>

</html>