<?php
include '../includes/auth-check.php';
include '../config/db.php';
include '../includes/header.php';

$user_id = $_SESSION['user_id'];

$user = $conn->query(
    "SELECT * FROM users WHERE id='$user_id'"
)->fetch_assoc();

if (isset($_POST['update'])) {
    $fullname = $_POST['fullname'];
    $email = $_POST['email'];

    $conn->query(
        "UPDATE users
         SET fullname='$fullname', email='$email'
         WHERE id='$user_id'"
    );

    echo "<p style='color:green;'>Profile updated!</p>";
}
?>

<h1>My Profile</h1>

<form method="POST">
    <input type="text" name="fullname"
           value="<?php echo htmlspecialchars($user['fullname']); ?>" required>

    <input type="email" name="email"
           value="<?php echo htmlspecialchars($user['email']); ?>" required>

    <button type="submit" name="update">Update Profile</button>
</form>

<?php include '../includes/footer.php'; ?>