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

// Logic to handle status update
if (isset($_GET['update_id']) && isset($_GET['new_status'])) {
    $booking_id = $_GET['update_id'];
    $new_status = $_GET['new_status'];
    
    $update_stmt = $conn->prepare("UPDATE bookings SET status = ? WHERE id = ?");
    $update_stmt->bind_param("si", $new_status, $booking_id);
    $update_stmt->execute();
    
    header("Location: dashboard.php");
    exit;
}

$sql = "SELECT bookings.id as booking_id, users.fullname, concerts.concert_name, bookings.quantity, 
               bookings.total_price, bookings.payment_reference, bookings.status 
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

        /* --- Navigation --- */
        nav {
            display: flex; justify-content: space-between; align-items: center;
            padding: 0 8%; background: var(--nav-bg); backdrop-filter: blur(12px);
            position: sticky; top: 0; z-index: 1000; border-bottom: 1px solid var(--glass-border);
            height: 80px;
        }

        .logo { font-weight: 800; font-size: 1.4rem; display: flex; align-items: center; gap: 10px; color: var(--text-main); text-decoration: none; }
        .nav-links { display: flex; align-items: center; gap: 2rem; }
        .nav-links a { text-decoration: none; color: var(--text-muted); font-weight: 600; font-size: 0.85rem; }
        .nav-links a.active { color: var(--accent-primary); }

        /* Logout Button UI */
        .btn-logout {
            background: var(--accent-gradient);
            color: #ffffff !important;
            padding: 10px 24px;
            border-radius: 10px;
            font-weight: 700;
            box-shadow: 0 10px 15px -3px rgba(129, 140, 248, 0.3);
        }

        /* Theme Switch UI */
        .theme-switch {
            width: 50px; height: 26px; background: var(--glass-border);
            border-radius: 50px; position: relative; cursor: pointer;
            display: flex; align-items: center; padding: 0 5px; justify-content: space-between;
        }
        .switch-dot {
            position: absolute; width: 18px; height: 18px; background: var(--accent-gradient);
            border-radius: 50%; left: 4px; transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        body.dark .switch-dot { transform: translateX(24px); }

        /* --- Table & Dashboard --- */
        .admin-container { padding: 40px 8%; }
        .table-wrapper { background: var(--surface); border: 1px solid var(--glass-border); border-radius: 24px; overflow: hidden; }
        .admin-table { width: 100%; border-collapse: collapse; text-align: left; }
        .admin-table th { background: var(--glass-border); padding: 20px; font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted); font-weight: 800; }
        .admin-table td { padding: 20px; border-bottom: 1px solid var(--glass-border); font-size: 0.9rem; font-weight: 600; }
        
        /* Status Badges */
        .status-badge {
            padding: 6px 14px; border-radius: 50px; font-size: 0.75rem; font-weight: 800;
            text-transform: uppercase; text-decoration: none; display: inline-flex; align-items: center; gap: 5px;
        }
        .status-paid { background: rgba(34, 197, 94, 0.1); color: #22c55e; border: 1px solid rgba(34, 197, 94, 0.2); }
        .status-pending { background: rgba(234, 179, 8, 0.1); color: #eab308; border: 1px solid rgba(234, 179, 8, 0.2); }
        .status-cancelled { background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.2); }
        
        .ref-code { font-family: monospace; background: rgba(255,255,255,0.05); padding: 4px 8px; border-radius: 6px; color: var(--accent-primary); }
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
            <span class="material-symbols-outlined" style="font-size: 12px; color: var(--text-muted);">light_mode</span>
            <span class="material-symbols-outlined" style="font-size: 12px; color: var(--text-muted);">dark_mode</span>
            <div class="switch-dot"></div>
        </div>

        <a href="<?php echo $root; ?>auth/logout.php" class="btn-logout">LOGOUT</a>
    </div>
</nav>

<div class="admin-container">
    <header style="margin-bottom: 30px;">
        <h1 style="font-size: 2.2rem; font-weight: 800; letter-spacing: -1px;">Sales Summary</h1>
        <p style="color: var(--text-muted);">Real-time payment verification report</p>
    </header>
    
    <div class="table-wrapper">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Buyer</th>
                    <th>Concert</th>
                    <th>Qty</th>
                    <th>Reference No.</th>
                    <th>Status (Click to cycle)</th>
                    <th>Revenue</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): 
                        $curr = strtolower($row['status'] ?? 'pending');
                        $next = ($curr == 'pending') ? 'paid' : (($curr == 'paid') ? 'cancelled' : 'pending');
                    ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($row['fullname']); ?></strong></td>
                            <td><?php echo htmlspecialchars($row['concert_name']); ?></td>
                            <td><?php echo $row['quantity']; ?></td>
                            <td><span class="ref-code">#<?php echo htmlspecialchars($row['payment_reference']); ?></span></td>
                            <td>
                                <a href="?update_id=<?php echo $row['booking_id']; ?>&new_status=<?php echo $next; ?>" 
                                   class="status-badge status-<?php echo $curr; ?>">
                                    <span class="material-symbols-outlined" style="font-size: 14px;">sync</span>
                                    <?php echo ucfirst($curr); ?>
                                </a>
                            </td>
                            <td style="color: var(--accent-primary); font-weight: 800;">
                                ₱<?php echo number_format($row['total_price'], 2); ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="6" style="text-align:center; padding:60px; color:var(--text-muted);">No transactions found.</td></tr>
                <?php endif; ?>
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