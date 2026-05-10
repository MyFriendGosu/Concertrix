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

$concert = null;
if (isset($_GET['id'])) {
    $concert_id = $_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM concerts WHERE id = ?");
    $stmt->bind_param("i", $concert_id);
    $stmt->execute();
    $concert = $stmt->get_result()->fetch_assoc();

    $zone_stmt = $conn->prepare("SELECT * FROM seat_zones WHERE concert_id = ?");
    $zone_stmt->bind_param("i", $concert_id);
    $zone_stmt->execute();
    $zones = $zone_stmt->get_result();
}

if (!$concert) {
    header("Location: home.php");
    exit;
}

$img_path = !empty($concert['image']) ? "assets/images/concerts/".$concert['image'] : "assets/images/Concert.png";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($concert['concert_name']); ?> | Concertix</title>
    
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --bg-body: #f8fafc;
            --surface: #ffffff;
            --accent-primary: #0052ff;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --glass-border: rgba(0, 0, 0, 0.05);
            --nav-bg: rgba(255, 255, 255, 0.8);
            --brand-navy: #001571; 
        }

        body.dark {
            --bg-body: #020617;
            --surface: #0f172a;
            --accent-primary: #3b82f6;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --glass-border: rgba(255, 255, 255, 0.1);
            --nav-bg: rgba(2, 6, 23, 0.8);
            --brand-navy: #1e3a8a;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-body);
            color: var(--text-main);
            transition: background 0.3s ease;
        }

        nav {
            display: flex; justify-content: space-between; align-items: center;
            padding: 0 8%; background: var(--nav-bg); backdrop-filter: blur(12px);
            position: sticky; top: 0; z-index: 1000; height: 80px;
            border-bottom: 1px solid var(--glass-border);
        }

        .logo { font-weight: 800; font-size: 1.4rem; display: flex; align-items: center; gap: 10px; color: var(--text-main); text-decoration: none; }
        
        .container { padding: 40px 8%; max-width: 1100px; margin: 0 auto; }

        /* --- Hero Section --- */
        .hero-card {
            background: var(--surface);
            border-radius: 24px;
            overflow: hidden;
            border: 1px solid var(--glass-border);
            margin-bottom: 40px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        }

        .image-container {
            width: 100%; height: 450px;
            background: #000; display: flex;
            align-items: center; justify-content: center;
        }

        .hero-image { width: 100%; height: 100%; object-fit: cover; }

        .hero-content { padding: 40px; }
        .blue-divider { width: 45px; height: 4px; background: #00c2ff; margin-bottom: 20px; border-radius: 2px; }
        .concert-title { font-size: 3rem; font-weight: 800; margin-bottom: 15px; letter-spacing: -1.5px; }

        /* --- Schedule Section (ss4.PNG Style) --- */
        .about-section {
            background: var(--surface);
            border-radius: 24px;
            padding: 40px;
            border: 1px solid var(--glass-border);
        }

        .section-header {
            font-size: 1.8rem; font-weight: 700; text-transform: uppercase;
            margin-bottom: 35px; color: var(--text-main); letter-spacing: 0.5px;
        }

        .schedule-row {
            display: flex; justify-content: space-between; align-items: center;
            padding: 25px 0; border-bottom: 1px solid var(--glass-border);
        }

        .schedule-row:last-child { border-bottom: none; }

        .schedule-info h3 { 
            font-size: 1.6rem; font-weight: 500; color: var(--text-main); margin-bottom: 2px; 
        }
        
        .venue-text { 
            font-size: 1.25rem; color: var(--text-main); font-weight: 400; margin-bottom: 8px;
        }

        .zone-details { color: var(--text-muted); font-size: 0.95rem; }

        /* --- Navy Button Style --- */
        .buy-tickets-btn {
            background-color: var(--brand-navy);
            color: #fff;
            text-decoration: none;
            padding: 14px 28px;
            border-radius: 6px;
            font-weight: 700;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: transform 0.2s ease, opacity 0.2s ease;
        }

        .buy-tickets-btn:hover { opacity: 0.9; transform: translateY(-2px); }

        @media (max-width: 768px) {
            .schedule-row { flex-direction: column; align-items: flex-start; gap: 20px; }
            .buy-tickets-btn { width: 100%; justify-content: center; }
        }
    </style>
</head>
<body class="dark">

<nav>
    <a href="home.php" class="logo">
        <span class="material-symbols-outlined" style="color: #0052ff">theater_comedy</span>
        CONCERTIX
    </a>
    <div style="display: flex; gap: 20px; align-items: center;">
        <a href="home.php" style="text-decoration:none; color:var(--text-muted); font-weight:700; font-size:0.85rem;">EXPLORE</a>
        <div id="themeToggle" style="cursor:pointer; color:var(--text-main);"><span class="material-symbols-outlined">contrast</span></div>
    </div>
</nav>

<main class="container">
    <div class="hero-card">
        <div class="image-container">
            <img src="<?php echo $root . $img_path; ?>" class="hero-image" alt="Concert Poster">
        </div>
        <div class="hero-content">
            <div class="blue-divider"></div>
            <h1 class="concert-title"><?php echo htmlspecialchars($concert['concert_name']); ?></h1>
            <p style="color: var(--text-muted); line-height: 1.8; max-width: 850px;">
                <?php echo nl2br(htmlspecialchars($concert['description'])); ?>
            </p>
        </div>
    </div>

    <div class="about-section">
        <h2 class="section-header">About This Event</h2>

        <div class="schedule-list">
            <?php 
            while ($z = $zones->fetch_assoc()): 
                // Format: Friday, May 15 | 7:30 PM
                $formatted_date = date('l, F j', strtotime($concert['concert_date']));
                $formatted_time = date('g:i A', strtotime($concert['concert_time']));
                
                $sold_out = $z['available_slots'] <= 0;
            ?>
                <div class="schedule-row">
                    <div class="schedule-info">
                        <h3><?php echo $formatted_date; ?> | <?php echo $formatted_time; ?></h3>
                        <p class="venue-text"><?php echo htmlspecialchars($concert['venue']); ?></p>
                        <p class="zone-details">
                            <?php echo htmlspecialchars($z['zone_name']); ?> • ₱<?php echo number_format($z['price'], 2); ?>
                            <?php if (!$sold_out): ?>
                                <span style="margin-left:8px; color: #10b981;">(<?php echo $z['available_slots']; ?> left)</span>
                            <?php endif; ?>
                        </p>
                    </div>

                    <?php if (!$sold_out): ?>
                        <a href="buy-ticket.php?id=<?php echo $concert['id']; ?>&zone_id=<?php echo $z['id']; ?>" class="buy-tickets-btn">
                            BUY TICKETS <span class="material-symbols-outlined" style="font-size: 16px;">chevron_right</span>
                        </a>
                    <?php else: ?>
                        <button class="buy-tickets-btn" style="background: #94a3b8; cursor: not-allowed;" disabled>SOLD OUT</button>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</main>

<script>
    const themeToggle = document.getElementById('themeToggle');
    const body = document.body;
    
    if (localStorage.getItem('theme') === 'light') body.classList.remove('dark');
    
    themeToggle.addEventListener('click', () => {
        body.classList.toggle('dark');
        localStorage.setItem('theme', body.classList.contains('dark') ? 'dark' : 'light');
    });
</script>

</body>
</html>