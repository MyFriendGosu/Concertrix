<?php
include '../config/db.php';
include '../includes/header.php';

$concert_id = $_GET['id'];

$concert = $conn->query(
    "SELECT * FROM concerts WHERE id='$concert_id'"
)->fetch_assoc();

$zones = $conn->query(
    "SELECT * FROM seat_zones WHERE concert_id='$concert_id'"
);
?>

<h1><?php echo htmlspecialchars($concert['concert_name']); ?></h1>
<p>Date: <?php echo $concert['concert_date']; ?></p>
<p>Time: <?php echo $concert['concert_time']; ?></p>
<p>Venue: <?php echo htmlspecialchars($concert['venue']); ?></p>

<h2>Seat Zones</h2>

<ul>
<?php while ($z = $zones->fetch_assoc()) { ?>
    <li>
        <?php echo $z['zone_name']; ?> —
        ₱<?php echo number_format($z['price'],2); ?> —
        Available: <?php echo $z['available_slots']; ?>
    </li>
<?php } ?>
</ul>

<a class="buy-btn" href="buy-ticket.php?id=<?php echo $concert_id; ?>">
    Buy Ticket
</a>

<?php include '../includes/footer.php'; ?>