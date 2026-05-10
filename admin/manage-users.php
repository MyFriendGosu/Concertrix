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

// Handle User Deletion
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    // Prevent admin from deleting themselves
    if ($id !== $_SESSION['user_id']) {
        $conn->query("DELETE FROM users WHERE id = $id");
    }
    header("Location: manage-users.php");
    exit;
}

$sql = "SELECT id, fullname, email, role, created_at FROM users ORDER BY created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users | Concertix Admin</title>
    
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
            --danger: #ef4444;
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

        /* Navigation Bar */
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

        /* Theme Toggle */
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

        /* --- UPDATED LOGOUT BUTTON (MATCHING DASHBOARD) --- */
        .btn-logout {
            background: var(--accent-gradient);
            color: #ffffff !important;
            padding: 10px 24px;
            border-radius: 12px;
            border: none;
            font-weight: 800 !important;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-size: 0.8rem;
            transition: 0.3s all ease !important;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .btn-logout:hover {
            transform: translateY(-2px);
            filter: brightness(1.1);
            box-shadow: 0 8px 20px rgba(129, 140, 248, 0.3);
        }

        .admin-container { padding: 40px 8%; }
        .action-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }

        /* Table Styling */
        .table-wrapper { background: var(--surface); border: 1px solid var(--glass-border); border-radius: 24px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
        .admin-table { width: 100%; border-collapse: collapse; text-align: left; }
        .admin-table th { background: rgba(255,255,255,0.02); padding: 20px; font-size: 0.7rem; text-transform: uppercase; color: var(--text-muted); font-weight: 800; border-bottom: 1px solid var(--glass-border); }
        .admin-table td { padding: 20px; border-bottom: 1px solid var(--glass-border); font-size: 0.9rem; vertical-align: middle; }

        /* Unified Role Badge */
        .role-pill {
            display: inline-flex; align-items: center; justify-content: center;
            width: 85px; height: 26px; border-radius: 50px;
            font-size: 0.65rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px;
            background: rgba(255, 255, 255, 0.05); border: 1px solid var(--glass-border);
        }
        .role-admin { background: rgba(129, 140, 248, 0.1); color: var(--accent-primary); border: 1px solid rgba(129, 140, 248, 0.2); }

        .btn-delete {
            width: 36px; height: 36px; display: flex; align-items: center; justify-content: center;
            border-radius: 10px; text-decoration: none; background: rgba(239, 68, 68, 0.05);
            border: 1px solid rgba(239, 68, 68, 0.1); color: var(--danger); transition: 0.2s;
        }
        .btn-delete:hover { background: var(--danger); color: white; transform: translateY(-2px); }
    </style>
</head>
<body class="dark">

<nav>
    <a href="dashboard.php" class="logo">
        <span class="material-symbols-outlined" style="color: var(--accent-primary)">theater_comedy</span>
        CONCERTIX ADMIN
    </a>
    <div class="nav-links">
        <a href="dashboard.php">REPORTS</a>
        <a href="manage-concerts.php">CONCERTS</a>
        <a href="manage-users.php" class="active">USERS</a>
        
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
        <div>
            <h1 style="font-size: 2rem; font-weight: 800; letter-spacing: -1px;">User Management</h1>
            <p style="color: var(--text-muted);">Manage platform access and user permissions.</p>
        </div>
    </div>

    <div class="table-wrapper">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>User Profile</th>
                    <th>Email Address</th>
                    <th>Account Role</th>
                    <th>Joined Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($user = $result->fetch_assoc()): ?>
                <tr>
                    <td>
                        <div style="font-weight: 800;"><?php echo htmlspecialchars($user['fullname']); ?></div>
                        <div style="font-size: 0.75rem; color: var(--text-muted);">ID: #<?php echo $user['id']; ?></div>
                    </td>
                    <td style="color: var(--text-muted);"><?php echo htmlspecialchars($user['email']); ?></td>
                    <td>
                        <div class="role-pill <?php echo ($user['role'] === 'admin') ? 'role-admin' : ''; ?>">
                            <?php echo $user['role']; ?>
                        </div>
                    </td>
                    <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                    <td>
                        <?php if($user['id'] != $_SESSION['user_id']): ?>
                            <a href="?delete_id=<?php echo $user['id']; ?>" class="btn-delete" title="Delete User" 
                               onclick="return confirm('Careful! This user will be permanently removed.')">
                                <span class="material-symbols-outlined" style="font-size: 18px;">delete</span>
                            </a>
                        <?php else: ?>
                            <span style="font-size: 0.7rem; color: var(--text-muted); font-weight: 800;">YOU</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    const themeToggle = document.getElementById('themeToggle');
    const body = document.body;

    // Load saved theme
    if (localStorage.getItem('theme') === 'light') body.classList.remove('dark');

    themeToggle.addEventListener('click', () => {
        body.classList.toggle('dark');
        const currentTheme = body.classList.contains('dark') ? 'dark' : 'light';
        localStorage.setItem('theme', currentTheme);
    });
</script>

</body>
</html>