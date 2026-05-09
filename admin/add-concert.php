<?php
include '../config/db.php';
include 'admin-auth.php';

if (isset($_POST['add'])) {

    $concert_name = $_POST['concert_name'];
    $concert_date = $_POST['concert_date'];
    $concert_time = $_POST['concert_time'];
    $venue = $_POST['venue'];

    $stmt = $conn->prepare(
        "INSERT INTO concerts (concert_name, concert_date, concert_time, venue)
         VALUES (?, ?, ?, ?)"
    );

    $stmt->bind_param("ssss",
        $concert_name,
        $concert_date,
        $concert_time,
        $venue
    );

    if ($stmt->execute()) {
        echo "<p style='color:green;'>Concert added successfully!</p>";
    }
}
?>

<h2>Add Concert</h2>

<form method="POST">
    <input type="text" name="concert_name" placeholder="Concert Name" required>
    <input type="date" name="concert_date" required>
    <input type="time" name="concert_time" required>
    <input type="text" name="venue" placeholder="Venue" required>

    <button type="submit" name="add">Add Concert</button>
</form>