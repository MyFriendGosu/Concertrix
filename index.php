<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once __DIR__ . '/config/db.php';
$root = "/concert_ticketing_system/"; 

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
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: var(--bg-body); color: var(--text-main); }

        nav {
            display: flex; justify-content: space-between; align-items: center;
            padding: 0 8%; background: var(--nav-bg); backdrop-filter: blur(12px);
            position: sticky; top: 0; z-index: 1000; border-bottom: 1px solid var(--glass-border);
            height: 80px;
        }

        .logo { font-weight: 800; font-size: 1.4rem; display: flex; align-items: center; gap: 10px; color: var(--text-main); text-decoration: none; }
        .nav-links { display: flex; align-items: center; gap: 2.5rem; }
        .nav-links a { text-decoration: none; color: var(--text-muted); font-weight: 600; font-size: 0.85rem; }
        
        .btn-register {
            background: var(--accent-gradient); color: #ffffff !important;
            padding: 10px 24px; border-radius: 10px; font-weight: 700;
        }

        /* Theme Switch Styling */
        .theme-switch {
            width: 50px; height: 26px; background: var(--glass-border);
            border-radius: 50px; position: relative; cursor: pointer;
            display: flex; align-items: center; padding: 0 5px; justify-content: space-between;
            flex-shrink: 0;
        }

        .switch-dot {
            position: absolute; width: 18px; height: 18px; background: var(--accent-gradient);
            border-radius: 50%; transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1); left: 4px;
        }
        body.dark .switch-dot { transform: translateX(24px); }

        .hero {
            height: 50vh; display: flex; flex-direction: column; justify-content: center;
            align-items: center; text-align: center; color: white; padding: 2rem;
            background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('<?php echo $root; ?>assets/images/Concert.png');
            background-size: cover; background-position: center;
        }

        .container { padding: 60px 8%; }
        .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 25px; }

        .concert-card {
            background: var(--surface); border: 1px solid var(--glass-border);
            border-radius: 20px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            display: flex; flex-direction: column; height: 100%;
        }

        .card-image-wrapper { width: 100%; height: 180px; background: #000; overflow: hidden; }
        .card-image { width: 100%; height: 100%; object-fit: cover; }
        .card-content { padding: 20px; flex-grow: 1; display: flex; flex-direction: column; }

        .card-title {
            font-size: 1.15rem; font-weight: 800; margin: 5px 0 15px 0;
            min-height: 2.8rem; display: -webkit-box; -webkit-line-clamp: 2;
            -webkit-box-orient: vertical; overflow: hidden;
        }

        .arrow-action-wrapper {
            display: flex; justify-content: space-between; align-items: center;
            margin-top: auto; padding-top: 15px; border-top: 1px solid var(--glass-border);
        }

        .btn-details {
            background: none; border: none; color: var(--accent-primary);
            font-size: 0.75rem; font-weight: 800; cursor: pointer; text-transform: uppercase;
            font-family: inherit;
        }

        .btn-arrow {
            text-decoration: none; color: var(--accent-primary); width: 38px; height: 38px;
            display: flex; align-items: center; justify-content: center; border-radius: 50%;
            background: rgba(99, 102, 241, 0.08); transition: all 0.3s ease;
        }

        .btn-arrow:hover { background: var(--accent-primary); color: #fff !important; transform: translateX(5px); }

        .modal-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.85); backdrop-filter: blur(10px);
            z-index: 2000; display: none; align-items: center; justify-content: center; padding: 20px;
        }
        .modal-card {
            background: var(--surface); width: 100%; max-width: 550px;
            border-radius: 28px; border: 1px solid var(--glass-border); overflow: hidden;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }
        .modal-body { padding: 40px; }
    </style>
</head>
<body class="dark">

