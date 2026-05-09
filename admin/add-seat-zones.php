<?php
include '../config/db.php';
include 'admin-auth.php';

$concerts = $conn->query("SELECT * FROM concerts");

if (isset($_POST['add_zone'])) {

    $concert_id = $_POST['concert_id'];
    $zone_name = $_POST['zone_name'];
    $price = $_POST['price'];
    $slots = $_POST['slots'];

    $stmt = $conn->prepare(
        "INSERT INTO seat_zones (concert_id, zone_name, price, available_slots)
         VALUES (?, ?, ?, ?)"
    );

    $stmt->bind_param("isdi",
        $concert_id,
        $zone_name,
        $price,
        $slots
    );

    if ($stmt->execute()) {
        echo "<p style='color:green;'>Seat zone added!</p>";
    }
}
?>

<h2>Add Seat Zone</h2>

<form method="POST">
    <select name="concert_id" required>
        <option value="">Select Concert</option>
        <?php while ($c = $concerts->fetch_assoc()) { ?>
            <option value="<?= $c['id'] ?>">
                <?= htmlspecialchars($c['concert_name']) ?>
            </option>
        <?php } ?>
    </select>

    <input type="text" name="zone_name" placeholder="Zone Name (VIP, Upper Box)" required>
    <input type="number" name="price" placeholder="Price" required>
    <input type="number" name="slots" placeholder="Available Seats" required>

    <button type="submit" name="add_zone">Add Seat Zone</button>
</form>