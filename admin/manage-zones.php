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

// Handle Delete
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    $conn->query("DELETE FROM concerts WHERE id = $id");
    header("Location: manage-concerts.php");
    exit;
}

// Updated Query: You might want to consider how you define "status" at the concert level.
// If concerts also have a status column, we use that. 
$sql = "SELECT * FROM concerts ORDER BY concert_date DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Events | Concertix Admin</title>
    
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
            --danger: #ef4444;
            --success: #22c55e;
            --warning: #fbbf24;
            --info: #3b82f6;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; transition: background-color 0.3s ease, color 0.3s ease; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: var(--bg-body); color: var(--text-main); }

        nav { display: flex; justify-content: space-between; align-items: center; padding: 0 8%; background: rgba(2, 6, 23, 0.8); backdrop-filter: blur(12px); position: sticky; top: 0; z-index: 1000; border-bottom: 1px solid var(--glass-border); height: 80px; }
        .logo { font-weight: 800; font-size: 1.4rem; display: flex; align-items: center; gap: 10px; color: var(--text-main); text-decoration: none; }
        
        .admin-container { padding: 40px 8%; }
        .action-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        
        .table-wrapper { background: var(--surface); border: 1px solid var(--glass-border); border-radius: 24px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.3); }
        .admin-table { width: 100%; border-collapse: collapse; text-align: left; }
        .admin-table th { background: rgba(255,255,255,0.02); padding: 20px; font-size: 0.7rem; text-transform: uppercase; color: var(--text-muted); font-weight: 800; border-bottom: 1px solid var(--glass-border); }
        .admin-table td { padding: 20px; border-bottom: 1px solid rgba(255,255,255,0.05); font-size: 0.9rem; vertical-align: middle; }

        /* --- FIXED: Standardized Status Badges --- */
        .status-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 85px;         
            height: 26px;        
            padding: 0;          
            border-radius: 50px;
            font-size: 0.65rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Logic for DB 'active' status */
        .status-active { 
            background: rgba(34, 197, 94, 0.1); 
            color: var(--success); 
            border: 1px solid rgba(34, 197, 94, 0.2); 
        }

        /* Logic for DB 'inactive' or 'expired' status */
        .status-inactive { 
            background: rgba(239, 68, 68, 0.1); 
            color: var(--danger); 
            border: 1px solid rgba(239, 68, 68, 0.2); 
        }

        .action-btn-group { display: flex; gap: 8px; align-items: center; }
        .btn-icon {
            width: 36px; height: 36px; display: flex; align-items: center; justify-content: center;
            border-radius: 10px; text-decoration: none; background: rgba(255,255,255,0.03);
            border: 1px solid var(--glass-border); color: var(--text-main); transition: 0.2s;
        }
        .btn-icon:hover { transform: translateY(-2px); background: rgba(255,255,255,0.1); }
        
        .btn-add { background: var(--accent-gradient); color: white; padding: 12px 24px; border-radius: 12px; text-decoration: none; font-weight: 700; display: flex; align-items: center; gap: 8px; }
    </style>
</head>
<body>

<nav>
    <a href="dashboard.php" class="logo">
        <span class="material-symbols-outlined" style="color: var(--accent-primary)">theater_comedy</span>
        CONCERTIX ADMIN
    </a>
</nav>

<div class="admin-container">
    <div class="action-bar">
        <div>
            <h1 style="font-size: 2rem; font-weight: 800; letter-spacing: -1px;">Event Management</h1>
            <p style="color: var(--text-muted);">View concert status and seat availability.</p>
        </div>
        <a href="add-concert.php" class="btn-add">
            <span class="material-symbols-outlined">add_circle</span> New Event
        </a>
    </div>

    <div class="table-wrapper">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Event Details</th>
                    <th>Date</th>
                    <th>Venue</th>
                    <th>Availability Status</th> <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $result->fetch_assoc()): 
                    // Fetch if any zone is active for this concert to determine overall status
                    $cid = $row['id'];
                    $status_check = $conn->query("SELECT status FROM seat_zones WHERE concert_id = $cid LIMIT 1");
                    $status_row = $status_check->fetch_assoc();
                    $db_status = $status_row['status'] ?? 'inactive'; 

                    $img = !empty($row['image']) ? "../assets/images/concerts/".$row['image'] : "../assets/images/Concert.png";
                ?>
                <tr>
                    <td>
                        <div style="display: flex; align-items: center; gap: 15px;">
                            <img src="<?php echo $img; ?>" style="width: 45px; height: 45px; border-radius: 10px; object-fit: cover; border: 1px solid var(--glass-border);">
                            <div>
                                <div style="font-weight: 800;"><?php echo htmlspecialchars($row['concert_name']); ?></div>
                                <div style="font-size: 0.75rem; color: var(--text-muted);">ID: #<?php echo $row['id']; ?></div>
                            </div>
                        </div>
                    </td>
                    <td><?php echo date('M d, Y', strtotime($row['concert_date'])); ?></td>
                    <td style="color: var(--text-muted);"><?php echo htmlspecialchars($row['venue']); ?></td>
                    <td>
                        <div class="status-pill <?php echo ($db_status === 'active') ? 'status-active' : 'status-inactive'; ?>">
                            <?php echo ucfirst($db_status); ?>
                        </div>
                    </td>
                    <td>
                        <div class="action-btn-group">
                            <a href="manage-zones.php?id=<?php echo $row['id']; ?>" class="btn-icon" style="color: var(--accent-primary);" title="Manage Tiers">
                                <span class="material-symbols-outlined">event_seat</span>
                            </a>
                            <a href="edit-concert.php?id=<?php echo $row['id']; ?>" class="btn-icon" style="color: var(--warning);" title="Edit Info">
                                <span class="material-symbols-outlined">edit</span>
                            </a>
                            <a href="?delete_id=<?php echo $row['id']; ?>" class="btn-icon" style="color: var(--danger);" title="Delete" onclick="return confirm('Careful! This will remove the event and all bookings.')">
                                <span class="material-symbols-outlined">delete</span>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>