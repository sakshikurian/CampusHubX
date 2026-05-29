<?php
include("../includes/db.php");

$id = $_GET['id'];

$conn->query("
DELETE FROM pending_users
WHERE id=$id
");

header("Location: user_authentication.php");