<nav>
    <a href="index.php" class="logo">
        <span class="material-symbols-outlined" style="color: var(--accent-primary)">theater_comedy</span>
        CONCERTIX
    </a>
    <div class="nav-links">
        <a href="index.php">HOME</a>
        <a href="<?php echo $root; ?>auth/login.php">LOGIN</a>
        <a href="<?php echo $root; ?>auth/register.php" class="btn-register">REGISTER</a>
        
        <div class="theme-switch" id="themeToggle">
            <span class="material-symbols-outlined" style="font-size: 14px;">light_mode</span>
            <span class="material-symbols-outlined" style="font-size: 14px;">dark_mode</span>
            <div class="switch-dot"></div>
        </div>
    </div>
</nav>

<section class="hero">
    <h1 style="font-size: 3rem; letter-spacing: -2px; font-weight: 800;">Feel the Music.<br>Live the Moment.</h1>
</section>

<main class="container">
    <h2 style="font-weight: 800; margin-bottom: 30px; letter-spacing: -1px; font-size: 2rem;">UPCOMING CONCERTS</h2>
    <div class="grid">
        <?php
        $query = "SELECT * FROM concerts ORDER BY concert_date ASC LIMIT 4";
        $result = $conn->query($query);
        while ($row = $result->fetch_assoc()):
            $img = !empty($row['image']) ? "assets/images/concerts/".$row['image'] : "assets/images/Concert.png";
        ?>
            <div class="concert-card">
                <div class="card-image-wrapper">
                    <img src="<?php echo $root . $img; ?>" class="card-image">
                </div>
                <div class="card-content">
                    <span style="font-size: 0.65rem; color: var(--accent-primary); font-weight: 800; text-transform: uppercase;">
                        <?php echo date('M d, Y', strtotime($row['concert_date'])); ?>
                    </span>
                    <h3 class="card-title"><?php echo htmlspecialchars($row['concert_name']); ?></h3>
                    
                    <div class="arrow-action-wrapper">
                        <button class="btn-details" 
                                onclick='openDetails(<?php echo htmlspecialchars(json_encode($row), ENT_QUOTES, "UTF-8"); ?>)'>
                            View Details
                        </button>
                        <a href="<?php echo $root; ?>auth/login.php" class="btn-arrow">
                            <span class="material-symbols-outlined">arrow_forward</span>
                        </a>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</main>

<div class="modal-overlay" id="detailsModal" onclick="closeDetails()">
    <div class="modal-card" onclick="event.stopPropagation()">
        <div class="modal-body">
            <h2 id="m-title" style="font-size: 1.8rem; font-weight: 800; letter-spacing: -1px;"></h2>
            <div style="display: flex; gap: 20px; margin: 10px 0 20px 0;">
                <span id="m-date" style="font-size: 0.8rem; font-weight: 700; color: var(--accent-primary);"></span>
                <span id="m-venue" style="font-size: 0.8rem; font-weight: 700; color: var(--text-muted);"></span>
            </div>
            
            <p id="m-desc" style="line-height: 1.7; color: var(--text-muted); margin-bottom: 30px; white-space: pre-line;"></p>
            
            <button onclick="closeDetails()" style="width: 100%; padding: 16px; background: var(--accent-gradient); color: #fff; border: none; border-radius: 12px; font-weight: 800; cursor: pointer;">Close Details</button>
        </div>
    </div>
</div>

<script>
    const themeToggle = document.getElementById('themeToggle');
    const body = document.body;

    // Load theme
    if (localStorage.getItem('theme') === 'light') body.classList.remove('dark');

    // Toggle logic
    themeToggle.addEventListener('click', () => {
        body.classList.toggle('dark');
        localStorage.setItem('theme', body.classList.contains('dark') ? 'dark' : 'light');
    });

    function openDetails(data) {
        document.getElementById('m-title').innerText = data.concert_name || "Event Details";
        if (data.concert_date) {
            const date = new Date(data.concert_date);
            document.getElementById('m-date').innerText = date.toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' });
        }
        document.getElementById('m-venue').innerText = data.venue || "Venue TBA";
        document.getElementById('m-desc').innerText = data.description || "Experience an incredible performance live. Join us for a night to remember!";
        document.getElementById('detailsModal').style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    function closeDetails() {
        document.getElementById('detailsModal').style.display = 'none';
        document.body.style.overflow = 'auto';
    }
</script>

</body>
</html>