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

$user_id = $_SESSION['user_id'];

// Fetch bookings joined with concert and zone details for the receipt
$query = "
    SELECT b.*, c.concert_name, c.concert_date, c.venue, c.image, z.zone_name, z.price as unit_price
    FROM bookings b
    JOIN concerts c ON b.concert_id = c.id 
    JOIN seat_zones z ON b.seat_zone_id = z.id
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
        .nav-links { display: flex; align-items: center; gap: 2.5rem; height: 100%; }
        .nav-links a { text-decoration: none; color: var(--text-muted); font-weight: 600; font-size: 0.85rem; }
        .nav-links a.active { color: var(--accent-primary); }

        .btn-logout {
            background: var(--accent-gradient); color: #ffffff !important;
            padding: 10px 24px; border-radius: 10px; font-weight: 700;
            box-shadow: 0 10px 15px -3px rgba(168, 85, 247, 0.3); margin-left: 10px;
        }

        .theme-switch {
            width: 50px; height: 26px; background: var(--glass-border);
            border-radius: 50px; position: relative; cursor: pointer;
            display: flex; align-items: center; padding: 0 5px; justify-content: space-between;
            margin-left: 10px; flex-shrink: 0;
        }

        .switch-dot {
            position: absolute; width: 18px; height: 18px; background: var(--accent-gradient);
            border-radius: 50%; transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1); left: 4px;
        }
        body.dark .switch-dot { transform: translateX(24px); }

        .container { padding: 60px 8%; }
        .header-section { margin-bottom: 40px; }
        .header-section h1 { font-size: 2.5rem; font-weight: 800; letter-spacing: -1px; }

        .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 25px; }

        /* --- Ticket UI --- */
        .ticket-card {
            background: var(--surface); border: 1px solid var(--glass-border);
            border-radius: 24px; display: flex; flex-direction: column;
            overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            position: relative;
        }

        .ticket-visual { height: 120px; width: 100%; object-fit: cover; filter: brightness(0.7); }
        .ticket-body { padding: 25px; position: relative; }

        .ticket-divider { border-top: 2px dashed var(--glass-border); margin: 20px 0; position: relative; }
        .ticket-divider::before, .ticket-divider::after {
            content: ''; position: absolute; width: 20px; height: 20px;
            background: var(--bg-body); border-radius: 50%; top: -11px;
        }
        .ticket-divider::before { left: -36px; }
        .ticket-divider::after { right: -36px; }

        .concert-title { font-size: 1.25rem; font-weight: 800; margin-bottom: 5px; }
        .info-row { display: flex; justify-content: space-between; margin-bottom: 10px; }
        .info-group label { display: block; font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700; margin-bottom: 2px; }
        .info-group span { font-weight: 700; font-size: 0.9rem; }

        /* Status Badges */
        .status-badge {
            display: inline-block; padding: 4px 12px; border-radius: 50px;
            font-size: 0.7rem; font-weight: 800; text-transform: uppercase; margin-bottom: 15px;
        }
        .status-paid { background: rgba(34, 197, 94, 0.1); color: #22c55e; }
        .status-pending { background: rgba(234, 179, 8, 0.1); color: #eab308; }
        .status-cancelled { background: rgba(239, 68, 68, 0.1); color: #ef4444; }

        /* Adjusted View Receipt Button with Right Arrow Logic */
        .btn-receipt {
            margin-top: 15px;
            width: 100%;
            padding: 14px;
            border-radius: 12px;
            border: 1px solid var(--glass-border);
            background: var(--bg-body);
            color: var(--text-main);
            font-weight: 700;
            font-size: 0.85rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .btn-receipt:hover {
            background: var(--glass-border);
            transform: translateY(-2px);
        }

        .btn-receipt:hover .arrow-icon {
            transform: translateX(5px);
        }

        .btn-receipt:active {
            transform: scale(0.98);
        }

        .arrow-icon {
            font-size: 18px;
            transition: transform 0.2s ease;
        }

        /* Modal Styling */
        .modal-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.8); backdrop-filter: blur(8px);
            z-index: 2000; display: none; align-items: center; justify-content: center;
        }
        .modal-card {
            background: var(--surface); width: 95%; max-width: 450px;
            border-radius: 28px; padding: 40px; border: 1px solid var(--glass-border);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
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
        <p style="color: var(--text-muted);">Manage your bookings and view your transaction history.</p>
    </div>

    <div class="grid">
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): 
                $img_path = !empty($row['image']) ? "assets/images/concerts/".$row['image'] : "assets/images/Concert.png";
                $status = strtolower($row['status'] ?? 'pending');
                $status_label = ucfirst($status);
                $status_class = "status-" . $status;
            ?>
                <div class="ticket-card">
                    <img src="<?php echo $root . $img_path; ?>" class="ticket-visual" alt="Concert Cover">
                    
                    <div class="ticket-body">
                        <div class="status-badge <?php echo $status_class; ?>">
                            <?php echo $status_label; ?>
                        </div>

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
                                <label>Tier</label>
                                <span><?php echo htmlspecialchars($row['zone_name']); ?></span>
                            </div>
                        </div>

                        <button class="btn-receipt" 
                                onclick='showReceipt(<?php echo json_encode([
                                    "event" => $row["concert_name"],
                                    "date" => date("F d, Y", strtotime($row["concert_date"])),
                                    "venue" => $row["venue"],
                                    "tier" => $row["zone_name"],
                                    "qty" => $row["quantity"],
                                    "unit" => number_format($row["unit_price"], 2),
                                    "total" => number_format($row["total_price"], 2),
                                    "ref" => $row["payment_reference"],
                                    "status" => $status_label
                                ]); ?>)'>
                            <span>View Receipt</span>
                            <span class="material-symbols-outlined arrow-icon">arrow_forward</span>
                        </button>
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

