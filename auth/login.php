<?php
session_start();
include(__DIR__ . '/../config/db.php');
$root = "/concert_ticketing_system/"; 

$errors = []; // Changed to an array to hold multiple specific errors

if (isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Back-end Validation
    if (empty($email)) {
        $errors['email'] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Please enter a valid email address.";
    }

    if (empty($password)) {
        $errors['password'] = "Password is required.";
    }

    // Process Login if no validation errors
    if (empty($errors)) {
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
                $errors['password'] = "Incorrect password.";
            }
        } else {
            $errors['email'] = "No account associated with this email.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Concertix</title>
    
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --bg-70: #f8fafc;
            --secondary-20: #ffffff;
            --accent-10: #6366f1;
            --text-main: #0f172a;
            --text-dim: #64748b;
            --input-bg: #ffffff;
            --border: #e2e8f0;
            --shadow: rgba(0, 0, 0, 0.1);
            --error: #ef4444;
        }

        body.dark {
            --bg-70: #0f172a;
            --secondary-20: #1e293b;
            --accent-10: #818cf8;
            --text-main: #f8fafc;
            --text-dim: #94a3b8;
            --input-bg: rgba(15, 23, 42, 0.6);
            --border: rgba(255, 255, 255, 0.1);
            --shadow: rgba(0, 0, 0, 0.4);
        }

        * { box-sizing: border-box; transition: 0.2s ease; }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-70);
            display: flex; align-items: center; justify-content: center;
            min-height: 100vh; margin: 0; color: var(--text-main);
        }

        .theme-toggle {
            position: fixed; top: 2rem; right: 2rem;
            background: var(--secondary-20); border: 1px solid var(--border);
            color: var(--accent-10); padding: 10px; border-radius: 50%;
            cursor: pointer; display: flex; box-shadow: 0 4px 12px var(--shadow);
        }

        .login-card {
            background: var(--secondary-20); padding: 2.5rem;
            border-radius: 1.5rem; border: 1px solid var(--border);
            box-shadow: 0 20px 25px -5px var(--shadow);
            width: 90%; max-width: 400px;
        }

        .brand-section { text-align: center; margin-bottom: 2rem; }
        .logo-icon { font-size: 3rem; color: var(--accent-10); margin-bottom: 0.5rem; }
        
        .form-group { margin-bottom: 1.5rem; position: relative; }
        .form-group label { display: block; font-size: 0.75rem; font-weight: 700; margin-bottom: 0.5rem; color: var(--text-dim); text-transform: uppercase; }

        .input-wrapper { position: relative; display: flex; align-items: center; }
        .input-icon { position: absolute; left: 12px; color: var(--text-dim); font-size: 1.25rem; }

        input {
            width: 100%; padding: 12px 12px 12px 42px;
            background: var(--input-bg); border: 1px solid var(--border);
            border-radius: 0.75rem; font-size: 1rem; color: var(--text-main);
        }

        input.invalid { border-color: var(--error); background: rgba(239, 68, 68, 0.05); }

        .field-error {
            color: var(--error); font-size: 0.75rem; font-weight: 600;
            margin-top: 5px; display: flex; align-items: center; gap: 4px;
        }

        .btn-primary {
            width: 100%; padding: 14px; background: var(--accent-10);
            color: white; border: none; border-radius: 0.75rem;
            font-weight: 700; cursor: pointer; display: flex;
            align-items: center; justify-content: center; gap: 10px;
        }

        .btn-primary:hover { transform: translateY(-1px); opacity: 0.9; }

        .auth-footer { margin-top: 2rem; text-align: center; font-size: 0.9rem; color: var(--text-dim); }
        .auth-footer a { color: var(--accent-10); text-decoration: none; font-weight: 600; }
    </style>
</head>
<body class="light">

    <button id="themeToggle" class="theme-toggle">
        <span class="material-symbols-outlined" id="themeIcon">dark_mode</span>
    </button>

    <div class="login-card">
        <div class="brand-section">
            <span class="material-symbols-outlined logo-icon">confirmation_number</span>
            <h1>Concertix</h1>
            <p>Welcome back! Sign in to continue.</p>
        </div>

        <form method="POST" action="" id="loginForm" novalidate>
            <div class="form-group">
                <label for="email">Email Address</label>
                <div class="input-wrapper">
                    <span class="material-symbols-outlined input-icon">mail</span>
                    <input type="email" name="email" id="email" 
                           class="<?php echo isset($errors['email']) ? 'invalid' : ''; ?>"
                           placeholder="name@example.com" 
                           value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required>
                </div>
                <?php if (isset($errors['email'])): ?>
                    <div class="field-error">
                        <span class="material-symbols-outlined" style="font-size: 14px;">error</span>
                        <?php echo $errors['email']; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-wrapper">
                    <span class="material-symbols-outlined input-icon">lock</span>
                    <input type="password" name="password" id="password" 
                           class="<?php echo isset($errors['password']) ? 'invalid' : ''; ?>"
                           placeholder="••••••••" required>
                </div>
                <?php if (isset($errors['password'])): ?>
                    <div class="field-error">
                        <span class="material-symbols-outlined" style="font-size: 14px;">error</span>
                        <?php echo $errors['password']; ?>
                    </div>
                <?php endif; ?>
            </div>

            <button type="submit" name="login" class="btn-primary">
                Sign In <span class="material-symbols-outlined">login</span>
            </button>
        </form>

        <div class="auth-footer">
            Don't have an account? <a href="register.php">Create one</a>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const loginForm = document.getElementById("loginForm");
            const toggleBtn = document.getElementById("themeToggle");
            const body = document.body;

            // Theme Management
            const updateUI = (theme) => {
                document.getElementById("themeIcon").textContent = theme === "dark" ? "light_mode" : "dark_mode";
                body.className = theme;
            };
            updateUI(localStorage.getItem("theme") || "light");

            toggleBtn.addEventListener("click", () => {
                const newTheme = body.classList.contains("dark") ? "light" : "dark";
                localStorage.setItem("theme", newTheme);
                updateUI(newTheme);
            });

            // Client-side Validation
            loginForm.addEventListener("submit", (e) => {
                const email = document.getElementById("email");
                const password = document.getElementById("password");
                let hasError = false;

                // Simple Email Regex
                const emailPattern = /^[^ ]+@[^ ]+\.[a-z]{2,3}$/;

                if (!email.value.match(emailPattern)) {
                    showInlineError(email, "Enter a valid email.");
                    hasError = true;
                }

                if (password.value.length < 1) {
                    showInlineError(password, "Password cannot be empty.");
                    hasError = true;
                }

                if (hasError) e.preventDefault();
            });

            function showInlineError(input, msg) {
                input.classList.add("invalid");
                let errorDiv = input.parentElement.parentElement.querySelector(".field-error");
                if (!errorDiv) {
                    errorDiv = document.createElement("div");
                    errorDiv.className = "field-error";
                    errorDiv.innerHTML = `<span class="material-symbols-outlined" style="font-size: 14px;">error</span> ${msg}`;
                    input.parentElement.parentElement.appendChild(errorDiv);
                }
            }
        });
    </script>
</body>
</html>