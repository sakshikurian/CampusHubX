<?php

$source = $_POST['source'];
$destination = $_POST['destination'];

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

        <pre>

<?php
echo $output;
?>

</pre>

    </div>

    <a href="navigation.php" class="btn btn-primary">
        Back
    </a>

</body>

</html>