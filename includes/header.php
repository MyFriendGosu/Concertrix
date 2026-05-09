<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/** 
 * Absolute path definition to prevent folder stacking 
 * Matches project folder: concert_ticketing_system
 */
$root = "/concert_ticketing_system/"; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Concertix</title>
    <!-- CSS link using root path for consistent styling -->
    <link rel="stylesheet" href="<?php echo $root; ?>assets/css/style.css">
</head>
<body>

<nav class="navbar">
    <div class="logo">
        <a href="<?php echo $root; ?>index.php">CONCERTIX</a>
    </div>

    <div class="nav-links">
        <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'admin') : ?>
            <!-- Admin-Only Navigation: Clean management view -->
            <div class="admin-nav-group">
                <a href="<?php echo $root; ?>admin/dashboard.php" class="active">ADMIN DASHBOARD</a>
                <a href="<?php echo $root; ?>admin/add-concert.php">+ ADD CONCERT</a>
                <a href="<?php echo $root; ?>admin/add-seat-zones.php" class="admin-btn-special">+ ADD SEAT ZONE</a>
            </div>
            <!-- Logout for Admin -->
            <a href="<?php echo $root; ?>auth/logout.php" class="logout-btn">LOGOUT</a>

        <?php elseif (isset($_SESSION['user_id'])) : ?>
            <!-- Regular User Navigation -->
            <a href="<?php echo $root; ?>index.php">HOME</a>
            <a href="<?php echo $root; ?>pages/home.php">CONCERTS</a>
            <a href="<?php echo $root; ?>pages/about.php">ABOUT</a>
            <a href="<?php echo $root; ?>pages/my-tickets.php">MY TICKETS</a>
            <a href="<?php echo $root; ?>pages/contact.php">CONTACT</a>
            <a href="<?php echo $root; ?>auth/logout.php" class="logout-btn">LOGOUT</a>

        <?php else : ?>
            <!-- Guest Navigation -->
            <a href="<?php echo $root; ?>index.php">HOME</a>
            <a href="<?php echo $root; ?>pages/home.php">CONCERTS</a>
            <a href="<?php echo $root; ?>pages/about.php">ABOUT</a>
            <a href="<?php echo $root; ?>pages/contact.php">CONTACT</a>
            <a href="<?php echo $root; ?>auth/login.php" class="auth-btn login">LOGIN</a>
            <a href="<?php echo $root; ?>auth/register.php" class="auth-btn register">REGISTER</a>
        <?php endif; ?>
    </div>
</nav>