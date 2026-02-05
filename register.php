<?php
include('includes/db.php');
session_start();
$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($conn->real_escape_string($_POST['name']));
    $email = trim($conn->real_escape_string($_POST['email']));
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // 1️⃣ Basic field validation
    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        $message = "<div class='alert alert-danger'>All fields are required!</div>";
    }
    // 2️⃣ Email format validation
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "<div class='alert alert-danger'>Invalid email format!</div>";
    }
    // 3️⃣ Password length validation
    elseif (strlen($password) < 8) {
        $message = "<div class='alert alert-danger'>Password must be at least 8 characters long!</div>";
    }
    // 4️⃣ Check if passwords match
    elseif ($password !== $confirm_password) {
        $message = "<div class='alert alert-danger'>Passwords do not match!</div>";
    } else {
        // 5️⃣ Check if email already exists
        $checkEmail = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $checkEmail->bind_param("s", $email);
        $checkEmail->execute();
        $checkEmail->store_result();

        if ($checkEmail->num_rows > 0) {
            $message = "<div class='alert alert-danger'>Email already registered. Please login!</div>";
        } else {
            // 6️⃣ Hash the password securely
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // 7️⃣ Insert into database
            $sql = "INSERT INTO users (name, email, password) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sss", $name, $email, $hashed_password);

            if ($stmt->execute()) {
                $message = "<div class='alert alert-success'>Registration successful! <a href='index.php'>Login here</a>.</div>";
            } else {
                $message = "<div class='alert alert-danger'>Registration failed. Please try again later.</div>";
            }
            $stmt->close();
        }
        $checkEmail->close();
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

        .btn-success {
            background-color: #4caf50;
            border: none;
        }

        .btn-success:hover {
            background-color: #43a047;
        }
    </style>
</head>

<body>

    <div class="register-container">
        <div class="card shadow-lg p-4" style="width: 400px;">
            <h3 class="text-center mb-4 text-success">Create Account</h3>

            <?php echo $message; ?>

            <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>"
                onsubmit="return validateForm()">
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
                    <div class="form-text">Min 8 characters required.</div>
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

    <script>
        function validateForm() {
            const email = document.getElementById("email").value.trim();
            const password = document.getElementById("password").value;
            const confirm = document.getElementById("confirm_password").value;

            const emailPattern = /^[^ ]+@[^ ]+\.[a-z]{2,3}$/;

            if (!email.match(emailPattern)) {
                alert("Please enter a valid email address.");
                return false;
            }
            if (password.length < 8) {
                alert("Password must be at least 8 characters long.");
                return false;
            }
            if (password !== confirm) {
                alert("Passwords do not match!");
                return false;
            }
            return true;
        }
    </script>

</body>

</html>