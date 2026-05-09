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
$formatted_date = (strtotime($concert['concert_date'])) ? date('j M Y', strtotime($concert['concert_date'])) : $concert['concert_date'];
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
            --accent-gradient: linear-gradient(135deg, #0052ff 0%, #00c2ff 100%);
            --text-main: #0f172a;
            --text-muted: #64748b;
            --glass-border: rgba(0, 0, 0, 0.05);
            --nav-bg: rgba(255, 255, 255, 0.8);
        }

        body.dark {
            --bg-body: #020617;
            --surface: #0f172a;
            --accent-primary: #3b82f6;
            --accent-gradient: linear-gradient(135deg, #3b82f6 0%, #2dd4bf 100%);
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

        /* Nav Bar - Synchronized with home.php */
        nav {
            display: flex; justify-content: space-between; align-items: center;
            padding: 0 8%; background: var(--nav-bg); backdrop-filter: blur(12px);
            position: sticky; top: 0; z-index: 1000; height: 80px;
            border-bottom: 1px solid var(--glass-border);
        }

        .logo { font-weight: 800; font-size: 1.4rem; display: flex; align-items: center; gap: 10px; color: var(--text-main); text-decoration: none; }
        
        /* Container and Card Style (Referencing image_855514.png) */
        .container { padding: 40px 8%; max-width: 1200px; margin: 0 auto; }

        .details-card {
            background: var(--surface);
            border-radius: 16px;
            overflow: hidden;
            border: 1px solid var(--glass-border);
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        }

        .image-container {
            width: 100%;
            height: 450px;
            background: #000;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .hero-image {
            width: 100%;
            height: 100%;
            object-fit: contain; /* Ensures the image fits properly as requested */
        }

        .content-panel { padding: 40px; }

        .date-label { font-weight: 800; font-size: 0.95rem; margin-bottom: 8px; }
        
        .blue-divider {
            width: 50px; height: 4px; background: #00c2ff; margin-bottom: 25px; border-radius: 2px;
        }

        .concert-title { font-size: 2.8rem; font-weight: 800; color: var(--text-main); margin-bottom: 15px; letter-spacing: -1px; }

        .venue-info {
            display: flex; align-items: center; gap: 8px; color: var(--text-muted); font-weight: 600; margin-bottom: 30px;
        }

        /* Zones Grid */
        .zones-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 24px;
            margin-top: 40px;
            border-top: 1px solid var(--glass-border);
            padding-top: 40px;
        }

        .zone-item {
            background: var(--bg-body);
            padding: 25px;
            border-radius: 12px;
            border: 1px solid var(--glass-border);
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .zone-header { font-weight: 800; font-size: 1.1rem; }
        .price-tag { font-size: 1.8rem; font-weight: 800; color: var(--accent-primary); }
        
        /* Buy Ticket Button Style - Referencing image_855514.png */
        .buy-btn {
            background: #0052ff;
            color: #fff;
            text-decoration: none;
            padding: 14px 28px;
            border-radius: 50px;
            font-weight: 800;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            transition: transform 0.2s ease, filter 0.2s ease;
            border: none;
            cursor: pointer;
        }

        .buy-btn:hover { transform: translateY(-2px); filter: brightness(1.1); }
        .buy-btn:disabled { background: var(--text-muted); cursor: not-allowed; }

        @media (max-width: 768px) {
            .image-container { height: 300px; }
            .concert-title { font-size: 2rem; }
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
    <div class="details-card">
        <div class="image-container">
            <img src="<?php echo $root . $img_path; ?>" class="hero-image" alt="Concert Poster">
        </div>

        <div class="content-panel">
            <p class="date-label"><?php echo $formatted_date; ?></p>
            <div class="blue-divider"></div>
            
            <h1 class="concert-title"><?php echo htmlspecialchars($concert['concert_name']); ?></h1>
            
            <div class="venue-info">
                <span class="material-symbols-outlined">location_on</span>
                <?php echo htmlspecialchars($concert['venue']); ?> • <?php echo htmlspecialchars($concert['concert_time']); ?>
            </div>

            <p style="color: var(--text-muted); line-height: 1.8; max-width: 800px;">
                <?php echo nl2br(htmlspecialchars($concert['description'])); ?>
            </p>

            <div class="zones-grid">
                <?php while ($z = $zones->fetch_assoc()): 
                    $sold_out = $z['available_slots'] <= 0;
                ?>
                    <div class="zone-item">
                        <div>
                            <div class="zone-header"><?php echo htmlspecialchars($z['zone_name']); ?></div>
                            <div class="price-tag">₱<?php echo number_format($z['price'], 2); ?></div>
                            <div style="font-size: 0.8rem; font-weight: 700; color: var(--text-muted);">
                                <?php echo $sold_out ? "SOLD OUT" : $z['available_slots'] . " TICKETS REMAINING"; ?>
                            </div>
                        </div>

                        <?php if (!$sold_out): ?>
                            <a href="buy-ticket.php?id=<?php echo $concert['id']; ?>&zone_id=<?php echo $z['id']; ?>" class="buy-btn">
                                Buy tickets <span class="material-symbols-outlined">confirmation_number</span>
                            </a>
                        <?php else: ?>
                            <button class="buy-btn" disabled>Unavailable</button>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            </div>
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