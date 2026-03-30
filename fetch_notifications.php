<?php
session_start();
include "includes/db.php";

$userId = $_SESSION['user_id'] ?? 0;

$q = mysqli_query($conn, "
SELECT * FROM notifications 
WHERE user_id=$userId 
ORDER BY id DESC 
LIMIT 5
");

if (mysqli_num_rows($q) == 0) {
    echo "<li class='dropdown-item text-muted'>No notifications</li>";
}

while ($row = mysqli_fetch_assoc($q)) {
    echo "
    <li class='dropdown-item'>
        " . $row['message'] . "<br>
        <small class='text-muted'>" . $row['created_at'] . "</small>
    </li>";
}
?>