<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once __DIR__ . '/config/db.php';
$root = "/concert_ticketing_system/"; 

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: " . $root . ($_SESSION['role'] === 'admin' ? "admin/dashboard.php" : "pages/home.php"));
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Concertix | Live the Moment</title>
    
    <!-- External Assets -->
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
        }

        /* --- Navigation --- */
        nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.5rem 8%;
            background: var(--nav-bg);
            backdrop-filter: blur(12px);
            position: sticky;
            top: 0;
            z-index: 1000;
            border-bottom: 1px solid var(--glass-border);
        }

        .logo { font-weight: 800; font-size: 1.4rem; display: flex; align-items: center; gap: 10px; color: var(--text-main); text-decoration: none; }
        .nav-links { display: flex; align-items: center; gap: 2.5rem; }
        .nav-links a { text-decoration: none; color: var(--text-muted); font-weight: 600; font-size: 0.85rem; }
        .nav-links a:hover { color: var(--accent-primary); }

        .btn-register {
            background: var(--accent-gradient);
            color: #ffffff !important;
            padding: 10px 24px;
            border-radius: 10px;
            font-weight: 700;
            box-shadow: 0 10px 15px -3px rgba(168, 85, 247, 0.3);
        }

        .theme-switch {
            width: 50px; height: 26px; background: var(--glass-border);
            border-radius: 50px; position: relative; cursor: pointer;
            display: flex; align-items: center; padding: 0 5px; justify-content: space-between;
        }

        .switch-dot {
            position: absolute; width: 18px; height: 18px; background: var(--accent-gradient);
            border-radius: 50%; transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1); left: 4px;
        }
        body.dark .switch-dot { transform: translateX(24px); }

        /* --- Hero Section --- */
        .hero {
            height: 65vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            /* Updated to use Concert.png from your assets */
            background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), 
                        url('<?php echo $root; ?>assets/images/Concert.png');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 2rem;
        }

        .hero-badge {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 8px 20px;
            border-radius: 50px;
            text-transform: uppercase;
            letter-spacing: 5px;
            font-weight: 700;
            font-size: 0.7rem;
            margin-bottom: 20px;
        }

        .hero h1 { font-size: 3.5rem; font-weight: 800; line-height: 1.1; }

        /* --- Content Grid --- */
        .container { padding: 80px 8%; }
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
            margin-top: 30px;
        }

        .concert-card {
            background: var(--surface);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
            transition: transform 0.3s ease;
        }
        .concert-card:hover { transform: translateY(-5px); }
        .card-image { height: 180px; width: 100%; object-fit: cover; }
        .card-content { padding: 20px; }
        .price { color: var(--accent-primary); font-weight: 800; font-size: 1.2rem; }
    </style>
</head>
<body class="dark">

<nav>
    <a href="#" class="logo">
        <span class="material-symbols-outlined" style="color: var(--accent-primary)">theater_comedy</span>
        CONCERTIX
    </a>
    
    <div class="nav-links">
        <a href="#">HOME</a>
        <a href="#concerts">CONCERTS</a>
        <a href="#">ABOUT</a>
        
        <div class="theme-switch" id="themeToggle">
            <span class="material-symbols-outlined" style="font-size: 14px;">light_mode</span>
            <span class="material-symbols-outlined" style="font-size: 14px;">dark_mode</span>
            <div class="switch-dot"></div>
        </div>

        <a href="<?php echo $root; ?>auth/login.php">LOGIN</a>
        <a href="<?php echo $root; ?>auth/register.php" class="btn-register">REGISTER</a>
    </div>
</nav>

<section class="hero">
    <div class="hero-badge">Live Experience</div>
    <h1>Feel the Music.<br>Live the Moment.</h1>
</section>

<main class="container" id="concerts">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <h2 style="font-weight: 800;">UPCOMING CONCERTS</h2>
        <a href="<?php echo $root; ?>pages/home.php" style="color: var(--accent-primary); text-decoration: none; font-weight: 700;">View All</a>
    </div>

    <div class="grid">
        <?php
        $query = "SELECT * FROM concerts ORDER BY concert_date ASC LIMIT 4";
        $result = $conn->query($query);
        
        if ($result && $result->num_rows > 0):
            while ($row = $result->fetch_assoc()):
                // Fallback logic for images
                $img_path = !empty($row['image']) ? "assets/images/concerts/".$row['image'] : "assets/images/Concert.png";
        ?>
            <div class="concert-card">
                <img src="<?php echo $root . $img_path; ?>" class="card-image" alt="Concert">
                <div class="card-content">
                    <p style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700;">
                        <?php echo date('M d, Y', strtotime($row['concert_date'])); ?>
                    </p>
                    <h3 style="margin: 5px 0 15px 0;"><?php echo htmlspecialchars($row['concert_name']); ?></h3>
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span class="price">₱<?php echo number_format($row['price'], 0); ?></span>
                        <a href="<?php echo $root; ?>auth/login.php" class="material-symbols-outlined" style="text-decoration: none; color: var(--text-muted);">arrow_forward</a>
                    </div>
                </div>
            </div>
        <?php 
            endwhile;
        else:
            echo "<p style='color: var(--text-muted); grid-column: 1/-1; text-align: center; padding: 40px;'>No concerts currently scheduled. Check back soon!</p>";
        endif; 
        ?>
    </div>
</main>

<script>
    const themeToggle = document.getElementById('themeToggle');
    const body = document.body;

    // Persist theme preference
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