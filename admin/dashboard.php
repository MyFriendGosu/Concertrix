<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once __DIR__ . '/../config/db.php';

$root = "/concert_ticketing_system/";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: " . $root . "index.php");
    exit;
}

$sql = "SELECT users.fullname, concerts.concert_name, bookings.quantity, 
               bookings.total_price, bookings.payment_reference 
        FROM bookings 
        JOIN users ON bookings.user_id = users.id 
        JOIN concerts ON bookings.concert_id = concerts.id 
        ORDER BY bookings.id DESC"; 

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Concertix</title>
    
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

        * { margin: 0; padding: 0; box-sizing: border-box; transition: all 0.3s ease; }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-body);
            color: var(--text-main);
            min-height: 100vh;
        }

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
        .nav-links a.active { color: var(--accent-primary); }

        .btn-logout {
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
            border-radius: 50%; left: 4px;
        }
        body.dark .switch-dot { transform: translateX(24px); }

        .admin-container { padding: 40px 8%; }
        
        /* --- Action Bar --- */
        .action-bar {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-bottom: 30px;
            gap: 20px;
            flex-wrap: wrap;
        }

        .admin-header h1 { font-size: 2.2rem; font-weight: 800; letter-spacing: -1px; margin-bottom: 5px; }
        .admin-header p { color: var(--text-muted); font-weight: 600; }

        .button-group { display: flex; gap: 12px; }

        .btn-action {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 12px 20px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 700;
            font-size: 0.85rem;
            border: 1px solid var(--glass-border);
            background: var(--surface);
            color: var(--text-main);
        }

        .btn-action.primary {
            background: var(--accent-gradient);
            color: white;
            border: none;
            box-shadow: 0 10px 20px -5px rgba(99, 102, 241, 0.4);
        }

        .btn-action:hover { transform: translateY(-3px); }

        .table-wrapper {
            background: var(--surface);
            border: 1px solid var(--glass-border);
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0,0,0,0.05);
        }

        .admin-table { width: 100%; border-collapse: collapse; text-align: left; }
        .admin-table th {
            background: var(--glass-border);
            padding: 20px;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 800;
            color: var(--text-muted);
        }

        .admin-table td { padding: 20px; border-bottom: 1px solid var(--glass-border); font-size: 0.9rem; font-weight: 600; }
        .ref-code { font-family: monospace; background: var(--bg-body); padding: 4px 8px; border-radius: 6px; color: var(--accent-primary); }
        .price-tag { color: var(--accent-primary); font-weight: 800; }
    </style>
</head>
<body class="dark">

<nav>
    <a href="#" class="logo">
        <span class="material-symbols-outlined" style="color: var(--accent-primary)">theater_comedy</span>
        CONCERTIX ADMIN
    </a>
    <div class="nav-links">
        <a href="dashboard.php" class="active">REPORTS</a>
        <a href="manage-concerts.php">CONCERTS</a>
        <div class="theme-switch" id="themeToggle">
            <span class="material-symbols-outlined" style="font-size: 14px;">light_mode</span>
            <span class="material-symbols-outlined" style="font-size: 14px;">dark_mode</span>
            <div class="switch-dot"></div>
        </div>
        <a href="<?php echo $root; ?>auth/logout.php" class="btn-logout">LOGOUT</a>
    </div>
</nav>

<div class="admin-container">
    <div class="action-bar">
        <header class="admin-header">
            <h1>Sales Summary</h1>
            <p>Real-time report of all ticket transactions</p>
        </header>

        <div class="button-group">
            <a href="add-seat-zones.php" class="btn-action">
                <span class="material-symbols-outlined">event_seat</span>
                Seat Zones
            </a>
            <a href="add-concert.php" class="btn-action primary">
                <span class="material-symbols-outlined">add_circle</span>
                Add Concert
            </a>
        </div>
    </div>
    
    <div class="table-wrapper">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Buyer Name</th>
                    <th>Concert Event</th>
                    <th>Qty</th>
                    <th>Total Revenue</th>
                    <th>Reference No.</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($row['fullname']); ?></strong></td>
                            <td><?php echo htmlspecialchars($row['concert_name']); ?></td>
                            <td><?php echo $row['quantity']; ?></td>
                            <td class="price-tag">₱<?php echo number_format($row['total_price'], 2); ?></td>
                            <td><span class="ref-code">#<?php echo htmlspecialchars($row['payment_reference']); ?></span></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5" style="text-align:center; padding:60px; color:var(--text-muted);">No transactions found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    const themeToggle = document.getElementById('themeToggle');
    if (localStorage.getItem('theme') === 'light') document.body.classList.remove('dark');
    themeToggle.addEventListener('click', () => {
        document.body.classList.toggle('dark');
        localStorage.setItem('theme', document.body.classList.contains('dark') ? 'dark' : 'light');
    });
</script>
</body>
</html>