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

// Handle Delete Request
if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];
    $conn->query("DELETE FROM concerts WHERE id = '$id'");
    header("Location: manage-concerts.php");
}

$query = "SELECT c.*, 
          (SELECT SUM(available_slots) FROM seat_zones WHERE concert_id = c.id) as total_available 
          FROM concerts c ORDER BY concert_date DESC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Concerts | Admin</title>
    
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

        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
        }

        .admin-header h1 { font-size: 2rem; font-weight: 800; letter-spacing: -1px; }

        .btn-add {
            background: var(--accent-gradient);
            color: white;
            text-decoration: none;
            padding: 12px 24px;
            border-radius: 12px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.9rem;
        }

        /* --- Table Styling --- */
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

        .concert-info { display: flex; align-items: center; gap: 15px; }
        .mini-poster {
            width: 45px; height: 45px;
            border-radius: 8px;
            object-fit: cover;
            background: #000;
        }

        .status-badge {
            padding: 4px 10px;
            border-radius: 50px;
            font-size: 0.7rem;
            font-weight: 700;
            background: rgba(59, 130, 246, 0.1);
            color: var(--accent-primary);
        }

        .actions { display: flex; gap: 10px; }
        .action-btn {
            text-decoration: none;
            color: var(--text-muted);
            padding: 8px;
            border-radius: 8px;
            border: 1px solid var(--glass-border);
            display: flex;
            align-items: center;
        }

        .action-btn:hover { background: rgba(255, 255, 255, 0.1); color: var(--text-main); }
        .delete-btn:hover { background: var(--danger); color: white; border-color: var(--danger); }

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

    <a href="dashboard.php" class="nav-back">
        <span class="material-symbols-outlined" style="font-size: 18px;">arrow_back</span> 
        Back to Dashboard
    </a>

    <div class="admin-header">
        <div>
            <h1>Manage Concerts</h1>
            <p style="color: var(--text-muted);">View, edit, or remove events from the platform.</p>
        </div>
        <a href="add-concert.php" class="btn-add">
            <span class="material-symbols-outlined">add</span> Create Concert
        </a>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Concert Name</th>
                    <th>Date & Time</th>
                    <th>Venue</th>
                    <th>Tix Left</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $result->fetch_assoc()): 
                    $img = !empty($row['image']) ? "../assets/images/concerts/".$row['image'] : "../assets/images/Concert.png";
                    $date = date('M d, Y', strtotime($row['concert_date']));
                ?>
                <tr>
                    <td>
                        <div class="concert-info">
                            <img src="<?php echo $img; ?>" class="mini-poster">
                            <div>
                                <div style="font-weight: 700;"><?php echo htmlspecialchars($row['concert_name']); ?></div>
                                <div class="status-badge">Active</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div style="font-weight: 600;"><?php echo $date; ?></div>
                        <div style="color: var(--text-muted); font-size: 0.8rem;"><?php echo $row['concert_time']; ?></div>
                    </td>
                    <td style="color: var(--text-muted);"><?php echo htmlspecialchars($row['venue']); ?></td>
                    <td style="font-weight: 700; color: var(--accent-primary);">
                        <?php echo $row['total_available'] ?? '0'; ?>
                    </td>
                    <td>
                        <div class="actions">
                            <a href="edit-concert.php?id=<?php echo $row['id']; ?>" class="action-btn" title="Edit">
                                <span class="material-symbols-outlined" style="font-size: 18px;">edit</span>
                            </a>
                            <a href="manage-zones.php?id=<?php echo $row['id']; ?>" class="action-btn" title="Manage Zones">
                                <span class="material-symbols-outlined" style="font-size: 18px;">confirmation_number</span>
                            </a>
                            <a href="?delete_id=<?php echo $row['id']; ?>" 
                               class="action-btn delete-btn" 
                               title="Delete" 
                               onclick="return confirm('Delete this concert? All bookings and zones will be affected.');">
                                <span class="material-symbols-outlined" style="font-size: 18px;">delete</span>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

</body>
</html>