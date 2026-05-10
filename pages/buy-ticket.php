<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once __DIR__ . '/../config/db.php';
$root = "/concert_ticketing_system/"; 

if (!isset($_GET['id']) || !isset($_GET['zone_id'])) {
    header("Location: home.php");
    exit;
}

if (!isset($_SESSION['user_id'])) {
    header("Location: " . $root . "auth/login.php");
    exit;
}

$concert_id = $_GET['id'];
$seat_zone_id = $_GET['zone_id'];

$stmt = $conn->prepare("SELECT * FROM concerts WHERE id = ?");
$stmt->bind_param("i", $concert_id);
$stmt->execute();
$concert = $stmt->get_result()->fetch_assoc();

$zone_stmt = $conn->prepare("SELECT * FROM seat_zones WHERE id = ? AND concert_id = ?");
$zone_stmt->bind_param("ii", $seat_zone_id, $concert_id);
$zone_stmt->execute();
$selected_zone = $zone_stmt->get_result()->fetch_assoc();

if (!$concert || !$selected_zone) {
    header("Location: home.php");
    exit;
}

$message = "";
$status = "";

if (isset($_POST['buy'])) {
    $quantity = (int)$_POST['quantity'];
    $payment_reference = trim($_POST['payment_reference']);

    if ($quantity > 5 || $quantity < 1) {
        $message = "Maximum of 5 tickets per transaction.";
        $status = "error";
    } 
    elseif (!preg_match('/^[0-9]{8}$/', $payment_reference)) {
        $message = "Invalid Reference! Must be exactly 8 digits (numbers only).";
        $status = "error";
    } else {
        if ($selected_zone['available_slots'] < $quantity) {
            $message = "Sorry, only " . $selected_zone['available_slots'] . " tickets left for this tier.";
            $status = "error";
        } else {
            $total = $selected_zone['price'] * $quantity;
            $user_id = $_SESSION['user_id'];

            $booking_stmt = $conn->prepare("INSERT INTO bookings (user_id, concert_id, seat_zone_id, quantity, payment_reference, total_price, status) VALUES (?, ?, ?, ?, ?, ?, 'Paid')");
            $booking_stmt->bind_param("iiiisd", $user_id, $concert_id, $seat_zone_id, $quantity, $payment_reference, $total);
            
            if ($booking_stmt->execute()) {
                $conn->query("UPDATE seat_zones SET available_slots = available_slots - $quantity WHERE id='$seat_zone_id'");
                $message = "Purchase successful! Redirecting to your tickets...";
                $status = "success";
            } else {
                $message = "Database error. Please try again.";
                $status = "error";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout | <?php echo htmlspecialchars($concert['concert_name']); ?></title>
    
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
        }

        * { margin: 0; padding: 0; box-sizing: border-box; transition: all 0.3s ease; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: var(--bg-body); color: var(--text-main); min-height: 100vh; display: flex; flex-direction: column; }
        
        nav { display: flex; justify-content: space-between; align-items: center; padding: 0 8%; background: rgba(2, 6, 23, 0.8); backdrop-filter: blur(12px); position: sticky; top: 0; z-index: 1000; height: 70px; border-bottom: 1px solid var(--glass-border); }
        .logo { font-weight: 800; font-size: 1.2rem; display: flex; align-items: center; gap: 8px; color: var(--text-main); text-decoration: none; }
        
        .container { flex: 1; display: flex; justify-content: center; align-items: center; padding: 40px 20px; }
        
        .checkout-card { background: var(--surface); border: 1px solid var(--glass-border); border-radius: 24px; width: 100%; max-width: 480px; padding: 40px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5); }
        .summary-box { background: rgba(59, 130, 246, 0.1); border: 1px solid rgba(59, 130, 246, 0.2); border-radius: 16px; padding: 20px; margin-bottom: 30px; }
        .summary-title { font-size: 0.75rem; font-weight: 800; color: var(--accent-primary); text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 10px; font-family: 'Plus Jakarta Sans', sans-serif; }
        .summary-info { font-weight: 700; font-size: 1.1rem; }
        
        .form-group { margin-bottom: 20px; }
        label { display: block; font-size: 0.7rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; margin-bottom: 8px; letter-spacing: 1.5px; font-family: 'Plus Jakarta Sans', sans-serif; }

        input {
            width: 100%; padding: 14px 18px; background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--glass-border); border-radius: 12px; color: white;
            font-family: inherit; font-size: 0.95rem; outline: none;
        }

        input:focus { border-color: var(--accent-primary); background: rgba(255, 255, 255, 0.05); }

        /* ENHANCED: Purchase Button Style */
        .purchase-btn {
            width: 100%; padding: 16px; 
            background: var(--accent-gradient); border: none;
            border-radius: 12px; color: white; 
            font-family: 'Plus Jakarta Sans', sans-serif; /* Explicit family */
            font-weight: 800; /* Matching label weight */
            font-size: 0.85rem;
            text-transform: uppercase; 
            letter-spacing: 2px; /* Enhanced tracking for modern feel */
            cursor: pointer; margin-top: 10px; 
            display: flex; justify-content: center;
            align-items: center; gap: 10px;
            box-shadow: 0 10px 20px -5px rgba(59, 130, 246, 0.4);
            transition: 0.3s all ease;
        }

        .purchase-btn:hover { transform: translateY(-2px); filter: brightness(1.1); box-shadow: 0 15px 30px -5px rgba(59, 130, 246, 0.5); }
        
        .alert { padding: 15px; border-radius: 12px; margin-bottom: 20px; text-align: center; font-size: 0.85rem; font-weight: 600; }
        .alert-error { background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.2); }
        .alert-success { background: rgba(34, 197, 94, 0.1); color: #22c55e; border: 1px solid rgba(34, 197, 94, 0.2); }
    </style>
</head>
<body>

<nav>
    <a href="home.php" class="logo">
        <span class="material-symbols-outlined" style="color: var(--accent-primary)">theater_comedy</span>
        CONCERTIX
    </a>
</nav>

<main class="container">
    <div class="checkout-card">
        <div style="text-align: center; margin-bottom: 25px;">
            <h2 style="font-size: 1.8rem; font-weight: 800; letter-spacing: -1px;">Secure Checkout</h2>
            <p style="color: var(--text-muted); font-size: 0.9rem; margin-top: 5px;"><?php echo htmlspecialchars($concert['concert_name']); ?></p>
        </div>

        <div class="summary-box">
            <p class="summary-title">Selected Tier</p>
            <p class="summary-info"><?php echo htmlspecialchars($selected_zone['zone_name']); ?></p>
            <p style="font-size: 0.9rem; color: var(--text-muted); margin-top: 5px;">₱<?php echo number_format($selected_zone['price'], 2); ?> per ticket</p>
        </div>

        <?php if ($status === "success"): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
            <script>setTimeout(() => { window.location.href = 'my-tickets.php'; }, 2000);</script>
        <?php elseif ($status === "error"): ?>
            <div class="alert alert-error"><?php echo $message; ?></div>
        <?php endif; ?>

        <form method="POST" id="purchaseForm">
            <div class="form-group">
                <label>Ticket Quantity</label>
                <input type="number" name="quantity" min="1" max="5" value="1" required>
            </div>

            <div class="form-group">
                <label>8-Digit Transaction Reference</label>
                <input type="text" 
                       name="payment_reference" 
                       pattern="\d{8}" 
                       maxlength="8" 
                       oninput="this.value = this.value.replace(/[^0-9]/g, '').substring(0, 8);" 
                       placeholder="Enter exactly 8 numbers" 
                       required>
                <p style="font-size: 0.65rem; color: var(--text-muted); margin-top: 5px;">Numbers only. Must be exactly 8 digits.</p>
            </div>

            <button type="submit" name="buy" class="purchase-btn">
                Confirm Purchase 
                <span class="material-symbols-outlined" style="font-size: 20px;">lock</span>
            </button>
        </form>
        
        <a href="concert-details.php?id=<?php echo $concert_id; ?>" style="display: block; text-align: center; margin-top: 20px; color: var(--text-muted); text-decoration: none; font-size: 0.8rem; font-weight: 700;">Cancel Transaction</a>
    </div>
</main>

<script>
    document.querySelector('input[name="payment_reference"]').addEventListener('keypress', function (e) {
        if (!/[0-9]/.test(e.key)) {
            e.preventDefault();
        }
    });
</script>

</body>
</html>