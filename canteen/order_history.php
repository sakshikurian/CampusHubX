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
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta charset="UTF-8">

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
            --dark-surface: #172633;
            --dark-surface-2: #203442;
            --dark-text: #f5fbff;
            --dark-muted: #b8c7d3;
            --dark-accent: #ffb703;
            --dark-border: rgba(255, 255, 255, 0.14);
        }

        body {
            background: #f4f6f9;
            min-height: 100vh;
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
        .orders-panel {
            border: 1px solid var(--border-soft);
            border-radius: 18px;
            overflow: hidden;
            box-shadow: var(--shadow-soft);
            background: rgba(255, 255, 255, 0.92);
        }

        body.dark-mode {
            background:
                radial-gradient(circle at top left, rgba(255, 183, 3, 0.14), transparent 28%),
                linear-gradient(145deg, #101820 0%, #15222d 48%, #0d151c 100%) !important;
            color: var(--dark-text) !important;
        }

        body.dark-mode .navbar-shell {
            background: linear-gradient(120deg, #08111a, #16384c);
            box-shadow: 0 16px 38px rgba(0, 0, 0, 0.42);
        }

        body.dark-mode .orders-panel {
            background: linear-gradient(180deg, var(--dark-surface), var(--dark-surface-2));
            border-color: var(--dark-border);
        }

        body.dark-mode .table {
            --bs-table-bg: transparent;
            --bs-table-color: var(--dark-text);
            --bs-table-border-color: var(--dark-border);
            color: var(--dark-text);
        }

        body.dark-mode h3 {
            color: var(--dark-text);
        }

        body.dark-mode .badge.bg-warning {
            background: var(--dark-accent) !important;
            color: #17202a !important;
        }

        @media (max-width: 768px) {
            .navbar-shell .container {
                align-items: flex-start;
                gap: 12px;
            }

            .brand-mark {
                width: 40px;
                height: 40px;
                border-radius: 10px;
                font-size: 0.9rem;
            }

            .navbar-brand {
                font-size: 1rem;
            }

            .orders-shell {
                margin-top: 1.5rem !important;
            }

            .orders-table thead {
                display: none;
            }

            .orders-table,
            .orders-table tbody,
            .orders-table tr,
            .orders-table td {
                display: block;
                width: 100%;
            }

            .orders-table tr {
                border: 1px solid var(--border-soft);
                border-radius: 14px;
                margin: 12px;
                padding: 12px;
                background: rgba(255, 255, 255, 0.74);
            }

            body.dark-mode .orders-table tr {
                background: rgba(16, 24, 32, 0.7);
                border-color: var(--dark-border);
            }

            .orders-table td {
                border: 0;
                text-align: left !important;
                padding: 8px 0;
            }

            .orders-table td::before {
                content: attr(data-label);
                display: block;
                font-size: 0.78rem;
                font-weight: 800;
                color: var(--text-muted);
                text-transform: uppercase;
                margin-bottom: 4px;
            }

            body.dark-mode .orders-table td::before {
                color: var(--dark-muted);
            }

            .orders-table .btn {
                width: 100%;
            }
        }
</style>
</head>


<body>
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
    <div class="container mt-5 orders-shell">

        <h3 class="mb-4"> My Order History</h3>

        <div class="orders-panel table-responsive">
        <table class="table table-bordered text-center align-middle mb-0 orders-table">

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

                        <td data-label="Token No">Token #<?= $row['token_no'] ?></td>
                        <td data-label="Amount">₹<?= $row['amount'] ?></td>

                        <td data-label="Coupon">

                            <?php if ($row['status'] == "approved") { ?>

                                <a href="generate_bill.php?id=<?= $row['order_id'] ?>" class="btn btn-success btn-sm">
                                    Download Coupon
                                </a>

                            <?php } else { ?>

                                —

                            <?php } ?>

                        </td>

                        <td data-label="Status">

                            <?php if ($row['status'] == "pending") { ?>
                                <span class="badge bg-warning text-dark">Pending</span>

                            <?php } elseif ($row['status'] == "approved") { ?>
                                <span class="badge bg-success">Approved</span>

                            <?php } else { ?>
                                <span class="badge bg-danger">Rejected</span>
                            <?php } ?>

                        </td>

                        <td data-label="Date"><?= $row['order_date'] ?></td>
                        <td data-label="Order">
                            <a href="reorder.php?id=<?= $row['order_id'] ?>" class="btn btn-primary btn-sm">
                                Reorder
                            </a>
                        </td>

                    </tr>

                <?php } ?>

            </tbody>

        </table>
        </div>

    </div>
   
    <script>
const darkModeToggleBtn = document.getElementById("darkModeToggle");
if (localStorage.getItem("darkMode") === "enabled") {
    document.body.classList.add("dark-mode");
}

if (darkModeToggleBtn) {
    darkModeToggleBtn.textContent = document.body.classList.contains("dark-mode") ? "☀️" : "🌙";
    darkModeToggleBtn.addEventListener("click", () => {
        document.body.classList.toggle("dark-mode");
        const enabled = document.body.classList.contains("dark-mode");
        localStorage.setItem("darkMode", enabled ? "enabled" : "disabled");
        darkModeToggleBtn.textContent = enabled ? "☀️" : "🌙";
    });
}
</script>
</body>

</html>
