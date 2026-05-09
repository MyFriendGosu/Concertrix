<?php
session_start();
include(__DIR__ . '/../config/db.php');

if (isset($_POST['register'])) {
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Check if email exists
    $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $error = "Email already exists. Try logging in.";
    } else {
        $stmt = $conn->prepare("INSERT INTO users (fullname, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $fullname, $email, $hashedPassword);

        if ($stmt->execute()) {
            $success = "Account created! You can now log in.";
        } else {
            $error = "Registration failed. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Concertix</title>
    
    <!-- Material Symbols & Fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            /* Light Theme */
            --bg-70: #f8fafc;
            --secondary-20: #ffffff;
            --accent-10: #6366f1;
            --text-main: #0f172a;
            --text-dim: #64748b;
            --input-bg: #ffffff;
            --border: #e2e8f0;
            --shadow: rgba(0, 0, 0, 0.08);
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
            --shadow: rgba(0, 0, 0, 0.3);
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

        .register-card {
            background: var(--secondary-20);
            padding: 2.5rem;
            border-radius: 1.5rem;
            border: 1px solid var(--border);
            box-shadow: 0 20px 25px -5px var(--shadow);
            width: 90%;
            max-width: 440px;
        }

        .brand-section { text-align: center; margin-bottom: 2rem; }
        .logo-box { 
            background: var(--accent-10); 
            width: 50px; height: 50px; 
            border-radius: 12px; 
            display: inline-flex; 
            align-items: center; 
            justify-content: center; 
            color: white; 
            margin-bottom: 1rem;
        }

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
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.15);
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
            margin-top: 0.5rem;
        }

        .btn-primary:hover { filter: brightness(1.1); transform: translateY(-1px); }

        .status-banner {
            padding: 12px;
            border-radius: 0.75rem;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.85rem;
            margin-bottom: 1.5rem;
            border: 1px solid;
        }

        .error { background: rgba(239, 68, 68, 0.1); color: #ef4444; border-color: rgba(239, 68, 68, 0.2); }
        .success { background: rgba(16, 185, 129, 0.1); color: #10b981; border-color: rgba(16, 185, 129, 0.2); }

        .auth-footer { margin-top: 1.5rem; text-align: center; font-size: 0.9rem; color: var(--text-dim); }
        .auth-footer a { color: var(--accent-10); text-decoration: none; font-weight: 600; }
    </style>
</head>
<body>

    <button id="themeToggle" class="theme-toggle">
        <span class="material-symbols-outlined" id="themeIcon">dark_mode</span>
    </button>

    <div class="register-card">
        <div class="brand-section">
            <div class="logo-box">
                <span class="material-symbols-outlined">confirmation_number</span>
            </div>
            <h1>Create Account</h1>
            <p>Join the community and start your journey.</p>
        </div>

        <?php if (isset($error)): ?>
            <div class="status-banner error">
                <span class="material-symbols-outlined">error</span>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($success)): ?>
            <div class="status-banner success">
                <span class="material-symbols-outlined">check_circle</span>
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="fullname">Full Name</label>
                <div class="input-wrapper">
                    <span class="material-symbols-outlined input-icon">person</span>
                    <input type="text" name="fullname" id="fullname" placeholder="John Doe" required>
                </div>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <div class="input-wrapper">
                    <span class="material-symbols-outlined input-icon">mail</span>
                    <input type="email" name="email" id="email" placeholder="name@email.com" required>
                </div>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-wrapper">
                    <span class="material-symbols-outlined input-icon">lock</span>
                    <input type="password" name="password" id="password" placeholder="••••••••" required>
                </div>
            </div>

            <button type="submit" name="register" class="btn-primary">
                Register <span class="material-symbols-outlined">arrow_forward</span>
            </button>
        </form>

        <div class="auth-footer">
            Already have an account? <a href="login.php">Login here</a>
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