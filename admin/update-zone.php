<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once __DIR__ . '/../config/db.php';
$root = "/concert_ticketing_system/";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: " . $root . "auth/login.php");
    exit;
}

$zone_id = $_GET['id'] ?? null;
if (!$zone_id) {
    header("Location: manage-concerts.php");
    exit;
}

$stmt = $conn->prepare("SELECT sz.*, c.concert_name FROM seat_zones sz JOIN concerts c ON sz.concert_id = c.id WHERE sz.id = ?");
$stmt->bind_param("i", $zone_id);
$stmt->execute();
$zone = $stmt->get_result()->fetch_assoc();

if (!$zone) {
    header("Location: manage-concerts.php");
    exit;
}

$message = "";
$status = "";

if (isset($_POST['update_zone'])) {
    $name = $_POST['zone_name'];
    $price = $_POST['price'];
    $slots = $_POST['available_slots'];

    $update_stmt = $conn->prepare("UPDATE seat_zones SET zone_name = ?, price = ?, available_slots = ? WHERE id = ?");
    $update_stmt->bind_param("sdii", $name, $price, $slots, $zone_id);

    if ($update_stmt->execute()) {
        $message = "Tier updated successfully!";
        $status = "success";
        $zone['zone_name'] = $name;
        $zone['price'] = $price;
        $zone['available_slots'] = $slots;
    } else {
        $message = "Error updating tier.";
        $status = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Tier | Concertix Admin</title>
    
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
            --input-fill: rgba(255, 255, 255, 0.03);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; transition: all 0.3s ease; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: var(--bg-body); color: var(--text-main); display: flex; justify-content: center; align-items: center; min-height: 100vh; padding: 20px; }

        .edit-card { background: var(--surface); border: 1px solid var(--glass-border); border-radius: 28px; width: 100%; max-width: 450px; padding: 40px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5); }
        .header { text-align: center; margin-bottom: 30px; }
        .header h1 { font-size: 1.5rem; font-weight: 800; letter-spacing: -1px; }
        .header p { color: var(--text-muted); font-size: 0.85rem; margin-top: 5px; }

        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-size: 0.7rem; font-weight: 800; text-transform: uppercase; color: var(--text-muted); margin-bottom: 8px; letter-spacing: 1.5px; font-family: 'Plus Jakarta Sans', sans-serif; }

        input, select { width: 100%; padding: 14px 18px; background: var(--input-fill); border: 1px solid var(--glass-border); border-radius: 12px; color: var(--text-main); font-family: inherit; outline: none; appearance: none; font-weight: 600; }
        select option { background-color: var(--surface); color: var(--text-main); }
        select { background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='white'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 15px center; background-size: 15px; }

        input:focus, select:focus { border-color: var(--accent-primary); background: rgba(255, 255, 255, 0.06); }
        
        /* ENHANCED: Button Font Weight and Family matched to Labels */
        .btn-update { 
            width: 100%; 
            padding: 16px; 
            background: var(--accent-gradient); 
            border: none; 
            border-radius: 12px; 
            color: white; 
            font-family: 'Plus Jakarta Sans', sans-serif; /* Explicit font family */
            font-weight: 800; /* Matching label weight */
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 2px; /* Enhanced tracking */
            cursor: pointer; 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            margin-top: 10px; 
            transition: 0.3s all ease;
        }
        .btn-update:hover { transform: translateY(-2px); box-shadow: 0 10px 15px -3px rgba(129, 140, 248, 0.3); filter: brightness(1.1); }

        .alert { padding: 12px; border-radius: 10px; margin-bottom: 20px; font-size: 0.85rem; text-align: center; font-weight: 600; }
        .alert-success { background: rgba(34, 197, 94, 0.1); color: #22c55e; border: 1px solid rgba(34, 197, 94, 0.2); }
        .alert-error { background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.2); }
        
        .back-link { display: flex; align-items: center; justify-content: center; gap: 5px; margin-top: 25px; color: var(--text-muted); text-decoration: none; font-size: 0.85rem; font-weight: 700; }
    </style>
</head>
<body class="dark">

    <div class="edit-card">
        <div class="header">
            <span class="material-symbols-outlined" style="color: var(--accent-primary); font-size: 40px;">edit_calendar</span>
            <h1>Edit Seating Tier</h1>
            <p><?php echo htmlspecialchars($zone['concert_name']); ?></p>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-<?php echo $status; ?>"><?php echo $message; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Zone Category</label>
                <select name="zone_name" id="zone_select" required>
                    <option value="VIP" <?php echo ($zone['zone_name'] == 'VIP') ? 'selected' : ''; ?>>VIP</option>
                    <option value="Lower Box" <?php echo ($zone['zone_name'] == 'Lower Box') ? 'selected' : ''; ?>>Lower Box</option>
                    <option value="Upper Box" <?php echo ($zone['zone_name'] == 'Upper Box') ? 'selected' : ''; ?>>Upper Box</option>
                    <option value="General Admission" <?php echo ($zone['zone_name'] == 'General Admission') ? 'selected' : ''; ?>>General Admission</option>
                </select>
            </div>

            <div class="form-group">
                <label>Price (₱)</label>
                <input type="number" step="0.01" name="price" id="price_input" value="<?php echo $zone['price']; ?>" required>
            </div>

            <div class="form-group">
                <label>Current Tickets Left</label>
                <input type="number" name="available_slots" id="slots_input" value="<?php echo $zone['available_slots']; ?>" required>
            </div>

            <button type="submit" name="update_zone" class="btn-update">
                Save Changes
            </button>
        </form>

        <a href="manage-concerts.php" class="back-link">
            <span class="material-symbols-outlined" style="font-size: 18px;">arrow_back</span>
            Back to Concerts
        </a>
    </div>

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
    </script>
</body>
</html>