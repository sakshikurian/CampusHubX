<?php
include "../includes/db.php";

$search = $_GET['q'] ?? '';

$sql = "SELECT * FROM menu WHERE item_name LIKE '%$search%'";
$result = $conn->query($sql);

while ($row = $result->fetch_assoc()) {
    ?>
    <div class="col-md-4 mb-3">
        <div class="card shadow-sm text-center">
            <div class="card-body">

                <h5>
                    <?= $row['item_name'] ?>
                </h5>
                <p>₹
                    <?= $row['price'] ?>
                </p>

                <form action="place_order.php" method="POST">
                    <input type="hidden" name="item_id" value="<?= $row['item_id'] ?>">
                    <input type="hidden" name="price" value="<?= $row['price'] ?>">

                    Qty:
                    <input type="number" id="qty<?= $row['item_id'] ?>" name="qty" value="1" min="1">

                    <p>Total: ₹
                        <?= $row['price'] ?>
                    </p>

                    <button class="btn btn-success">Order</button>
                </form>

            </div>
        </div>
    </div>
<?php } ?>