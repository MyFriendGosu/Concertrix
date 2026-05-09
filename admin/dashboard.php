<?php
session_start();
// Step up one level to find config from the admin folder
include_once __DIR__ . '/../config/db.php';

// Define root for absolute pathing
$root = "/concert_ticketing_system/";

// Security: Ensure only admins can access this page
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: " . $root . "index.php");
    exit;
}

include_once __DIR__ . '/../includes/header.php';

// Your SQL for the summary report
$sql = "SELECT users.fullname, concerts.concert_name, bookings.quantity, 
               bookings.total_price, bookings.payment_reference 
        FROM bookings 
        JOIN users ON bookings.user_id = users.id 
        JOIN concerts ON bookings.concert_id = concerts.id 
        ORDER BY bookings.id DESC"; 

$result = $conn->query($sql);
?>

<div class="admin-container">
    <header class="admin-header">
        <h1>Admin Dashboard</h1>
        <p>Summary report of all ticket buyers</p>
    </header>
    
    <div class="table-wrapper">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Buyer</th>
                    <th>Concert</th>
                    <th>Tickets</th>
                    <th>Total Price</th>
                    <th>Transaction #</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($row['fullname']); ?></strong></td>
                            <td><?php echo htmlspecialchars($row['concert_name']); ?></td>
                            <td><?php echo $row['quantity']; ?></td>
                            <td>P<?php echo number_format($row['total_price'], 2); ?></td>
                            <td class="ref-code">#<?php echo htmlspecialchars($row['payment_reference']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="no-data">No ticket sales recorded yet.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>