<?php
session_start();
include(__DIR__ . '/../config/db.php');
$root = "/concert_ticketing_system/"; 

if (isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, password, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            header("Location: " . $root . ($user['role'] === 'admin' ? "admin/dashboard.php" : "pages/home.php"));
            exit;
        } else {
            $error = "Invalid password.";
        }
    } else {
        $error = "No account found with that email.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Concertix</title>
    
    <!-- Material Symbols & Fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            /* Light Theme (Default) */
            --bg-70: #f8fafc;
            --secondary-20: #ffffff;
            --accent-10: #6366f1;
            --text-main: #0f172a;
            --text-dim: #64748b;
            --input-bg: #ffffff;
            --border: #e2e8f0;
            --shadow: rgba(0, 0, 0, 0.1);
        }

        body.dark {
            /* Dark Theme */
            --bg-70: #0f172a;
            --secondary-20: #1e293b;
            --accent-10: #818cf8;
            --text-main: #f8fafc;
            --text-dim: #94a3b8;
            --input-bg: rgba(15, 23, 42, 0.6);
            --border: rgba(255, 255, 255, 0.1);
            --shadow: rgba(0, 0, 0, 0.4);
        }

        * { box-sizing: border-box; transition: background-color 0.3s ease, border-color 0.3s ease, color 0.3s ease; }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-70);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            color: var(--text-main);
        }

        /* Floating Theme Toggle */
        .theme-toggle {
            position: fixed;
            top: 2rem;
            right: 2rem;
            background: var(--secondary-20);
            border: 1px solid var(--border);
            color: var(--accent-10);
            padding: 10px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            box-shadow: 0 4px 12px var(--shadow);
            z-index: 1000;
        }

        .login-card {
            background: var(--secondary-20);
            padding: 2.5rem;
            border-radius: 1.5rem;
            border: 1px solid var(--border);
            box-shadow: 0 20px 25px -5px var(--shadow);
            width: 90%;
            max-width: 400px;
        }

        .brand-section { text-align: center; margin-bottom: 2rem; }
        .logo-icon { font-size: 3rem; color: var(--accent-10); margin-bottom: 1rem; }
        .brand-section h1 { margin: 0; font-size: 1.75rem; font-weight: 700; }
        .brand-section p { color: var(--text-dim); font-size: 0.9rem; margin-top: 0.5rem; }

        .form-group { margin-bottom: 1.25rem; }
        .form-group label { display: block; font-size: 0.8rem; font-weight: 600; margin-bottom: 0.5rem; color: var(--text-dim); text-transform: uppercase; }

        .input-wrapper { position: relative; display: flex; align-items: center; }
        .input-icon { position: absolute; left: 12px; color: var(--text-dim); font-size: 1.25rem; }

        input {
            width: 100%;
            padding: 12px 12px 12px 42px;
            background: var(--input-bg);
            border: 1px solid var(--border);
            border-radius: 0.75rem;
            font-size: 1rem;
            color: var(--text-main);
        }

        input:focus {
            outline: none;
            border-color: var(--accent-10);
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.2);
        }

        .btn-primary {
            width: 100%;
            padding: 14px;
            background: var(--accent-10);
            color: white;
            border: none;
            border-radius: 0.75rem;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-top: 1rem;
        }

        .btn-primary:hover { filter: brightness(1.1); transform: translateY(-1px); }

        .error-banner {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
            padding: 12px;
            border-radius: 0.75rem;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.85rem;
            margin-bottom: 1.5rem;
            border: 1px solid rgba(239, 68, 68, 0.2);
        }

        .auth-footer { margin-top: 2rem; text-align: center; font-size: 0.9rem; color: var(--text-dim); }
        .auth-footer a { color: var(--accent-10); text-decoration: none; font-weight: 600; }
    </style>
</head>
<body class="light">

    <button id="themeToggle" class="theme-toggle" title="Toggle Dark/Light Mode">
        <span class="material-symbols-outlined" id="themeIcon">dark_mode</span>
    </button>

    <div class="login-card">
        <div class="brand-section">
            <span class="material-symbols-outlined logo-icon">confirmation_number</span>
            <h1>Concertix</h1>
            <p>Welcome back! Sign in to continue.</p>
        </div>

        <?php if (isset($error)): ?>
            <div class="error-banner">
                <span class="material-symbols-outlined">warning</span>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="email">Email</label>
                <div class="input-wrapper">
                    <span class="material-symbols-outlined input-icon">mail</span>
                    <input type="email" name="email" id="email" placeholder="name@example.com" required>
                </div>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-wrapper">
                    <span class="material-symbols-outlined input-icon">lock</span>
                    <input type="password" name="password" id="password" placeholder="••••••••" required>
                </div>
            </div>

            <button type="submit" name="login" class="btn-primary">
                Login <span class="material-symbols-outlined">login</span>
            </button>
        </form>

        <div class="auth-footer">
            Don't have an account? <a href="register.php">Create one</a>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const toggleBtn = document.getElementById("themeToggle");
            const themeIcon = document.getElementById("themeIcon");
            const body = document.body;

            const updateUI = (theme) => {
                themeIcon.textContent = theme === "dark" ? "light_mode" : "dark_mode";
                body.className = theme;
            };

            // Load theme
            const savedTheme = localStorage.getItem("theme") || "light";
            updateUI(savedTheme);

            toggleBtn.addEventListener("click", () => {
                const currentTheme = body.classList.contains("dark") ? "dark" : "light";
                const newTheme = currentTheme === "dark" ? "light" : "dark";
                
                localStorage.setItem("theme", newTheme);
                updateUI(newTheme);
            });
        });
    </script>
</body>
</html>