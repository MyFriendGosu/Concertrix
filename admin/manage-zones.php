<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once __DIR__ . '/../config/db.php';
$root = "/concert_ticketing_system/";

// Admin Authorization Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: " . $root . "auth/login.php");
    exit;
}

$concert_id = $_GET['id'] ?? null;
if (!$concert_id) {
    header("Location: manage-concerts.php");
    exit;
}

// Fetch Concert Details for header
$stmt = $conn->prepare("SELECT concert_name FROM concerts WHERE id = ?");
$stmt->bind_param("i", $concert_id);
$stmt->execute();
$concert = $stmt->get_result()->fetch_assoc();

// Handle Add Zone
if (isset($_POST['add_zone'])) {
    $name = $_POST['zone_name'];
    $price = $_POST['price'];
    $slots = $_POST['available_slots'];

    $add_stmt = $conn->prepare("INSERT INTO seat_zones (concert_id, zone_name, price, available_slots) VALUES (?, ?, ?, ?)");
    $add_stmt->bind_param("isdi", $concert_id, $name, $price, $slots);
    $add_stmt->execute();
}

// Handle Delete Zone
if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];
    $conn->query("DELETE FROM seat_zones WHERE id = '$id'");
    header("Location: manage-zones.php?id=" . $concert_id);
}

// Fetch all zones for this concert
$zones = $conn->query("SELECT * FROM seat_zones WHERE concert_id = '$concert_id' ORDER BY price DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Zones | <?php echo htmlspecialchars($concert['concert_name']); ?></title>
    
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --bg-body: #020617;
            --surface: #0f172a;
            --accent-primary: #3b82f6;
            --accent-gradient: linear-gradient(135deg, #3b82f6 0%, #2dd4bf 100%);
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --glass-border: rgba(255, 255, 255, 0.1);
            --danger: #ef4444;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-body);
            color: var(--text-main);
            padding: 40px 8%;
        }

        .header-section { margin-bottom: 40px; }
        .header-section h1 { font-size: 2rem; font-weight: 800; letter-spacing: -1px; }
        .header-section p { color: var(--text-muted); margin-top: 5px; }

        .layout-grid {
            display: grid;
            grid-template-columns: 350px 1fr;
            gap: 30px;
            align-items: start;
        }

        /* --- Form Styling --- */
        .card {
            background: var(--surface);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.3);
        }

        .card h2 { font-size: 1.1rem; font-weight: 700; margin-bottom: 20px; color: var(--accent-primary); }

        .form-group { margin-bottom: 15px; }
        label { display: block; font-size: 0.65rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; margin-bottom: 6px; letter-spacing: 1px; }

        input {
            width: 100%;
            padding: 12px 15px;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--glass-border);
            border-radius: 10px;
            color: white;
            font-family: inherit;
            outline: none;
        }

        input:focus { border-color: var(--accent-primary); }

        .add-btn {
            width: 100%;
            padding: 12px;
            background: var(--accent-gradient);
            border: none;
            border-radius: 10px;
            color: white;
            font-weight: 700;
            cursor: pointer;
            margin-top: 10px;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
        }

        /* --- Table Styling --- */
        .table-box { overflow: hidden; border-radius: 20px; border: 1px solid var(--glass-border); background: var(--surface); }
        table { width: 100%; border-collapse: collapse; text-align: left; }
        th { padding: 15px 20px; font-size: 0.7rem; text-transform: uppercase; color: var(--text-muted); border-bottom: 1px solid var(--glass-border); background: rgba(255,255,255,0.02); }
        td { padding: 18px 20px; border-bottom: 1px solid rgba(255,255,255,0.05); }

        .price-text { font-weight: 800; color: var(--text-main); }
        .slots-text { font-weight: 600; color: var(--accent-primary); }

        .delete-btn {
            color: var(--danger);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            padding: 5px;
            border-radius: 6px;
        }
        .delete-btn:hover { background: rgba(239, 68, 68, 0.1); }

        .nav-back {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            color: var(--text-muted);
            text-decoration: none;
            font-size: 0.85rem;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

    <a href="manage-concerts.php" class="nav-back">
        <span class="material-symbols-outlined" style="font-size: 18px;">arrow_back</span> 
        Back to Concerts
    </a>

    <div class="header-section">
        <h1>Seating Zones</h1>
        <p>Managing categories for <strong><?php echo htmlspecialchars($concert['concert_name']); ?></strong></p>
    </div>

    <div class="layout-grid">
        <div class="card">
            <h2>Add New Tier</h2>
            <form method="POST">
                <div class="form-group">
                    <label>Zone Name</label>
                    <input type="text" name="zone_name" placeholder="e.g. VIP Center" required>
                </div>
                <div class="form-group">
                    <label>Price (₱)</label>
                    <input type="number" step="0.01" name="price" placeholder="0.00" required>
                </div>
                <div class="form-group">
                    <label>Initial Capacity</label>
                    <input type="number" name="available_slots" placeholder="Total seats" required>
                </div>
                <button type="submit" name="add_zone" class="add-btn">
                    <span class="material-symbols-outlined" style="font-size: 20px;">add_circle</span>
                    Create Tier
                </button>
            </form>
        </div>

        <div class="table-box">
            <table>
                <thead>
                    <tr>
                        <th>Tier Name</th>
                        <th>Price</th>
                        <th>Tickets Left</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($zones->num_rows > 0): ?>
                        <?php while($z = $zones->fetch_assoc()): ?>
                        <tr>
                            <td style="font-weight: 700;"><?php echo htmlspecialchars($z['zone_name']); ?></td>
                            <td class="price-text">₱<?php echo number_format($z['price'], 2); ?></td>
                            <td class="slots-text"><?php echo $z['available_slots']; ?></td>
                            <td>
                                <a href="?id=<?php echo $concert_id; ?>&delete_id=<?php echo $z['id']; ?>" 
                                   class="delete-btn"
                                   onclick="return confirm('Delete this tier? This may affect existing tickets.')">
                                    <span class="material-symbols-outlined">delete</span>
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" style="text-align: center; color: var(--text-muted); padding: 40px;">
                                No tiers created yet. Use the form on the left to start.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>