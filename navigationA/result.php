<?php

$rooms = [
"AX-501A","AX-501B","AX-502","AX-503A","AX-503B",
"AX-504","AX-505A","AX-505B","AX-506","AX-507",
"AX-508","AX-509","AX-510","AX-511","AX-512",
"AX-513A","AX-513B","AX-514A","AX-514B",
"AX-515A","AX-515B","AX-516B"
];

$map = array_flip($rooms);

$source = $map[$_POST['source']];
$destination = $map[$_POST['destination']];

// run C program
$command = "dijkstra $source $destination";
$output = shell_exec($command);

?>

<!DOCTYPE html>
<html>
<head>
<title>Navigation Result</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="container mt-5">

<h2>Shortest Path Result</h2>

<div class="alert alert-info">
<pre><?php echo $output; ?></pre>
</div>

<a href="navigation.php" class="btn btn-primary">Back</a>

</body>
</html>