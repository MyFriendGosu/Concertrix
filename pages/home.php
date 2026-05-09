<?php
// pages/home.php
session_start();

// Define root if not already defined (essential for direct access)
$root = "/concert_ticketing_system/";

// Include DB connection: stepping up from 'pages/' to find 'config/'
if (!isset($conn)) {
    include_once __DIR__ . '/../config/db.php';
}

// Include Header: stepping up from 'pages/' to find 'includes/'
include_once __DIR__ . '/../includes/header.php';

$result = $conn->query("SELECT * FROM concerts");
?>

<h1 class="section-title">Available Concerts</h1>

<div class="concert-grid">
<?php while ($row = $result->fetch_assoc()) { ?>
    <div class="concert-card">
        <?php if (!empty($row['image'])) { ?>
            <!-- $root ensures the path is localhost/concert_ticketing_system/assets/... -->
            <img src="<?php echo $root; ?>assets/images/concerts/<?php echo $row['image']; ?>" class="concert-image">
        <?php } ?>

        <div class="card-content">
            <h2><?php echo htmlspecialchars($row['concert_name']); ?></h2>
            <p>Date: <?php echo $row['concert_date']; ?></p>
            <p>Venue: <?php echo htmlspecialchars($row['venue']); ?></p>

            <a class="buy-btn" href="<?php echo $root; ?>pages/concert-details.php?id=<?php echo $row['id']; ?>">
                View Details
            </a>
        </div>
    </div>
<?php } ?>
</div>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>
