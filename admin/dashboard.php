<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once __DIR__ . '/../config/db.php';

$root = "/concert_ticketing_system/";

// Admin Authorization
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: " . $root . "index.php");
    exit;
}

// Fetch stats
$total_concerts = $conn->query("SELECT COUNT(*) as count FROM concerts")->fetch_assoc()['count'];
$total_users = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'user'")->fetch_assoc()['count'];
$total_tickets = $conn->query("SELECT SUM(available_slots) as count FROM seat_zones")->fetch_assoc()['count'] ?? 0;

// UPDATED QUERY: Pivot seat zones into individual columns
$concert_query = "SELECT c.*, 
                 (SELECT available_slots FROM seat_zones WHERE concert_id = c.id AND zone_name = 'VIP' LIMIT 1) as vip_slots,
                 (SELECT available_slots FROM seat_zones WHERE concert_id = c.id AND zone_name = 'Lower Box' LIMIT 1) as lower_slots,
                 (SELECT available_slots FROM seat_zones WHERE concert_id = c.id AND zone_name = 'Upper Box' LIMIT 1) as upper_slots,
                 (SELECT available_slots FROM seat_zones WHERE concert_id = c.id AND zone_name = 'General Admission' LIMIT 1) as ga_slots
                 FROM concerts c ORDER BY concert_date DESC LIMIT 10";
