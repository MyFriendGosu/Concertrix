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

// Handle Role Toggle (Promotion/Demotion)
if (isset($_GET['toggle_role_id'])) {
    $id = $_GET['toggle_role_id'];
    $current_role = $_GET['current'];
    $new_role = ($current_role === 'admin') ? 'user' : 'admin';
    
    $stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
    $stmt->bind_param("si", $new_role, $id);
    $stmt->execute();
    header("Location: manage-users.php");
}

// Handle Delete Request
if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];
    // Prevent admin from deleting themselves
    if ($id != $_SESSION['user_id']) {
        $conn->query("DELETE FROM users WHERE id = '$id'");
    }
    header("Location: manage-users.php");
}

$query = "SELECT id, fullname, email, role, created_at FROM users ORDER BY role ASC, fullname ASC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users | Admin</title>
    
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --bg-body: #020617;
            --surface: #0f172a;
            --accent-primary: #3b82f6;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --glass-border: rgba(255, 255, 255, 0.1);
            --danger: #ef4444;
            --success: #22c55e;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-body);
            color: var(--text-main);
            padding: 40px 8%;
        }

        .admin-header {
            margin-bottom: 40px;
        }

        .admin-header h1 { font-size: 2rem; font-weight: 800; letter-spacing: -1px; }

        .table-container {
            background: var(--surface);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.3);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        th {
            background: rgba(255, 255, 255, 0.03);
            padding: 20px;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--text-muted);
            border-bottom: 1px solid var(--glass-border);
        }

        td {
            padding: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            font-size: 0.9rem;
        }

        tr:hover { background: rgba(255, 255, 255, 0.02); }

        .user-cell { display: flex; align-items: center; gap: 12px; }
        .avatar-circle {
            width: 35px; height: 35px;
            background: var(--accent-primary);
            color: white;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-weight: 800; font-size: 0.8rem;
        }

        .role-badge {
            padding: 4px 10px;
            border-radius: 50px;
            font-size: 0.7rem;
            font-weight: 800;
            text-transform: uppercase;
        }
        .role-admin { background: rgba(59, 130, 246, 0.1); color: var(--accent-primary); border: 1px solid rgba(59, 130, 246, 0.2); }
        .role-user { background: rgba(148, 163, 184, 0.1); color: var(--text-muted); border: 1px solid rgba(148, 163, 184, 0.2); }

        .actions { display: flex; gap: 10px; }
        .action-link {
            text-decoration: none;
            color: var(--text-muted);
            padding: 8px;
            border-radius: 8px;
            border: 1px solid var(--glass-border);
            display: flex; align-items: center;
        }
        .action-link:hover { background: rgba(255, 255, 255, 0.1); color: var(--text-main); }
        .delete-btn:hover { background: var(--danger); color: white; border-color: var(--danger); }

        .nav-back {
            display: inline-flex; align-items: center; gap: 5px;
            color: var(--text-muted); text-decoration: none;
            font-size: 0.85rem; margin-bottom: 20px;
        }
    </style>
</head>
<body>

    <a href="dashboard.php" class="nav-back">
        <span class="material-symbols-outlined" style="font-size: 18px;">arrow_back</span> 
        Back to Dashboard
    </a>

    <div class="admin-header">
        <h1>User Management</h1>
        <p style="color: var(--text-muted);">Manage permissions and monitor user accounts.</p>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>User</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Joined Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $result->fetch_assoc()): 
                    $initial = strtoupper(substr($row['fullname'], 0, 1));
                    $is_current_user = ($row['id'] == $_SESSION['user_id']);
                ?>
                <tr>
                    <td>
                        <div class="user-cell">
                            <div class="avatar-circle"><?php echo $initial; ?></div>
                            <div style="font-weight: 700;">
                                <?php echo htmlspecialchars($row['fullname']); ?>
                                <?php if($is_current_user) echo " <small style='color:var(--accent-primary)'>(You)</small>"; ?>
                            </div>
                        </div>
                    </td>
                    <td style="color: var(--text-muted);"><?php echo htmlspecialchars($row['email']); ?></td>
                    <td>
                        <span class="role-badge <?php echo ($row['role'] === 'admin') ? 'role-admin' : 'role-user'; ?>">
                            <?php echo $row['role']; ?>
                        </span>
                    </td>
                    <td style="color: var(--text-muted); font-size: 0.8rem;">
                        <?php echo date('M d, Y', strtotime($row['created_at'])); ?>
                    </td>
                    <td>
                        <div class="actions">
                            <a href="?toggle_role_id=<?php echo $row['id']; ?>&current=<?php echo $row['role']; ?>" 
                               class="action-link" title="Change Role">
                                <span class="material-symbols-outlined" style="font-size: 18px;">shield_person</span>
                            </a>

                            <?php if(!$is_current_user): ?>
                            <a href="?delete_id=<?php echo $row['id']; ?>" 
                               class="action-link delete-btn" 
                               title="Delete User"
                               onclick="return confirm('Permanently delete this user?');">
                                <span class="material-symbols-outlined" style="font-size: 18px;">person_remove</span>
                            </a>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

</body>
</html>