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
        
        $target_dir = realpath(__DIR__ . "/../assets/images/concerts/");
        if ($target_dir) {
            $target_file = $target_dir . DIRECTORY_SEPARATOR . $image_name;
            if (!move_uploaded_file($_FILES['concert_image']['tmp_name'], $target_file)) {
                $image_name = ""; 
            }
        }
    }

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
            --accent-gradient: linear-gradient(135deg, #818cf8 0%, #c084fc 100%);
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

        * { margin: 0; padding: 0; box-sizing: border-box; transition: all 0.2s ease; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: var(--bg-body); color: var(--text-main); min-height: 100vh; }

        nav { 
            display: flex; justify-content: space-between; align-items: center; 
            padding: 0 8%; background: var(--nav-bg); backdrop-filter: blur(12px); 
            position: sticky; top: 0; z-index: 1000; height: 80px; border-bottom: 1px solid var(--glass-border); 
        }
        .logo { font-weight: 800; font-size: 1.4rem; display: flex; align-items: center; gap: 10px; color: var(--text-main); text-decoration: none; }

        .container { padding: 60px 5%; display: flex; justify-content: center; }
        .form-card { 
            background: var(--surface); border: 1px solid var(--glass-border); 
            border-radius: 28px; padding: 40px; width: 100%; max-width: 600px; 
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5); 
        }

        .form-header { margin-bottom: 30px; }
        .form-header h2 { font-size: 2.2rem; font-weight: 800; letter-spacing: -1.5px; }
        .form-header p { color: var(--text-muted); font-size: 0.95rem; margin-top: 5px; }

        .form-group { margin-bottom: 24px; }
        .form-group label { display: block; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; color: var(--text-muted); margin-bottom: 10px; letter-spacing: 1.5px; font-family: 'Plus Jakarta Sans', sans-serif; }

        input, textarea { 
            width: 100%; padding: 16px 18px; border-radius: 14px; 
            background: rgba(0, 0, 0, 0.15); border: 1px solid var(--glass-border); 
            color: var(--text-main); font-family: inherit; font-weight: 600; font-size: 0.95rem; 
        }
        input:focus, textarea:focus { border-color: var(--accent-primary); outline: none; background: rgba(0,0,0,0.2); }

        .file-custom {
            display: flex; 
            flex-direction: row; 
            align-items: center;       
            justify-content: center; 
            padding: 22px 25px;        
            border: 2px dashed var(--glass-border); 
            border-radius: 14px; 
            cursor: pointer; 
            color: var(--text-muted); 
            background: rgba(0, 0, 0, 0.1); 
            text-transform: uppercase; 
            font-size: 0.75rem; 
            letter-spacing: 1.5px;
            width: 100%;
            text-align: center;
        }

        .file-custom b { font-weight: 800; white-space: nowrap; }

        .file-custom:hover { 
            border-color: var(--accent-primary); 
            color: var(--accent-primary); 
            background: rgba(129, 140, 248, 0.05); 
        }

        /* ENHANCED: Publish Button Font Weight and Family */
        .btn-submit {
            width: 100%; padding: 18px; border-radius: 16px; border: none;
            background: var(--accent-gradient); color: white;
            font-family: 'Plus Jakarta Sans', sans-serif; /* Explicit font family */
            font-weight: 800; /* Set to match the label's bold aesthetic while remaining "Normal" relative to header */
            font-size: 0.85rem; 
            text-transform: uppercase; 
            letter-spacing: 2px; /* Matches the tight, modern feel of the labels */
            cursor: pointer; 
            margin-top: 10px;
            box-shadow: 0 10px 20px -5px rgba(129, 140, 248, 0.4);
            transition: 0.3s all ease;
        }
        .btn-submit:hover { transform: translateY(-2px); filter: brightness(1.1); box-shadow: 0 15px 30px -5px rgba(129, 140, 248, 0.5); }

        .alert { padding: 15px; border-radius: 12px; margin-bottom: 25px; font-weight: 700; font-size: 0.9rem; text-align: center; background: rgba(34, 197, 94, 0.1); color: #22c55e; border: 1px solid rgba(34, 197, 94, 0.2); }
    </style>
</head>
<body class="dark">

<nav>
    <a href="dashboard.php" class="logo">
        <span class="material-symbols-outlined" style="color: var(--accent-primary)">theater_comedy</span>
        CONCERTIX ADMIN
    </a>
    <a href="manage-concerts.php" style="text-decoration:none; color:var(--text-muted); font-weight:700; font-size:0.8rem; display: flex; align-items: center; gap: 5px;">
        <span class="material-symbols-outlined" style="font-size: 18px;">arrow_back</span>
        BACK TO DASHBOARD
    </a>
</nav>

<main class="container">
    <div class="form-card">
        <div class="form-header">
            <h2>Add New Concert</h2>
            <p>Publish an event. All fields support descriptive text.</p>
        </div>

        <?php if ($message): ?>
            <div class="alert"><?php echo $message; ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label>Concert Poster / Image</label>
                <input type="file" name="concert_image" id="concert_image" style="display: none;" onchange="updateFileName()">
                <label for="concert_image" class="file-custom" id="file-label">
                    <b>Choose Image File</b>
                </label>
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
            label.innerHTML = `<b style="color:var(--accent-primary)">${input.files[0].name}</b>`;
            label.style.borderColor = 'var(--accent-primary)';
            label.style.borderStyle = 'solid';
        }
    }
    if (localStorage.getItem('theme') === 'light') document.body.classList.remove('dark');
</script>
</body>
</html>