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

$message = "";
$status = "";

// 1. Fetch Existing Data
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM concerts WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $concert = $stmt->get_result()->fetch_assoc();

    if (!$concert) {
        header("Location: manage-concerts.php");
        exit;
    }
} else {
    header("Location: manage-concerts.php");
    exit;
}

// 2. Handle Update Logic
if (isset($_POST['update'])) {
    $name = $_POST['concert_name'];
    $date = $_POST['concert_date'];
    $time = $_POST['concert_time'];
    $venue = $_POST['venue'];
    $desc = $_POST['description'];
    
    // Image Handling
    $image_name = $concert['image']; // Default to old image
    if (!empty($_FILES['image']['name'])) {
        $target = "../assets/images/concerts/" . basename($_FILES['image']['name']);
        if (move_uploaded_file($_FILES['image']['tmp_path'], $target)) {
            $image_name = $_FILES['image']['name'];
        }
    }

    $update_stmt = $conn->prepare("UPDATE concerts SET concert_name=?, concert_date=?, concert_time=?, venue=?, description=?, image=? WHERE id=?");
    $update_stmt->bind_param("ssssssi", $name, $date, $time, $venue, $desc, $image_name, $id);
    
    if ($update_stmt->execute()) {
        $message = "Concert updated successfully!";
        $status = "success";
        // Refresh data
        $concert['concert_name'] = $name;
        $concert['concert_date'] = $date;
        $concert['concert_time'] = $time;
        $concert['venue'] = $venue;
        $concert['description'] = $desc;
        $concert['image'] = $image_name;
    } else {
        $message = "Error updating concert.";
        $status = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Concert | Admin</title>
    
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
            padding: 60px 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .edit-card {
            background: var(--surface);
            border: 1px solid var(--glass-border);
            border-radius: 24px;
            width: 100%;
            max-width: 700px;
            padding: 40px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }

        .header { margin-bottom: 30px; }
        .header h1 { font-size: 1.8rem; font-weight: 800; letter-spacing: -1px; }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .full-width { grid-column: span 2; }

        .form-group { margin-bottom: 20px; }
        label { display: block; font-size: 0.7rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; margin-bottom: 8px; letter-spacing: 1px; }

        input, textarea, select {
            width: 100%;
            padding: 14px 18px;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--glass-border);
            border-radius: 12px;
            color: white;
            font-family: inherit;
            font-size: 0.95rem;
            outline: none;
        }

        input:focus, textarea:focus { border-color: var(--accent-primary); }

        .image-preview-box {
            display: flex;
            align-items: center;
            gap: 20px;
            padding: 15px;
            background: rgba(255, 255, 255, 0.02);
            border-radius: 12px;
            margin-bottom: 20px;
        }

        .current-poster {
            width: 80px;
            height: 80px;
            border-radius: 8px;
            object-fit: cover;
            border: 1px solid var(--glass-border);
        }

        .save-btn {
            width: 100%;
            padding: 16px;
            background: var(--accent-gradient);
            border: none;
            border-radius: 12px;
            color: white;
            font-weight: 800;
            font-size: 1rem;
            cursor: pointer;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
        }

        .save-btn:hover { filter: brightness(1.1); transform: translateY(-2px); }

        .alert {
            padding: 15px; border-radius: 12px; margin-bottom: 20px; text-align: center; font-size: 0.85rem; font-weight: 600;
        }
        .alert-success { background: rgba(34, 197, 94, 0.1); color: #22c55e; border: 1px solid rgba(34, 197, 94, 0.2); }
        .alert-error { background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.2); }

        .back-nav {
            text-decoration: none;
            color: var(--text-muted);
            font-size: 0.85rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 5px;
            margin-bottom: 20px;
            width: 100%;
            max-width: 700px;
        }
    </style>
</head>
<body>

    <a href="manage-concerts.php" class="back-nav">
        <span class="material-symbols-outlined" style="font-size: 18px;">arrow_back</span> Back to Manager
    </a>

    <div class="edit-card">
        <div class="header">
            <h1>Edit Event Details</h1>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-<?php echo $status; ?>"><?php echo $message; ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="form-grid">
                <div class="form-group full-width">
                    <label>Concert Name</label>
                    <input type="text" name="concert_name" value="<?php echo htmlspecialchars($concert['concert_name']); ?>" required>
                </div>

                <div class="form-group">
                    <label>Date</label>
                    <input type="date" name="concert_date" value="<?php echo $concert['concert_date']; ?>" required>
                </div>

                <div class="form-group">
                    <label>Time</label>
                    <input type="text" name="concert_time" value="<?php echo htmlspecialchars($concert['concert_time']); ?>" placeholder="e.g. 8:00 PM" required>
                </div>

                <div class="form-group full-width">
                    <label>Venue Location</label>
                    <input type="text" name="venue" value="<?php echo htmlspecialchars($concert['venue']); ?>" required>
                </div>

                <div class="form-group full-width">
                    <label>Description</label>
                    <textarea name="description" rows="4" required><?php echo htmlspecialchars($concert['description']); ?></textarea>
                </div>

                <div class="form-group full-width">
                    <label>Update Poster Image</label>
                    <div class="image-preview-box">
                        <img src="../assets/images/concerts/<?php echo $concert['image'] ?: 'Concert.png'; ?>" class="current-poster">
                        <input type="file" name="image" accept="image/*">
                    </div>
                </div>
            </div>

            <button type="submit" name="update" class="save-btn">
                Save Changes 
                <span class="material-symbols-outlined" style="font-size: 20px;">save</span>
            </button>
        </form>
    </div>

</body>
</html>