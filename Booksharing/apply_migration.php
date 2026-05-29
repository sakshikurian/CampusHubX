<?php
include "db_connect.php";

// Apply the schema migration
$migrations = [
    "ALTER TABLE comments ADD COLUMN parent_comment_id INT NULL DEFAULT NULL AFTER query_id"
];

foreach ($migrations as $migration) {
    echo "Executing: " . $migration . "\n";
    
    if (mysqli_query($conn, $migration)) {
        echo "✓ Migration applied successfully\n";
    } else {
        // Check if column already exists
        if (strpos(mysqli_error($conn), "Duplicate column name") !== false) {
            echo "✓ Column already exists - skipping\n";
        } else {
            echo "✗ Error: " . mysqli_error($conn) . "\n";
        }
    }
}

echo "\nAll migrations completed!";
mysqli_close($conn);
?>
