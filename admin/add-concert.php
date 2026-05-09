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

$message = "";

if (isset($_POST['add'])) {
    $concert_name = $_POST['concert_name'];
    $concert_date = $_POST['concert_date'];
    $concert_time = $_POST['concert_time'];
    $venue = $_POST['venue'];
    $description = $_POST['description'];
    
    // Image Upload Logic
    $image_name = "";
    if (isset($_FILES['concert_image']) && $_FILES['concert_image']['error'] === 0) {
        $ext = pathinfo($_FILES['concert_image']['name'], PATHINFO_EXTENSION);
        $image_name = time() . "_" . uniqid() . "." . $ext;
        
        // Using realpath to prevent directory navigation errors
        $target_dir = realpath(__DIR__ . "/../assets/images/concerts/");
        
        if ($target_dir) {
            $target_file = $target_dir . DIRECTORY_SEPARATOR . $image_name;
            if (!move_uploaded_file($_FILES['concert_image']['tmp_name'], $target_file)) {
                $image_name = ""; 
            }
        }
    }

    // Prepare statement: All 6 fields are now strings ('ssssss')
    $stmt = $conn->prepare(
        "INSERT INTO concerts (concert_name, concert_date, concert_time, venue, description, image)
         VALUES (?, ?, ?, ?, ?, ?)"
    );

    $stmt->bind_param("ssssss", 
        $concert_name, 
        $concert_date, 
        $concert_time, 
        $venue,
        $description,
        $image_name
    );

    if ($stmt->execute()) {
        $message = "Concert added successfully!";
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
    <title>Add Concert | Concertix Admin</title>
    
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --bg-body: #f8fafc;
            --surface: #ffffff;
            --accent-primary: #6366f1;
            --accent-gradient: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
            --text-main: #0f172a;
            --text-muted: #64748b;
            --glass-border: rgba(0, 0, 0, 0.05);
            --nav-bg: rgba(255, 255, 255, 0.8);
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

        * { margin: 0; padding: 0; box-sizing: border-box; transition: all 0.3s ease; }

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
            max-width: 600px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.05);
        }

        .form-header { margin-bottom: 30px; }
        .form-header h2 { font-size: 1.8rem; font-weight: 800; letter-spacing: -0.5px; }
        .form-header p { color: var(--text-muted); font-size: 0.9rem; margin-top: 5px; }

        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; color: var(--text-muted); margin-bottom: 8px; letter-spacing: 0.5px; }

        input, textarea {
            width: 100%; padding: 14px 18px; border-radius: 12px;
            background: var(--bg-body); border: 1px solid var(--glass-border);
            color: var(--text-main); font-family: inherit; font-weight: 600; font-size: 0.95rem;
            resize: none;
        }

        input:focus, textarea:focus { outline: none; border-color: var(--accent-primary); box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1); }

        .file-input-wrapper { position: relative; overflow: hidden; display: inline-block; width: 100%; }
        .file-custom {
            display: flex; align-items: center; justify-content: center; gap: 10px;
            padding: 14px; border: 2px dashed var(--glass-border); border-radius: 12px;
            cursor: pointer; color: var(--text-muted); font-weight: 700;
        }
        .file-custom:hover { border-color: var(--accent-primary); background: rgba(99, 102, 241, 0.05); }

        .btn-submit {
            width: 100%; padding: 16px; border-radius: 12px; border: none;
            background: var(--accent-gradient); color: white;
            font-weight: 800; font-size: 1rem; cursor: pointer;
            box-shadow: 0 10px 20px -5px rgba(99, 102, 241, 0.4);
            margin-top: 10px;
        }

        .btn-submit:hover { transform: translateY(-2px); filter: brightness(1.1); }

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
    <div style="display: flex; gap: 15px; align-items: center;">
        <a href="dashboard.php" style="text-decoration:none; color:var(--text-muted); font-weight:700; font-size:0.8rem;">BACK TO DASHBOARD</a>
    </div>
</nav>

<main class="container">
    <div class="form-card">
        <div class="form-header">
            <h2>Add New Concert</h2>
            <p>Publish an event. All fields support descriptive text.</p>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label>Concert Poster / Image</label>
                <div class="file-input-wrapper">
                    <input type="file" name="concert_image" id="concert_image" style="display: none;" onchange="updateFileName()">
                    <label for="concert_image" class="file-custom" id="file-label">
                        <span class="material-symbols-outlined">image</span>
                        Choose Image File
                    </label>
                </div>
            </div>

            <div class="form-group">
                <label>Event Name</label>
                <input type="text" name="concert_name" placeholder="e.g. Eraserheads: Huling El Bimbo" required>
            </div>

            <div class="form-group">
                <label>Description</label>
                <textarea name="description" rows="4" placeholder="Briefly describe the concert..." required></textarea>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div class="form-group">
                    <label>Date (Descriptive)</label>
                    <input type="text" name="concert_date" placeholder="e.g. December 2025" required>
                </div>
                <div class="form-group">
                    <label>Time (Descriptive)</label>
                    <input type="text" name="concert_time" placeholder="e.g. 7 PM" required>
                </div>
            </div>

            <div class="form-group">
                <label>Venue</label>
                <input type="text" name="venue" placeholder="e.g. Philippine Arena" required>
            </div>

            <button type="submit" name="add" class="btn-submit">Publish Concert</button>
        </form>
    </div>
</main>

<script>
    function updateFileName() {
        const input = document.getElementById('concert_image');
        const label = document.getElementById('file-label');
        if (input.files.length > 0) {
            label.innerHTML = `<span class="material-symbols-outlined">check_circle</span> ${input.files[0].name}`;
            label.style.borderColor = 'var(--accent-primary)';
            label.style.color = 'var(--accent-primary)';
        }
    }

    if (localStorage.getItem('theme') === 'light') document.body.classList.remove('dark');
</script>
</body>
</html>