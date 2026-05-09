<?php
include '../includes/auth-check.php';
include '../config/db.php';
include '../includes/header.php';

$user_id = $_SESSION['user_id'];

$result = $conn->query(
    "SELECT bookings.*, concerts.concert_name
     FROM bookings
     JOIN concerts ON bookings.concert_id = concerts.id
     WHERE bookings.user_id='$user_id'
     ORDER BY bookings.created_at DESC"
);
?>

<h1>My Tickets</h1>

<?php while ($t = $result->fetch_assoc()) { ?>
<div class="concert-card">
    <div class="card-content">
        <h2><?php echo $t['concert_name']; ?></h2>
        <p>Quantity: <?php echo $t['quantity']; ?></p>
        <p>Total: ₱<?php echo number_format($t['total_price'],2); ?></p>
        <p>Transaction #: <?php echo $t['payment_reference']; ?></p>
    </div>
</div>
<?php } ?>

<?php include '../includes/footer.php'; ?>