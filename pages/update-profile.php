<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once __DIR__ . '/../config/db.php';
$root = "/concert_ticketing_system/";

// Auth Check
if (!isset($_SESSION['user_id'])) {
    header("Location: " . $root . "auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$message = "";
$status = "";

// 1. Fetch Current User Data
$stmt = $conn->prepare("SELECT fullname, email FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// 2. Handle Update Logic
if (isset($_POST['update_profile'])) {
    $new_name = $_POST['fullname'];
    $new_email = $_POST['email'];
    $new_password = $_POST['new_password'];

    // Update Basic Info
    $upd_stmt = $conn->prepare("UPDATE users SET fullname = ?, email = ? WHERE id = ?");
    $upd_stmt->bind_param("ssi", $new_name, $new_email, $user_id);
    
    if ($upd_stmt->execute()) {
        $_SESSION['fullname'] = $new_name; // Sync session
        $message = "Profile updated successfully!";
        $status = "success";
        
        // Handle Password Change if provided
        if (!empty($new_password)) {
            $hashed_pass = password_hash($new_password, PASSWORD_DEFAULT);
            $pass_stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $pass_stmt->bind_param("si", $hashed_pass, $user_id);
            $pass_stmt->execute();
            $message .= " Password also changed.";
        }
        
        // Refresh local data
        $user['fullname'] = $new_name;
        $user['email'] = $new_email;
    } else {
        $message = "Update failed. Email might already be in use.";
        $status = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Settings | Concertix</title>
    
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

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-body);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        nav {
            display: flex; justify-content: space-between; align-items: center;
            padding: 0 8%; background: rgba(2, 6, 23, 0.8); backdrop-filter: blur(12px);
            height: 70px; border-bottom: 1px solid var(--glass-border);
        }

        .logo { font-weight: 800; font-size: 1.2rem; display: flex; align-items: center; gap: 8px; color: var(--text-main); text-decoration: none; }

        .container {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 40px 20px;
        }

        .settings-card {
            background: var(--surface);
            border: 1px solid var(--glass-border);
            border-radius: 24px;
            width: 100%;
            max-width: 450px;
            padding: 40px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }

        .header { text-align: center; margin-bottom: 30px; }
        .header h2 { font-size: 1.6rem; font-weight: 800; letter-spacing: -1px; }

        .form-group { margin-bottom: 20px; }
        label { display: block; font-size: 0.7rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; margin-bottom: 8px; letter-spacing: 1px; }

        input {
            width: 100%;
            padding: 12px 16px;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--glass-border);
            border-radius: 12px;
            color: white;
            font-family: inherit;
            font-size: 0.95rem;
            outline: none;
        }

        input:focus { border-color: var(--accent-primary); }

        .update-btn {
            width: 100%;
            padding: 14px;
            background: var(--accent-gradient);
            border: none;
            border-radius: 12px;
            color: white;
            font-weight: 800;
            font-size: 1rem;
            cursor: pointer;
            margin-top: 10px;
        }

        .update-btn:hover { filter: brightness(1.1); transform: translateY(-1px); }

        .alert {
            padding: 12px; border-radius: 10px; margin-bottom: 20px; text-align: center; font-size: 0.85rem; font-weight: 600;
        }
        .alert-success { background: rgba(34, 197, 94, 0.1); color: #22c55e; border: 1px solid rgba(34, 197, 94, 0.2); }
        .alert-error { background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.2); }

        .footer-links {
            display: flex; justify-content: center; gap: 20px; margin-top: 25px;
        }
        .footer-links a { color: var(--text-muted); text-decoration: none; font-size: 0.8rem; font-weight: 700; }
        .footer-links a:hover { color: var(--text-main); }
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
    <div class="settings-card">
        <div class="header">
            <h2>Account Settings</h2>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-<?php echo $status; ?>"><?php echo $message; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="fullname" value="<?php echo htmlspecialchars($user['fullname']); ?>" required>
            </div>

            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            </div>

            <hr style="border: 0; border-top: 1px solid var(--glass-border); margin: 25px 0;">

            <div class="form-group">
                <label>New Password (Leave blank to keep current)</label>
                <input type="password" name="new_password" placeholder="••••••••">
            </div>

            <button type="submit" name="update_profile" class="update-btn">Save Changes</button>
        </form>

        <div class="footer-links">
            <a href="home.php">Back to Home</a>
            <span style="color: var(--glass-border);">|</span>
            <a href="<?php echo $root; ?>auth/logout.php" style="color: #ef4444;">Logout</a>
        </div>
    </div>
</main>

</body>
</html>