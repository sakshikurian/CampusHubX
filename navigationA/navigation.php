<?php
session_start();

$username = "Student";

$labs = [
    "AX502",
    "AX505",
    "AX506",
    "AX508",
    "AX510",
    "AX512",
    "AX504",
    "AX507",
    "AX509",
    "AX511",
    "AX516"
];
?>

<!DOCTYPE html>
<html>

<head>

    <title>Campus Navigation</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body class="bg-light">

    <div class="container mt-5">

        <h2>Campus Lab Navigation</h2>

        <form action="find_path.php" method="POST">

            <div class="row">

                <div class="col-md-5">

                    <label>Source Lab</label>

                    <select name="source" class="form-control">

                        <?php
                        foreach ($labs as $index => $lab) {
                            echo "<option value='$index'>$lab</option>";
                        }
                        ?>

                    </select>

                </div>

                <div class="col-md-5">

                    <label>Destination Lab</label>

                    <select name="destination" class="form-control">

                        <?php
                        foreach ($labs as $index => $lab) {
                            echo "<option value='$index'>$lab</option>";
                        }
                        ?>

                    </select>

                </div>

                <div class="col-md-2">

                    <button class="btn btn-primary mt-4 w-100">
                        Find Path
                    </button>

                </div>

            </div>

        </form>

    </div>

</body>

</html>