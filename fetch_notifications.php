<?php
require_once 'includes/session.php';
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

    $style = ($row['is_read'] == 0)
        ? "font-weight:bold; color:#000;"
        : "color:#666;";

    echo "<li class='dropdown-item'>
    
    <a href='../campushubx/mark_read.php?id={$row['id']}&link=" . urlencode($row['link']) . "'
    style='
        display:block;
        padding:8px;
        margin-bottom:5px;
        border-radius:8px;
        text-decoration:none;
        $style
    '>

        {$row['message']}
        <br>
        <small>{$row['created_at']}</small>

    </a>

    </li>";
}
?>