<div class="modal-overlay" id="receiptModal" onclick="closeReceipt()">
    <div class="modal-card" onclick="event.stopPropagation()">
        <div style="text-align: center; margin-bottom: 30px;">
            <span class="material-symbols-outlined" style="font-size: 3rem; color: var(--accent-primary);">receipt_long</span>
            <h2 style="margin-top: 10px; font-weight: 800;">Receipt Details</h2>
            <p id="rc-status" style="font-weight: 800; font-size: 0.75rem; text-transform: uppercase; margin-top: 5px;"></p>
        </div>

        <div style="display: flex; flex-direction: column; gap: 15px;">
             <div style="display:flex; justify-content: space-between;"><span style="color:var(--text-muted)">Event</span><span id="rc-event" style="font-weight:700"></span></div>
             <div style="display:flex; justify-content: space-between;"><span style="color:var(--text-muted)">Venue</span><span id="rc-venue" style="font-weight:700"></span></div>
             <div style="display:flex; justify-content: space-between;"><span style="color:var(--text-muted)">Tier</span><span id="rc-tier" style="font-weight:700"></span></div>
             <div style="display:flex; justify-content: space-between;"><span style="color:var(--text-muted)">Qty</span><span id="rc-qty" style="font-weight:700"></span></div>
             <div style="display:flex; justify-content: space-between; border-top: 1px solid var(--glass-border); padding-top: 15px;">
                <span style="font-weight:800">Total Paid</span>
                <span style="font-weight:800; color:var(--accent-primary)">₱<span id="rc-total"></span></span>
             </div>
             <div style="margin-top: 15px; padding: 15px; background: var(--bg-body); border-radius: 12px; text-align: center;">
                <p style="font-size: 0.7rem; color: var(--text-muted); font-weight: 700; text-transform: uppercase;">Payment Reference</p>
                <p id="rc-ref" style="font-family: monospace; font-weight: 700; letter-spacing: 1px;"></p>
             </div>
        </div>

        <button class="btn-receipt" style="background: var(--accent-gradient); color: white !important; border: none; margin-top: 30px;" onclick="closeReceipt()">
            Close Receipt
        </button>
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

    function showReceipt(data) {
        document.getElementById('rc-event').innerText = data.event;
        document.getElementById('rc-venue').innerText = data.venue;
        document.getElementById('rc-tier').innerText = data.tier;
        document.getElementById('rc-qty').innerText = data.qty;
        document.getElementById('rc-total').innerText = data.total;
        document.getElementById('rc-ref').innerText = data.ref;
        document.getElementById('rc-status').innerText = data.status;
        
        const statusEl = document.getElementById('rc-status');
        statusEl.style.color = data.status === 'Paid' ? '#22c55e' : (data.status === 'Pending' ? '#eab308' : '#ef4444');
        document.getElementById('receiptModal').style.display = 'flex';
    }

    function closeReceipt() {
        document.getElementById('receiptModal').style.display = 'none';
    }
</script>

</body>
</html>