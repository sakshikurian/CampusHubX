<?php
session_start();
include "../includes/db.php";

$user_id = $_SESSION['user_id'] ?? 1;

$result = $conn->query("
SELECT * FROM orders 
WHERE user_id='$user_id'
ORDER BY order_id DESC
");
?>

<!DOCTYPE html>
<html>

<head>

    <title>My Orders</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body style="background:#f4f6f9">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="../canteen/index.php">⬅ CampusHubX</a>


        </div>
    </nav>
    <div class="container mt-5">

        <h3 class="mb-4"> My Order History</h3>

        <table class="table table-bordered text-center align-middle">

            <thead class="table-dark">

                <tr>
                    <th>Token No</th>
                    <th>Amount</th>
                    <th>Coupon</th>
                    <th>Status</th>
                    <th>Date</th>
                </tr>

            </thead>

            <tbody>

                <?php while ($row = $result->fetch_assoc()) { ?>

                    <tr>

                        <td>Token #<?= $row['token_no'] ?></td>
                        <td>₹<?= $row['amount'] ?></td>

                        <td>

                            <?php if ($row['status'] == "approved") { ?>

                                <a href="generate_bill.php?id=<?= $row['order_id'] ?>" class="btn btn-success btn-sm">
                                    Download Coupon
                                </a>

                            <?php } else { ?>

                                —

                            <?php } ?>

                        </td>

                        <td>

                            <?php if ($row['status'] == "pending") { ?>
                                <span class="badge bg-warning text-dark">Pending</span>

                            <?php } elseif ($row['status'] == "approved") { ?>
                                <span class="badge bg-success">Approved</span>

                            <?php } else { ?>
                                <span class="badge bg-danger">Rejected</span>
                            <?php } ?>

                        </td>

                        <td><?= $row['order_date'] ?></td>

                    </tr>

                <?php } ?>

            </tbody>

        </table>

    </div>

</body>

</html>