<?php
session_start();
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
$stmt->execute();

/* SEND APPROVAL EMAIL */
mail(
    $user['email'],
    "CampusHubX Account Approved",
    "Hello " . $user['name'] . ",\n\nYour CampusHubX account has been approved.\nYou can now login to the system.\n\nCampusHubX Team"
);

/* DELETE FROM pending_users */
$conn->query("DELETE FROM pending_users WHERE id=$id");

/* REDIRECT */
header("Location: user_authentication.php");
exit();

?>