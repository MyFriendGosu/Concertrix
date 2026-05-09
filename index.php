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
        .nav-links { display: flex; align-items: center; gap: 2rem; }
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
            margin-left: 10px;
        }

        .switch-dot {
            position: absolute; width: 18px; height: 18px; background: var(--accent-gradient);
            border-radius: 50%; transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1); left: 4px;
        }
        body.dark .switch-dot { transform: translateX(24px); }

        /* --- Hero Section --- */
        .hero {
            height: 60vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), 
                        url('<?php echo $root; ?>assets/images/Concert.png');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 2rem;
        }

        .hero h1 { font-size: 3rem; font-weight: 800; line-height: 1.1; }

        /* --- Content Grid --- */
        .container { padding: 60px 8%; }
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }

        .concert-card {
            background: var(--surface);
            border: 1px solid var(--glass-border);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            transition: transform 0.3s ease;
            display: flex;
            flex-direction: column;
        }
        .concert-card:hover { transform: translateY(-5px); }

        /* --- REDUCED IMAGE SIZE --- */
        .card-image-wrapper {
            width: 100%;
            height: 160px;
            background: #000;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .card-image {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .card-content { padding: 18px; flex-grow: 1; }
        
        .date-badge {
            font-size: 0.65rem;
            color: var(--accent-primary);
            text-transform: uppercase;
            font-weight: 800;
            margin-bottom: 5px;
            display: block;
        }
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
        <a href="<?php echo $root; ?>auth/login.php">LOGIN</a>
        <a href="<?php echo $root; ?>auth/register.php" class="btn-register">REGISTER</a>
        
        <div class="theme-switch" id="themeToggle">
            <div class="switch-dot"></div>
        </div>
    </div>
</nav>

<section class="hero">
    <h1>Feel the Music.<br>Live the Moment.</h1>
</section>

<main class="container" id="concerts">
    <h2 style="font-weight: 800; letter-spacing: -1px;">UPCOMING CONCERTS</h2>

    <div class="grid">
        <?php
        $query = "SELECT * FROM concerts ORDER BY concert_date ASC LIMIT 4";
        $result = $conn->query($query);
        
        if ($result && $result->num_rows > 0):
            while ($row = $result->fetch_assoc()):
                $img_path = !empty($row['image']) ? "assets/images/concerts/".$row['image'] : "assets/images/Concert.png";
        ?>
            <div class="concert-card">
                <div class="card-image-wrapper">
                    <img src="<?php echo $root . $img_path; ?>" class="card-image" alt="Concert">
                </div>
                <div class="card-content">
                    <span class="date-badge"><?php echo date('M d, Y', strtotime($row['concert_date'])); ?></span>
                    <h3 style="margin: 5px 0 15px 0; font-size: 1.1rem;"><?php echo htmlspecialchars($row['concert_name']); ?></h3>
                    <div style="display: flex; justify-content: flex-end;">
                        <a href="<?php echo $root; ?>auth/login.php" class="material-symbols-outlined" style="text-decoration: none; color: var(--accent-primary); font-weight: 800;">arrow_forward</a>
                    </div>
                </div>
            </div>
        <?php 
            endwhile;
        endif; 
        ?>
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