<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once __DIR__ . '/../config/db.php';

$root = "/concert_ticketing_system/";

// Security: Ensure only admins can access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: " . $root . "index.php");
    exit;
}

$concerts = $conn->query("SELECT * FROM concerts ORDER BY concert_name ASC");
$message = "";

if (isset($_POST['add_zone'])) {
    $concert_id = $_POST['concert_id'];
    $zone_name = $_POST['zone_name'];
    $price = $_POST['price'];
    $slots = $_POST['slots'];

    $stmt = $conn->prepare(
        "INSERT INTO seat_zones (concert_id, zone_name, price, available_slots, status)
         VALUES (?, ?, ?, ?, 'active')"
    );

    $stmt->bind_param("isdi", $concert_id, $zone_name, $price, $slots);

    if ($stmt->execute()) {
        $message = "Seat zone added successfully!";
    } else {
        $message = "Error: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Seat Zone | Concertix Admin</title>
    
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --bg-body: #020617;
            --surface: #0f172a;
            --accent-primary: #818cf8;
            --accent-gradient: linear-gradient(135deg, #818cf8 0%, #c084fc 100%);
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --glass-border: rgba(255, 255, 255, 0.1);
            --nav-bg: rgba(2, 6, 23, 0.8);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; transition: all 0.2s ease; }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-body);
            color: var(--text-main);
            min-height: 100vh;
        }

        nav {
            display: flex; justify-content: space-between; align-items: center;
            padding: 0 8%; background: var(--nav-bg); backdrop-filter: blur(12px);
            position: sticky; top: 0; z-index: 1000; height: 80px;
            border-bottom: 1px solid var(--glass-border);
        }

        .logo { font-weight: 800; font-size: 1.4rem; display: flex; align-items: center; gap: 10px; color: var(--text-main); text-decoration: none; }
        
        .container { padding: 60px 5%; display: flex; justify-content: center; }

        .form-card {
            background: var(--surface);
            border: 1px solid var(--glass-border);
            border-radius: 24px;
            padding: 40px;
            width: 100%;
            max-width: 550px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }

        .form-header { margin-bottom: 30px; text-align: center; }
        .form-header h2 { font-size: 1.8rem; font-weight: 800; letter-spacing: -0.5px; }
        .form-header p { color: var(--text-muted); font-size: 0.9rem; margin-top: 5px; }

        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-size: 0.7rem; font-weight: 800; text-transform: uppercase; color: var(--text-muted); margin-bottom: 8px; letter-spacing: 1.5px; font-family: 'Plus Jakarta Sans', sans-serif; }

        input, select {
            width: 100%; padding: 14px 18px; border-radius: 12px;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--glass-border);
            color: var(--text-main); font-family: inherit; font-weight: 600; font-size: 0.95rem;
            appearance: none; outline: none;
        }

        select option {
            background-color: var(--surface);
            color: white;
        }

        select {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='white'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 15px center;
            background-size: 18px;
        }

        input:focus, select:focus { border-color: var(--accent-primary); background: rgba(255, 255, 255, 0.06); }

        /* ENHANCED: Button Font Family & Weight synced with Labels */
        .btn-submit {
            width: 100%; padding: 16px; border-radius: 12px; border: none;
            background: var(--accent-gradient); color: white;
            font-family: 'Plus Jakarta Sans', sans-serif; /* Explicit font family */
            font-weight: 800; /* Matching label weight */
            font-size: 0.85rem; 
            text-transform: uppercase; 
            letter-spacing: 2px; /* Enhanced tracking for modern look */
            cursor: pointer; 
            margin-top: 10px; 
            transition: 0.3s all ease;
        }

        .btn-submit:hover { transform: translateY(-2px); filter: brightness(1.1); box-shadow: 0 10px 15px -3px rgba(129, 140, 248, 0.3); }

        .alert { padding: 15px; border-radius: 12px; margin-bottom: 25px; font-weight: 700; font-size: 0.9rem; text-align: center; }
        .alert-success { background: rgba(34, 197, 94, 0.1); color: #22c55e; border: 1px solid rgba(34, 197, 94, 0.2); }
    </style>
</head>
<body class="dark">

<nav>
    <a href="dashboard.php" class="logo">
        <span class="material-symbols-outlined" style="color: var(--accent-primary)">theater_comedy</span>
        CONCERTIX ADMIN
    </a>
    <a href="manage-concerts.php" style="text-decoration:none; color:var(--text-muted); font-weight:700; font-size:0.8rem; display:flex; align-items:center; gap:5px;">
        <span class="material-symbols-outlined" style="font-size:18px;">arrow_back</span> BACK TO CONCERTS
    </a>
</nav>

<main class="container">
    <div class="form-card">
        <div class="form-header">
            <span class="material-symbols-outlined" style="font-size: 40px; color: var(--accent-primary); margin-bottom: 10px;">confirmation_number</span>
            <h2>Add Seat Zone</h2>
            <p>Define pricing tiers and availability for specific concert areas.</p>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Target Concert</label>
                <select name="concert_id" required>
                    <option value="" disabled selected>Select an active concert</option>
                    <?php 
                    $concerts->data_seek(0); 
                    while ($c = $concerts->fetch_assoc()) { ?>
                        <option value="<?= $c['id'] ?>">
                            <?= htmlspecialchars($c['concert_name']) ?>
                        </option>
                    <?php } ?>
                </select>
            </div>

            <div class="form-group">
                <label>Zone Category</label>
                <select name="zone_name" id="zone_select" required>
                    <option value="" disabled selected>Select seat category</option>
                    <option value="VIP">VIP</option>
                    <option value="Lower Box">Lower Box</option>
                    <option value="Upper Box">Upper Box</option>
                    <option value="General Admission">General Admission</option>
                </select>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div class="form-group">
                    <label>Zone Price (₱)</label>
                    <input type="number" name="price" id="price_input" placeholder="0.00" step="0.01" required>
                </div>
                <div class="form-group">
                    <label>Total Capacity</label>
                    <input type="number" name="slots" id="slots_input" placeholder="e.g. 500" required>
                </div>
            </div>

            <button type="submit" name="add_zone" class="btn-submit">Initialize Zone</button>
        </form>
    </div>
</main>

<script>
    const tierDefaults = {
        "VIP": { price: 15000, capacity: 500 },
        "Lower Box": { price: 8500, capacity: 1500 },
        "Upper Box": { price: 4500, capacity: 1000 },
        "General Admission": { price: 1500, capacity: 2000 }
    };

    const zoneSelect = document.getElementById('zone_select');
    const priceInput = document.getElementById('price_input');
    const slotsInput = document.getElementById('slots_input');

    zoneSelect.addEventListener('change', function() {
        const selectedTier = this.value;
        if (tierDefaults[selectedTier]) {
            priceInput.value = tierDefaults[selectedTier].price;
            slotsInput.value = tierDefaults[selectedTier].capacity;
        }
    });

    if (localStorage.getItem('theme') === 'light') document.body.classList.remove('dark');
</script>
</body>
</html>