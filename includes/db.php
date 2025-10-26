<?php
$servername = "localhost";
$username = "root";
$password = "";     // IMPORTANT: Leave blank unless you set a root password
$database = "campushubx";
$port = 3307;       // ADDED THE PORT NUMBER

// Create connection
// Note: Port is added as the 5th parameter
$conn = new mysqli($servername, $username, $password, $database, $port);

// Check connection
if ($conn->connect_error) {
    // We stop the script and display a friendly message if connection fails
    die("Connection to MySQL failed: " . $conn->connect_error);
}
// Remove this line when done testing: echo "Connected successfully"; 
?>