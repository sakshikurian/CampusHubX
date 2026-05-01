<?php
require_once '../includes/session.php';

include "../includes/db.php";
$total = $_GET['amount'] ?? 0;


if (isset($_POST['submit_payment'])) {

    $name = $_POST['name'];
    $user_id = $_SESSION['user_id'] ?? 1; // change if you store login id

    $file = $_FILES['payment_ss'];

    $filename = time() . "_" . $file['name'];

    move_uploaded_file($file['tmp_name'], $_SERVER['DOCUMENT_ROOT'] . "/images/" . $filename);

    /* GET TODAY TOKEN NUMBER */

    $res = $conn->query("
    SELECT COUNT(*) as total 
    FROM orders 
    WHERE DATE(order_date) = CURDATE()
    ");

    $row = $res->fetch_assoc();

    $token_no = $row['total'] + 1;
    /* INSERT ORDER */

    $sql = "INSERT INTO orders 
(user_id,name,amount,payment_ss,status,token_no)
VALUES 
('$user_id','$name','$total','$filename','pending','$token_no')";

    if (!$conn->query($sql)) {
        echo "Database Error: " . $conn->error;
    } else {
        echo "Order saved successfully";
    }
    $order_id = $conn->insert_id;

    /* SAVE CART ITEMS */

    if (isset($_SESSION['cart'])) {

        foreach ($_SESSION['cart'] as $item) {

            $item_name = $item['item'];
            $qty = $item['qty'];

            $conn->query("
            INSERT INTO order_items (order_id,item_name,qty)
            VALUES ('$order_id','$item_name','$qty')
            ");

        }
    }
    /* CLEAR CART AFTER ORDER */

    unset($_SESSION['cart']);

    header("Location: index.php");
    exit();

}
?>

<!DOCTYPE html>
<html>

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta charset="UTF-8">
    <title>Payment</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --brand: #0f4c81;
            --brand-dark: #0a2f4f;
            --text-main: #16324f;
            --border-soft: rgba(15, 76, 129, 0.12);
            --shadow-soft: 0 18px 40px rgba(15, 76, 129, 0.12);
            --dark-surface: #172633;
            --dark-surface-2: #203442;
            --dark-text: #f5fbff;
            --dark-accent: #ffb703;
            --dark-border: rgba(255, 255, 255, 0.14);
        }

        body {
            background: #f4f6f9;
            color: var(--text-main);
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
            font-weight: 800;
        }

        .payment-shell {
            max-width: 680px;
        }

        .payment-card {
            border: 1px solid var(--border-soft);
            border-radius: 18px;
            box-shadow: var(--shadow-soft);
            overflow: hidden;
        }

        .qr-image {
            width: min(250px, 72vw);
            height: auto;
            border-radius: 16px;
            border: 1px solid var(--border-soft);
        }

        .qr-wrap {
            display: flex;
            justify-content: center;
        }

        .amount-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 10px 18px;
            border-radius: 999px;
            background: #e9f8f1;
            color: #147a52;
            font-weight: 800;
        }

        body.dark-mode {
            background:
                radial-gradient(circle at top left, rgba(255, 183, 3, 0.14), transparent 28%),
                linear-gradient(145deg, #101820 0%, #15222d 48%, #0d151c 100%) !important;
            color: var(--dark-text);
        }

        body.dark-mode .navbar-shell {
            background: linear-gradient(120deg, #08111a, #16384c);
            box-shadow: 0 16px 38px rgba(0, 0, 0, 0.42);
        }

        body.dark-mode .payment-card {
            background: linear-gradient(180deg, var(--dark-surface), var(--dark-surface-2));
            color: var(--dark-text);
            border-color: var(--dark-border);
        }

        body.dark-mode .form-control {
            background: #f7fbff;
            color: #10202b;
            border-color: rgba(255, 183, 3, 0.35);
        }

        body.dark-mode .amount-pill {
            background: rgba(255, 183, 3, 0.16);
            color: var(--dark-accent);
            border: 1px solid rgba(255, 183, 3, 0.35);
        }

        body.dark-mode .qr-image {
            border-color: rgba(255, 183, 3, 0.35);
        }

        body.dark-mode hr {
            border-color: var(--dark-border);
            opacity: 1;
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

            .payment-shell {
                margin-top: 1.5rem !important;
                padding-left: 14px;
                padding-right: 14px;
            }

            .payment-card .card-body {
                padding: 20px !important;
            }

            .payment-card h3 {
                font-size: 1.35rem;
            }

            .payment-card .btn {
                min-height: 48px;
            }
        }
    </style>
</head>

<body>
  <nav class="navbar navbar-expand-lg navbar-dark navbar-shell py-3">
        <div class="container">
            <div class="d-flex align-items-center gap-3">
                <div class="brand-mark">PP</div>
                <div>
                    <a class="navbar-brand fw-bold mb-0" href="index.php">Proceed Payment</a>
                    <div class="small text-white-50">Scan QR, Pay and Upload Screenshot</div>
                </div>
            </div>

            <div class="d-flex align-items-center gap-3 text-white mt-3 mt-lg-0">
                <button id="darkModeToggle" class="btn btn-outline-light me-2">
                    🌙
                </button>
                
            </div>
        </div>
    </nav>
    <div class="container payment-shell mt-5">

        <div class="card payment-card text-center">
            <div class="card-body p-4 p-md-5">

            <h3 class="mb-4">Scan QR & Pay</h3>
            <div class="qr-wrap mb-3">
                <img src="QR.jpeg" class="qr-image" alt="Payment QR code">
            </div>

            <div class="amount-pill mb-4 mt-2">
                Amount: ₹<?= number_format($total, 2) ?>
            </div>

            <hr>

            <form method="POST" enctype="multipart/form-data">

                <div class="mb-3 text-start">

                    <label class="form-label">Your Name</label>

                    <input type="text" name="name" class="form-control" required>

                </div>

                <div class="mb-3 text-start">

                    <label class="form-label">Upload Payment Screenshot</label>

                    <input type="file" name="payment_ss" class="form-control" required>

                </div>

                <button type="submit" name="submit_payment" class="btn btn-success w-100">
                    Submit Payment
                </button>

            </form>
            </div>

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
