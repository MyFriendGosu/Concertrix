<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/** 
 * Absolute path definition to prevent folder stacking 
 */
$root = "/concert_ticketing_system/"; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Concertix | Premium Live Experiences</title>
    
    <!-- Material Symbols (Rounded) & Plus Jakarta Sans -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo $root; ?>assets/css/style.css">

    <style>
        :root {
            /* Colors matching image reference and developer profile */
            --nav-bg: #02040a; 
            --nav-border: rgba(255, 255, 255, 0.06);
            --text-main: #ffffff;
            --text-dim: #94a3b8;
            --brand-gradient: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
            --nav-height: 80px;
        }

        body {
            margin: 0;
            background-color: var(--nav-bg);
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: var(--text-main);
        }

        .concertix-header {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: var(--nav-height);
            background: var(--nav-bg);
            border-bottom: 1px solid var(--nav-border);
            display: flex;
            align-items: center;
            z-index: 2000;
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }

        .nav-container {
            max-width: 1400px;
            width: 92%;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        /* Brand Logo Section */
        .brand-link {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            transition: transform 0.2s ease;
        }

        .brand-link:hover { transform: scale(1.02); }

        .brand-icon {
            color: #818cf8; 
            font-size: 32px;
            font-variation-settings: 'FILL' 1;
        }

        .brand-name {
            color: var(--text-main);
            font-weight: 800;
            font-size: 1.6rem;
            letter-spacing: -1.5px;
            text-transform: uppercase;
        }

        /* Navigation Menu */
        .nav-links {
            display: flex;
            gap: 30px;
            align-items: center;
        }

        .nav-item {
            color: var(--text-dim);
            text-decoration: none;
            font-weight: 700;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
            transition: color 0.3s ease;
            text-transform: uppercase;
        }

        .nav-item:hover, .nav-item.active {
            color: var(--text-main);
        }

        /* Right Actions (Toggle & Auth) */
        .header-actions {
            display: flex;
            align-items: center;
            gap: 24px;
        }

        /* Visual Theme Toggle Switch */
        .theme-switch-ui {
            display: flex;
            align-items: center;
            background: #0f172a;
            padding: 4px 8px;
            border-radius: 20px;
            gap: 8px;
            border: 1px solid var(--nav-border);
        }

        .switch-track {
            width: 42px;
            height: 22px;
            background: #1e293b;
            border-radius: 15px;
            position: relative;
        }

        .switch-knob {
            width: 18px;
            height: 18px;
            background: var(--brand-gradient);
            border-radius: 50%;
            position: absolute;
            right: 2px;
            top: 2px;
            box-shadow: 0 0 10px rgba(99, 102, 241, 0.5);
        }

        /* Authentication UI */
        .auth-link {
            text-decoration: none;
            font-weight: 700;
            font-size: 0.85rem;
            transition: all 0.3s ease;
        }

        .login-btn { color: var(--text-dim); }
        .login-btn:hover { color: var(--text-main); }

        .register-btn {
            background: var(--brand-gradient);
            color: white;
            padding: 12px 28px;
            border-radius: 12px;
            box-shadow: 0 10px 20px -5px rgba(99, 102, 241, 0.4);
        }

        .register-btn:hover {
            transform: translateY(-2px);
            filter: brightness(1.1);
        }

        .logout-pill {
            color: #ef4444;
            border: 1px solid rgba(239, 68, 68, 0.2);
            padding: 8px 18px;
            border-radius: 10px;
        }

        .logout-pill:hover {
            background: rgba(239, 68, 68, 0.1);
        }

        .header-spacer { height: var(--nav-height); }
    </style>
</head>
<body>

<header class="concertix-header">
    <div class="nav-container">
        <!-- Logo -->
        <a href="<?php echo $root; ?>index.php" class="brand-link">
            <span class="material-symbols-rounded brand-icon">theater_comedy</span>
            <span class="brand-name">CONCERTIX</span>
        </a>

        <!-- Dynamic Navigation Links based on Role -->
        <nav class="nav-links">
            <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'admin') : ?>
                <a href="<?php echo $root; ?>admin/dashboard.php" class="nav-item active">DASHBOARD</a>
                <a href="<?php echo $root; ?>admin/add-concert.php" class="nav-item">+ CONCERT</a>
                <a href="<?php echo $root; ?>admin/add-seat-zones.php" class="nav-item">SEAT ZONES</a>
            
            <?php else : ?>
                <a href="<?php echo $root; ?>index.php" class="nav-item">HOME</a>
                <a href="<?php echo $root; ?>pages/home.php" class="nav-item">CONCERTS</a>
                <a href="<?php echo $root; ?>pages/about.php" class="nav-item">ABOUT</a>
                <?php if (isset($_SESSION['user_id'])) : ?>
                    <a href="<?php echo $root; ?>pages/my-tickets.php" class="nav-item">MY TICKETS</a>
                <?php endif; ?>
                <a href="<?php echo $root; ?>pages/contact.php" class="nav-item">CONTACT</a>
            <?php endif; ?>
        </nav>

        <!-- Right Side Actions -->
        <div class="header-actions">
            <!-- Theme UI Switch -->
            <div class="theme-switch-ui">
                <span class="material-symbols-rounded" style="font-size: 14px; color: #64748b;">light_mode</span>
                <div class="switch-track">
                    <div class="switch-knob"></div>
                </div>
            </div>

            <?php if (isset($_SESSION['user_id'])) : ?>
                <a href="<?php echo $root; ?>auth/logout.php" class="auth-link logout-pill">LOGOUT</a>
            <?php else : ?>
                <a href="<?php echo $root; ?>auth/login.php" class="auth-link login-btn">LOGIN</a>
                <a href="<?php echo $root; ?>auth/register.php" class="auth-link register-btn">REGISTER</a>
            <?php endif; ?>
        </div>
    </div>
</header>

<div class="header-spacer"></div>