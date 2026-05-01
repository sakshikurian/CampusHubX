<?php
session_start();
include "includes/db.php";

$id = $_GET['id'] ?? 0;

mysqli_query($conn, "
    UPDATE notifications 
    SET is_read = 1 
    WHERE id = $id
");

// redirect to actual page
header("Location: " . $_GET['link']);
exit();
?>