<?php
include '../includes/auth-check.php';
include '../config/db.php';
include '../includes/header.php';

$concert_id = $_GET['id'];

$zones = $conn->query(
    "SELECT * FROM seat_zones WHERE concert_id='$concert_id'"
);

if (isset($_POST['buy'])) {

    $seat_zone_id = $_POST['seat_zone_id'];
    $quantity = $_POST['quantity'];
    $payment_reference = $_POST['payment_reference'];

    if ($quantity > 5) {
        die("Maximum of 5 tickets only.");
    }

    if (!ctype_digit($payment_reference) || strlen($payment_reference) != 8) {
        die("Payment reference must be 8 digits.");
    }

    $zone = $conn->query(
        "SELECT * FROM seat_zones WHERE id='$seat_zone_id'"
    )->fetch_assoc();

    if ($zone['available_slots'] < $quantity) {
        die("Not enough available seats.");
    }

    $total = $zone['price'] * $quantity;

    $conn->query(
        "INSERT INTO bookings
        (user_id, concert_id, seat_zone_id, quantity, payment_reference, total_price)
        VALUES
        ('{$_SESSION['user_id']}','$concert_id','$seat_zone_id','$quantity','$payment_reference','$total')"
    );

    $conn->query(
        "UPDATE seat_zones
         SET available_slots = available_slots - $quantity
         WHERE id='$seat_zone_id'"
    );

    echo "<p style='color:green;'>Ticket purchased successfully!</p>";
}
?>

<h2>Buy Ticket</h2>

<form method="POST">
    <select name="seat_zone_id" required>
        <?php while ($zone = $zones->fetch_assoc()) { ?>
            <option value="<?php echo $zone['id']; ?>">
                <?php echo $zone['zone_name']; ?> – ₱<?php echo $zone['price']; ?>
            </option>
        <?php } ?>
    </select>

    <input type="number" name="quantity" placeholder="Quantity" required>
    <input type="text" name="payment_reference" maxlength="8"
           placeholder="8-digit Transaction No." required>

    <button type="submit" name="buy">Purchase</button>
</form>

<?php include '../includes/footer.php'; ?>