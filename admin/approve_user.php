<?php
require_once '../includes/session.php';
include("../includes/db.php");

/* CHECK ADMIN LOGIN */
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../index.php");
    exit();
}

/* CHECK IF ID EXISTS */
if (!isset($_GET['id'])) {
    header("Location: user_authentication.php");
    exit();
}

$id = intval($_GET['id']);

/* GET USER FROM pending_users */
$result = $conn->query("SELECT * FROM pending_users WHERE id=$id");

if ($result->num_rows == 0) {
    header("Location: user_authentication.php");
    exit();
}

$user = $result->fetch_assoc();

/* INSERT INTO users TABLE */
$stmt = $conn->prepare("INSERT INTO users (name,email,password) VALUES (?,?,?)");
$stmt->bind_param("sss", $user['name'], $user['email'], $user['password']);

if (!$stmt->execute()) {
    $_SESSION['approval_message'] = [
        'type' => 'danger',
        'text' => 'Could not approve the user. Please try again.'
    ];

    header("Location: user_authentication.php");
    exit();
}

$loginUrl = "http://" . $_SERVER['HTTP_HOST'] . dirname(dirname($_SERVER['PHP_SELF'])) . "/index.php";
$loginUrl = str_replace("\\", "/", $loginUrl);

$subject = "CampusHubX Account Approved";
$message = "Hello " . $user['name'] . ",\n\n"
    . "Your CampusHubX account request has been approved.\n"
    . "You can now log in using your registered email ID: " . $user['email'] . "\n\n"
    . "Login here: " . $loginUrl . "\n\n"
    . "Regards,\nCampusHubX Team";

$headers = [
    "From: CampusHubX <no-reply@campushubx.local>",
    "Reply-To: no-reply@campushubx.local",
    "MIME-Version: 1.0",
    "Content-Type: text/plain; charset=UTF-8",
    "X-Mailer: PHP/" . phpversion()
];

/* SEND APPROVAL EMAIL */
$mailSent = mail(
    $user['email'],
    $subject,
    $message,
    implode("\r\n", $headers)
);

/* DELETE FROM pending_users */
$conn->query("DELETE FROM pending_users WHERE id=$id");

$_SESSION['approval_message'] = [
    'type' => $mailSent ? 'success' : 'warning',
    'text' => $mailSent
        ? 'User approved and approval email sent to ' . $user['email'] . '.'
        : 'User approved, but the approval email could not be sent. Check PHP/XAMPP mail settings.'
];

/* REDIRECT */
header("Location: user_authentication.php");
exit();

?>
