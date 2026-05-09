<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once __DIR__ . '/../config/db.php';
$root = "/concert_ticketing_system/"; 

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: " . $root . "auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch user's bookings with joined concert data
$query = "
    SELECT b.*, c.concert_name, c.concert_date, c.image 
    FROM bookings b
    JOIN concerts c ON b.concert_id = c.id 
    WHERE b.user_id = ? 
    ORDER BY b.created_at DESC
";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Tickets | Concertix</title>
    
    <!-- External Assets -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        /* --- Unified Design System --- */
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
        }

        /* --- Global Navigation Fix --- */
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
            height: 80px; /* Locked height for consistency */
        }

        .logo { font-weight: 800; font-size: 1.4rem; display: flex; align-items: center; gap: 10px; color: var(--text-main); text-decoration: none; }
        
        .nav-links { display: flex; align-items: center; gap: 2.5rem; height: 100%; }
        
        .nav-links a { 
            text-decoration: none; 
            color: var(--text-muted); 
            font-weight: 600; 
            font-size: 0.85rem; 
            display: flex;
            align-items: center;
            transition: color 0.3s ease;
        }
        
        .nav-links a:hover, .nav-links a.active { color: var(--accent-primary); }

        .btn-logout {
            background: var(--accent-gradient);
            color: #ffffff !important;
            padding: 10px 24px;
            border-radius: 10px;
            font-weight: 700;
            box-shadow: 0 10px 15px -3px rgba(168, 85, 247, 0.3);
            margin-left: 10px;
        }

        .theme-switch {
            width: 50px; height: 26px; background: var(--glass-border);
            border-radius: 50px; position: relative; cursor: pointer;
            display: flex; align-items: center; padding: 0 5px; justify-content: space-between;
            margin-left: 10px;
            flex-shrink: 0;
        }

        .switch-dot {
            position: absolute; width: 18px; height: 18px; background: var(--accent-gradient);
            border-radius: 50%; transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1); left: 4px;
        }
        body.dark .switch-dot { transform: translateX(24px); }

        /* --- Page Layout --- */
        .container { padding: 60px 8%; }

        .header-section { margin-bottom: 40px; }
        .header-section h1 { font-size: 2.5rem; font-weight: 800; letter-spacing: -1px; }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 25px;
        }

        /* --- Ticket UI --- */
        .ticket-card {
            background: var(--surface);
            border: 1px solid var(--glass-border);
            border-radius: 24px;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            position: relative;
        }

        .ticket-visual {
            height: 120px;
            width: 100%;
            object-fit: cover;
            filter: brightness(0.7);
        }

        .ticket-body {
            padding: 25px;
            position: relative;
        }

        .ticket-divider {
            border-top: 2px dashed var(--glass-border);
            margin: 20px 0;
            position: relative;
        }

        .ticket-divider::before, .ticket-divider::after {
            content: '';
            position: absolute;
            width: 20px; height: 20px;
            background: var(--bg-body);
            border-radius: 50%;
            top: -11px;
        }
        .ticket-divider::before { left: -36px; }
        .ticket-divider::after { right: -36px; }

        .concert-title { font-size: 1.25rem; font-weight: 800; margin-bottom: 5px; }
        
        .info-row { display: flex; justify-content: space-between; margin-bottom: 10px; }
        .info-group label { display: block; font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700; margin-bottom: 2px; }
        .info-group span { font-weight: 700; font-size: 0.9rem; }

        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            background: rgba(34, 197, 94, 0.1);
            color: #22c55e;
            border-radius: 50px;
            font-size: 0.7rem;
            font-weight: 800;
            text-transform: uppercase;
            margin-bottom: 15px;
        }
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
        <a href="my-tickets.php" class="active">MY TICKETS</a>
        <a href="profile.php">PROFILE</a>
        
        <a href="<?php echo $root; ?>auth/logout.php" class="btn-logout">LOGOUT</a>

        <div class="theme-switch" id="themeToggle">
            <span class="material-symbols-outlined" style="font-size: 14px;">light_mode</span>
            <span class="material-symbols-outlined" style="font-size: 14px;">dark_mode</span>
            <div class="switch-dot"></div>
        </div>
    </div>
</nav>

<main class="container">
    <div class="header-section">
        <h1>Your Tickets</h1>
        <p style="color: var(--text-muted);">Manage your bookings and view your event entrance codes.</p>
    </div>

    <div class="grid">
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): 
                $img_path = !empty($row['image']) ? "assets/images/concerts/".$row['image'] : "assets/images/Concert.png";
            ?>
                <div class="ticket-card">
                    <img src="<?php echo $root . $img_path; ?>" class="ticket-visual" alt="Concert Cover">
                    
                    <div class="ticket-body">
                        <div class="status-badge">Confirmed</div>
                        <h2 class="concert-title"><?php echo htmlspecialchars($row['concert_name']); ?></h2>
                        <p style="font-size: 0.85rem; color: var(--text-muted); font-weight: 600;">
                            <?php echo date('F d, Y', strtotime($row['concert_date'])); ?>
                        </p>

                        <div class="ticket-divider"></div>

                        <div class="info-row">
                            <div class="info-group">
                                <label>Quantity</label>
                                <span><?php echo $row['quantity']; ?> Tickets</span>
                            </div>
                            <div class="info-group" style="text-align: right;">
                                <label>Total Price</label>
                                <span style="color: var(--accent-primary);">₱<?php echo number_format($row['total_price'], 2); ?></span>
                            </div>
                        </div>

                        <div class="info-group" style="margin-top: 15px;">
                            <label>Payment Reference</label>
                            <span style="font-family: monospace; letter-spacing: 1px; font-size: 0.8rem;">
                                <?php echo htmlspecialchars($row['payment_reference']); ?>
                            </span>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div style="grid-column: 1/-1; text-align: center; padding: 100px 0;">
                <span class="material-symbols-outlined" style="font-size: 4rem; color: var(--glass-border);">confirmation_number</span>
                <p style="color: var(--text-muted); margin-top: 15px;">You don't have any bookings yet.</p>
                <a href="home.php" style="color: var(--accent-primary); text-decoration: none; font-weight: 700; margin-top: 10px; display: block;">Find a concert</a>
            </div>
        <?php endif; ?>
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