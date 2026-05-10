<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once __DIR__ . '/../config/db.php';

// The absolute root of your project
$root = "/concert_ticketing_system/"; 

if (isset($_SESSION['user_id'])) {
    $user_name = $_SESSION['user_name'] ?? 'User';
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

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-body);
            color: var(--text-main);
        }

        /* --- Navigation --- */
        nav {
            display: flex; justify-content: space-between; align-items: center;
            padding: 0 8%; background: var(--nav-bg); backdrop-filter: blur(12px);
            position: sticky; top: 0; z-index: 1000; border-bottom: 1px solid var(--glass-border);
            height: 80px;
        }

        .logo { font-weight: 800; font-size: 1.4rem; display: flex; align-items: center; gap: 10px; color: var(--text-main); text-decoration: none; }
        .nav-links { display: flex; align-items: center; gap: 2.5rem; }
        .nav-links a { text-decoration: none; color: var(--text-muted); font-weight: 600; font-size: 0.85rem; }
        .nav-links a:hover, .nav-links a.active { color: var(--accent-primary); }

        .btn-action-nav {
            background: var(--accent-gradient);
            color: #ffffff !important;
            padding: 10px 24px; border-radius: 10px; font-weight: 700;
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

        /* --- Content Grid --- */
        .container { padding: 60px 8%; }
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px; margin-top: 30px;
        }

        .concert-card {
            background: var(--surface);
            border: 1px solid var(--glass-border);
            border-radius: 20px; overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            transition: transform 0.3s ease;
        }
        .concert-card:hover { transform: translateY(-5px); }

        .card-image-wrapper { width: 100%; height: 180px; background: #000; overflow: hidden; }
        .card-image { width: 100%; height: 100%; object-fit: cover; }
        .card-content { padding: 20px; }

        .date-badge {
            font-size: 0.65rem; color: var(--accent-primary);
            text-transform: uppercase; font-weight: 800; margin-bottom: 8px; display: block;
        }

        /* --- Action Arrow UI --- */
        .arrow-action-wrapper {
            display: flex; justify-content: flex-end; align-items: center;
            margin-top: 15px; padding-top: 12px; border-top: 1px solid var(--glass-border);
        }

        .btn-arrow {
            text-decoration: none; color: var(--accent-primary);
            width: 38px; height: 38px; display: flex; align-items: center;
            justify-content: center; border-radius: 50%;
            background: rgba(99, 102, 241, 0.08); transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .btn-arrow:hover { background: var(--accent-primary); color: #ffffff !important; transform: translateX(5px); }
        .btn-arrow .material-symbols-outlined { font-size: 20px; font-weight: 800; }
    </style>
</head>
<body class="dark">

<nav>
    <a href="home.php" class="logo">
        <span class="material-symbols-outlined" style="color: var(--accent-primary)">theater_comedy</span>
        CONCERTIX
    </a>
    
    <div class="nav-links">
        <a href="home.php" class="active">EXPLORE</a>
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="my-tickets.php">MY TICKETS</a>
            <a href="profile.php">PROFILE</a>
            <a href="<?php echo $root; ?>auth/logout.php" class="btn-action-nav">LOGOUT</a>
        <?php else: ?>
            <a href="<?php echo $root; ?>auth/login.php">LOGIN</a>
            <a href="<?php echo $root; ?>auth/register.php" class="btn-action-nav">REGISTER</a>
        <?php endif; ?>
        
        <div class="theme-switch" id="themeToggle">
            <div class="switch-dot"></div>
        </div>
    </div>
</nav>

<main class="container" id="concerts">
    <h2 style="font-weight: 800; letter-spacing: -1px; font-size: 2rem;">UPCOMING EVENTS</h2>

    <div class="grid">
        <?php
        $query = "SELECT * FROM concerts ORDER BY concert_date ASC LIMIT 8";
        $result = $conn->query($query);
        
        if ($result && $result->num_rows > 0):
            while ($row = $result->fetch_assoc()):
                $img_path = !empty($row['image']) ? "assets/images/concerts/".$row['image'] : "assets/images/Concert.png";
                $formatted_date = (strtotime($row['concert_date'])) ? date('j M Y', strtotime($row['concert_date'])) : $row['concert_date'];
        ?>
            <div class="concert-card">
                <div class="card-image-wrapper">
                    <img src="<?php echo $root . $img_path; ?>" class="card-image" alt="Concert Poster">
                </div>
                <div class="card-content">
                    <span class="date-badge"><?php echo $formatted_date; ?></span>
                    <h3 style="font-size: 1.15rem; font-weight: 800; min-height: 2.8rem;"><?php echo htmlspecialchars($row['concert_name']); ?></h3>
                    
                    <div class="arrow-action-wrapper">
                        <a href="<?php echo $root; ?>pages/concert-details.php?id=<?php echo $row['id']; ?>" class="btn-arrow">
                            <span class="material-symbols-outlined">arrow_forward</span>
                        </a>
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

    if (localStorage.getItem('theme') === 'light') body.classList.remove('dark');

    themeToggle.addEventListener('click', () => {
        body.classList.toggle('dark');
        localStorage.setItem('theme', body.classList.contains('dark') ? 'dark' : 'light');
    });
</script>

</body>
</html>