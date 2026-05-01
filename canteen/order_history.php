<?php
require_once '../includes/session.php';
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

    <style>
    :root {
            --brand: #0f4c81;
            --brand-dark: #0a2f4f;
            --brand-soft: #e9f4ff;
            --accent: #ff9f1c;
            --success-soft: #e9f8f1;
            --danger-soft: #ffe8ea;
            --surface: rgba(255, 255, 255, 0.9);
            --text-main: #16324f;
            --text-muted: #5f7488;
            --border-soft: rgba(15, 76, 129, 0.12);
            --shadow-soft: 0 18px 40px rgba(15, 76, 129, 0.12);
        }
        .navbar-shell {
            background: linear-gradient(120deg, var(--brand-dark), var(--brand));
            box-shadow: 0 14px 30px rgba(10, 47, 79, 0.24);
        }

        .brand-mark {
            width: 44px;
            height: 44px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 14px;
            background: rgba(255, 255, 255, 0.12);
            font-size: 1.2rem;
        }
        /* DARK MODE BASE */
body.dark-mode {
    background: #121212 !important;
    color: #ffffff !important;
}

/* TABLE */
.dark-mode .table {
    color: #fff;
    border-color: #444;
     border-radius: 10px;
    overflow: hidden;
}

.dark-mode .table thead {
    background: #1f1f1f !important;
}

.dark-mode .table tbody tr {
    background: #1a1a1a;
}

.dark-mode .table tbody tr:hover {
    background: #222;
}

/* TEXT */
.dark-mode h3 {
    color: #fff;
}

/* BUTTONS */
.dark-mode .btn-primary {
    background: #0d6efd;
}

.dark-mode .btn-success {
    background: #198754;
}

/* BADGES */
.dark-mode .badge.bg-warning {
    background: #ffc107;
    color: #000;
}

.dark-mode .badge.bg-success {
    background: #198754;
}

.dark-mode .badge.bg-danger {
    background: #dc3545;
}

/* NAVBAR stays same (already dark) */
</style>
</head>


<body style="background:#f4f6f9">
      <nav class="navbar navbar-expand-lg navbar-dark navbar-shell py-3">
        <div class="container">
            <div class="d-flex align-items-center gap-3">
                <div class="brand-mark">OH</div>
                <div>
                    <a class="navbar-brand fw-bold mb-0" href="index.php">Order History</a>
                    <div class="small text-white-50">See your past orders</div>
                </div>
            </div>

            <div class="d-flex align-items-center gap-3 text-white mt-3 mt-lg-0">
                <button id="darkModeToggle" class="btn btn-outline-light me-2">
                    🌙
                </button>
                
            </div>
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
                    <th>Order</th>
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
                        <td>
                            <a href="reorder.php?id=<?= $row['order_id'] ?>" class="btn btn-primary btn-sm">
                                Reorder
                            </a>
                        </td>

                    </tr>

                <?php } ?>

            </tbody>

        </table>

    </div>
   
    <script>
const toggleBtn = document.getElementById("darkModeToggle");

// Load saved mode
if (localStorage.getItem("darkMode") === "enabled") {
    document.body.classList.add("dark-mode");
    toggleBtn.textContent = "☀️";
}

toggleBtn.addEventListener("click", () => {
    document.body.classList.toggle("dark-mode");

    if (document.body.classList.contains("dark-mode")) {
        localStorage.setItem("darkMode", "enabled");
        toggleBtn.textContent = "☀️";
    } else {
        localStorage.setItem("darkMode", "disabled");
        toggleBtn.textContent = "🌙";
    }
});
</script>
</body>

</html>