$concert_result = $conn->query($concert_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Concertix Admin</title>
    
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --bg-body: #f8fafc;
            --surface: #ffffff;
            --accent-primary: #6366f1;
            --accent-gradient: linear-gradient(135deg, #818cf8 0%, #c084fc 100%);
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

        nav { display: flex; justify-content: space-between; align-items: center; padding: 0 8%; background: var(--nav-bg); backdrop-filter: blur(12px); position: sticky; top: 0; z-index: 1000; border-bottom: 1px solid var(--glass-border); height: 80px; }
        .logo { font-weight: 800; font-size: 1.4rem; display: flex; align-items: center; gap: 10px; color: var(--text-main); text-decoration: none; }
        .nav-links { display: flex; align-items: center; gap: 2rem; }
        .nav-links a { text-decoration: none; color: var(--text-muted); font-weight: 600; font-size: 0.85rem; transition: 0.3s; }
        .nav-links a.active { color: var(--accent-primary); }

        .theme-switch { width: 50px; height: 26px; background: var(--glass-border); border-radius: 50px; position: relative; cursor: pointer; display: flex; align-items: center; padding: 0 5px; justify-content: space-between; }
        .switch-dot { position: absolute; width: 18px; height: 18px; background: var(--accent-gradient); border-radius: 50%; transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1); left: 4px; }
        body.dark .switch-dot { transform: translateX(24px); }

        .btn-logout { background: var(--accent-gradient); color: #ffffff !important; padding: 10px 24px; border-radius: 12px; border: none; font-weight: 800 !important; text-transform: uppercase; letter-spacing: 1px; font-size: 0.8rem; transition: 0.3s all ease !important; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2); text-decoration: none; }
        .btn-logout:hover { transform: translateY(-2px); filter: brightness(1.1); box-shadow: 0 8px 20px rgba(129, 140, 248, 0.3); }

        .dashboard-container { padding: 40px 8%; }
        .welcome-sec { margin-bottom: 40px; }
        .welcome-sec h1 { font-size: 2.5rem; font-weight: 800; letter-spacing: -1.5px; }

        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 25px; margin-bottom: 40px; }
        .stat-card { background: var(--surface); padding: 30px; border-radius: 24px; border: 1px solid var(--glass-border); box-shadow: 0 10px 20px rgba(0,0,0,0.05); }
        .stat-card h3 { font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted); letter-spacing: 1px; margin-bottom: 10px; }
        .stat-card .value { font-size: 2rem; font-weight: 800; color: var(--accent-primary); }

        .table-section-title { font-size: 1.2rem; font-weight: 800; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }
        .table-wrapper { background: var(--surface); border: 1px solid var(--glass-border); border-radius: 24px; overflow-x: auto; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
        .admin-table { width: 100%; border-collapse: collapse; text-align: left; min-width: 1000px; }
        .admin-table th { background: rgba(255,255,255,0.02); padding: 20px; font-size: 0.65rem; text-transform: uppercase; color: var(--text-muted); font-weight: 800; border-bottom: 1px solid var(--glass-border); }
        .admin-table td { padding: 20px; border-bottom: 1px solid var(--glass-border); font-size: 0.85rem; vertical-align: middle; }
        
        /* Specific styling for slot columns to make them stand out */
        .slot-cell { font-weight: 700; text-align: center; }
        .slot-vip { color: #fcd34d; } /* Goldish */
        .slot-lb { color: #60a5fa; }  /* Blue */
        .slot-ub { color: #c084fc; }  /* Purple */
        .slot-ga { color: #4ade80; }  /* Green */

        .status-pill { display: inline-flex; align-items: center; justify-content: center; width: 85px; height: 26px; border-radius: 50px; font-size: 0.6rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; }
        .status-active { background: rgba(34, 197, 94, 0.1); color: #22c55e; border: 1px solid rgba(34, 197, 94, 0.2); }
        .status-expired { background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.2); }
    </style>
</head>
<body class="dark">

<nav>
    <a href="dashboard.php" class="logo">
        <span class="material-symbols-outlined" style="color: var(--accent-primary)">theater_comedy</span>
        CONCERTIX ADMIN
    </a>
    <div class="nav-links">
        <a href="dashboard.php" class="active">REPORTS</a>
        <a href="manage-concerts.php">CONCERTS</a>
        <a href="manage-users.php">USERS</a>
        <div class="theme-switch" id="themeToggle">
            <span class="material-symbols-outlined" style="font-size: 14px;">light_mode</span>
            <span class="material-symbols-outlined" style="font-size: 14px;">dark_mode</span>
            <div class="switch-dot"></div>
        </div>
        <a href="<?php echo $root; ?>auth/logout.php" class="btn-logout">LOGOUT</a>
    </div>
</nav>

<div class="dashboard-container">
    <div class="welcome-sec">
        <h1>Admin Overview</h1>
        <p style="color: var(--text-muted);">Platform statistics and detailed seat availability.</p>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <h3>Active Concerts</h3>
            <div class="value"><?php echo $total_concerts; ?></div>
        </div>
        <div class="stat-card">
            <h3>Registered Users</h3>
            <div class="value"><?php echo $total_users; ?></div>
        </div>
        <div class="stat-card">
            <h3>Total Tickets Left</h3>
            <div class="value"><?php echo number_format($total_tickets); ?></div>
        </div>
    </div>

    <div class="table-section-title">
        <span class="material-symbols-outlined" style="color: var(--accent-primary)">analytics</span>
        Seating Inventory Breakdown
    </div>

    <div class="table-wrapper">
        <table class="admin-table">
            <thead>
                <tr>
                    <th style="width: 20%;">Concert Name</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th style="text-align: center;">VIP</th>
                    <th style="text-align: center;">Lower Box</th>
                    <th style="text-align: center;">Upper Box</th>
                    <th style="text-align: center;">Gen. Adm.</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $concert_result->fetch_assoc()): 
                    $is_active = (strtotime($row['concert_date']) >= strtotime(date('Y-m-d')));
                ?>
                <tr>
                    <td style="font-weight: 800;"><?php echo htmlspecialchars($row['concert_name']); ?></td>
                    <td style="color: var(--text-muted);"><?php echo date('M d, Y', strtotime($row['concert_date'])); ?></td>
                    <td>
                        <div class="status-pill <?php echo $is_active ? 'status-active' : 'status-expired'; ?>">
                            <?php echo $is_active ? 'Active' : 'Expired'; ?>
                        </div>
                    </td>
                    <td class="slot-cell slot-vip"><?php echo number_format($row['vip_slots'] ?? 0); ?></td>
                    <td class="slot-cell slot-lb"><?php echo number_format($row['lower_slots'] ?? 0); ?></td>
                    <td class="slot-cell slot-ub"><?php echo number_format($row['upper_slots'] ?? 0); ?></td>
                    <td class="slot-cell slot-ga"><?php echo number_format($row['ga_slots'] ?? 0); ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

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