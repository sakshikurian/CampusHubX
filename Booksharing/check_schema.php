<?php
include "db_connect.php";

echo "=== Database Structure Verification ===\n\n";

// Check comments table structure
echo "COMMENTS TABLE STRUCTURE:\n";
$result = mysqli_query($conn, "SHOW COLUMNS FROM comments");
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        echo "  - " . $row['Field'] . " (" . $row['Type'] . ")" . ($row['Null'] === 'YES' ? ' [NULL]' : '') . "\n";
    }
}

// Check if parent_comment_id exists
$columnCheck = mysqli_query($conn, "SHOW COLUMNS FROM comments LIKE 'parent_comment_id'");
if ($columnCheck && mysqli_num_rows($columnCheck) > 0) {
    echo "\n✓ parent_comment_id column exists for comment replies\n";
} else {
    echo "\n✗ parent_comment_id column does NOT exist\n";
}

echo "\n\nQUERIES TABLE STRUCTURE:\n";
$result = mysqli_query($conn, "SHOW COLUMNS FROM queries");
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        echo "  - " . $row['Field'] . " (" . $row['Type'] . ")\n";
    }
}

echo "\n\nRESOURCES TABLE STRUCTURE:\n";
$result = mysqli_query($conn, "SHOW COLUMNS FROM resources");
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        echo "  - " . $row['Field'] . " (" . $row['Type'] . ")\n";
    }
}

mysqli_close($conn);
?>
