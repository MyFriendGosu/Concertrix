<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once __DIR__ . '/../config/db.php';
$root = "/concert_ticketing_system/"; 

if (!isset($_SESSION['user_id'])) {
    header("Location: " . $root . "auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Fallback logic for database column name mismatch
$userName = $user['fullname'] ?? $user['full_name'] ?? 'Guest User';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile | Concertix</title>
    
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --bg-body: #f8fafc;
            --surface: #ffffff;
            --accent-primary: #6366f1;
            --accent-gradient: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
            --text-main: #0f172a;
            --text-muted: #64748b;
            --glass-border: rgba(0, 0, 0, 0.05);
            --nav-bg: rgba(255, 255, 255, 0.8);
        }

        body.dark {
            --bg-body: #020617;
            --surface: #0f172a;
            --accent-primary: #818cf8;
            --accent-gradient: linear-gradient(135deg, #818cf8 0%, #c084fc 100%);
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --glass-border: rgba(255, 255, 255, 0.1);
            --nav-bg: rgba(2, 6, 23, 0.8);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; transition: background-color 0.3s ease, color 0.3s ease; }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-body);
            color: var(--text-main);
            min-height: 100vh;
        }

        /* --- Fixed Nav Height --- */
        nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 8%;
            background: var(--nav-bg);
            backdrop-filter: blur(12px);
            position: sticky;
            top: 0;
            z-index: 1000;
            border-bottom: 1px solid var(--glass-border);
            height: 80px; 
        }

        .logo { font-weight: 800; font-size: 1.4rem; display: flex; align-items: center; gap: 10px; color: var(--text-main); text-decoration: none; }
        .nav-links { display: flex; align-items: center; gap: 2rem; height: 100%; }
        .nav-links a { text-decoration: none; color: var(--text-muted); font-weight: 600; font-size: 0.85rem; transition: color 0.3s ease; }
        .nav-links a.active { color: var(--accent-primary); }

        .btn-logout {
            background: var(--accent-gradient);
            color: #ffffff !important;
            padding: 8px 20px;
            border-radius: 8px;
            font-weight: 700;
            box-shadow: 0 10px 15px -3px rgba(168, 85, 247, 0.3);
        }

        .theme-switch {
            width: 46px; height: 24px; background: var(--glass-border);
            border-radius: 50px; position: relative; cursor: pointer;
            display: flex; align-items: center; padding: 0 4px; justify-content: space-between;
        }

        .switch-dot {
            position: absolute; width: 18px; height: 18px; background: var(--accent-gradient);
            border-radius: 50%; transition: 0.3s; left: 3px;
        }
        body.dark .switch-dot { transform: translateX(22px); }

        /* --- Reduced Card UI --- */
        .container { padding: 40px 5%; display: flex; justify-content: center; }

        .profile-card {
            background: var(--surface);
            border: 1px solid var(--glass-border);
            border-radius: 24px;
            padding: 30px;
            width: 100%;
            max-width: 480px; /* Reduced width */
            box-shadow: 0 15px 35px rgba(0,0,0,0.05);
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .profile-avatar {
            width: 90px; height: 90px; /* Reduced size */
            background: var(--accent-gradient);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            color: white; font-size: 2.5rem; font-weight: 800;
            margin-bottom: 15px;
        }

        .profile-header h1 { font-size: 1.5rem; font-weight: 800; margin-bottom: 5px; text-align: center; }
        .profile-header p { font-size: 0.85rem; color: var(--text-muted); text-align: center; margin-bottom: 25px; }

        .profile-info { width: 100%; margin-bottom: 20px; }

        .info-field {
            background: var(--bg-body);
            border: 1px solid var(--glass-border);
            padding: 12px 16px;
            border-radius: 12px;
            margin-bottom: 12px;
        }

        .info-field label {
            display: block; font-size: 0.65rem; color: var(--text-muted);
            text-transform: uppercase; font-weight: 800; margin-bottom: 2px;
        }

        .info-field p { font-weight: 600; font-size: 0.9rem; color: var(--text-main); }

        /* --- Update Button --- */
        .update-btn {
            width: 100%;
            padding: 12px;
            background: var(--accent-gradient);
            color: white;
            border: none;
            border-radius: 12px;
            font-weight: 700;
            font-size: 0.9rem;
            cursor: pointer;
            transition: transform 0.2s;
            text-align: center;
            text-decoration: none;
            box-shadow: 0 10px 20px -5px rgba(99, 102, 241, 0.4);
        }

        .update-btn:hover { transform: translateY(-2px); filter: brightness(1.1); }
    </style>
</head>
<body class="dark">

<nav>
    <a href="home.php" class="logo">
        <span class="material-symbols-outlined" style="color: var(--accent-primary)">theater_comedy</span>
        CONCERTIX
    </a>
    <div class="nav-links">
        <a href="home.php">EXPLORE</a>
        <a href="my-tickets.php">MY TICKETS</a>
        <a href="profile.php" class="active">PROFILE</a>
        <div class="theme-switch" id="themeToggle">
            <span class="material-symbols-outlined" style="font-size: 12px;">light_mode</span>
            <span class="material-symbols-outlined" style="font-size: 12px;">dark_mode</span>
            <div class="switch-dot"></div>
        </div>
        <a href="<?php echo $root; ?>auth/logout.php" class="btn-logout">LOGOUT</a>
    </div>
</nav>

<main class="container">
    <div class="profile-card">
        <div class="profile-avatar">
            <?php echo strtoupper(substr($userName, 0, 1)); ?>
        </div>
        
        <div class="profile-header">
            <h1><?php echo htmlspecialchars($userName); ?></h1>
            <p>Member since <?php echo date('M Y', strtotime($user['created_at'] ?? 'now')); ?></p>
        </div>

        <div class="profile-info">
            <div class="info-field">
                <label>Email Address</label>
                <p><?php echo htmlspecialchars($user['email'] ?? 'Not set'); ?></p>
            </div>

            <div class="info-field">
                <label>Account Role</label>
                <p style="text-transform: capitalize;"><?php echo htmlspecialchars($user['role'] ?? 'Standard User'); ?></p>
            </div>
        </div>

        <a href="update-profile.php" class="update-btn">Update Profile Information</a>
    </div>
</main>

<script>
    const themeToggle = document.getElementById('themeToggle');
    const body = document.body;

    if (localStorage.getItem('theme') === 'light') {
        body.classList.remove('dark');
    }

    themeToggle.addEventListener('click', () => {
        body.classList.toggle('dark');
        localStorage.setItem('theme', body.classList.contains('dark') ? 'dark' : 'light');
    });
</script>

</body>
</html>