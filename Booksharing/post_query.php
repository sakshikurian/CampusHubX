<?php
session_start();
include "../includes/db.php";

// protect
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['user_id'];   // use ID, not username
$question = $_POST['question'];

// insert into DB
mysqli_query(
    $conn,
    "INSERT INTO queries (user_id, question)
     VALUES ('$user_id', '$question')"
);

// redirect back
header("Location: index.php?mode=question&msg=posted");
exit();

?>