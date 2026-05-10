<?php
session_start();
include(__DIR__ . '/../config/db.php');
$root = "/concert_ticketing_system/"; 

$errors = []; 

if (isset($_POST['register'])) {
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // 1. Full Name Validation (No numbers allowed)
    if (empty($fullname)) {
        $errors['fullname'] = "Full name is required.";
    } elseif (!preg_match("/^[a-zA-Z\s]+$/", $fullname)) {
        $errors['fullname'] = "Name can only contain letters and spaces.";
    }

    // 2. Email Validation (Must start with a letter)
    if (empty($email)) {
        $errors['email'] = "Email is required.";
    } elseif (!preg_match("/^[a-zA-Z]/", $email)) {
        $errors['email'] = "Email must start with a letter.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Please enter a valid email format.";
    }

    // 3. Password Validation (Min 8 characters)
    if (empty($password)) {
        $errors['password'] = "Password is required.";
    } elseif (strlen($password) < 8) {
        $errors['password'] = "Password must be at least 8 characters.";
    }

    // Process Registration if no errors
    if (empty($errors)) {
        $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $errors['email'] = "This email is already registered.";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (fullname, email, password, role) VALUES (?, ?, ?, 'user')");
            $stmt->bind_param("sss", $fullname, $email, $hashedPassword);

            if ($stmt->execute()) {
                $success = "Account created! You can now log in.";
            } else {
                $errors['general'] = "Registration failed. Please try again.";
            }
        }
        $check->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Concertix</title>
    
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
            --shadow: rgba(0, 0, 0, 0.08);
            --error: #ef4444;
            --success: #10b981;
        }

        body.dark {
            --bg-70: #0f172a;
            --secondary-20: #1e293b;
            --accent-10: #818cf8;
            --text-main: #f8fafc;
            --text-dim: #94a3b8;
            --input-bg: rgba(15, 23, 42, 0.6);
            --border: rgba(255, 255, 255, 0.1);
            --shadow: rgba(0, 0, 0, 0.3);
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
            z-index: 1000;
        }

        .register-card {
            background: var(--secondary-20); padding: 2.5rem;
            border-radius: 1.5rem; border: 1px solid var(--border);
            box-shadow: 0 20px 25px -5px var(--shadow);
            width: 90%; max-width: 440px;
        }

        .brand-section { text-align: center; margin-bottom: 2rem; }
        .logo-box { 
            background: var(--accent-10); width: 50px; height: 50px; 
            border-radius: 12px; display: inline-flex; 
            align-items: center; justify-content: center; 
            color: white; margin-bottom: 1rem;
        }

        .form-group { margin-bottom: 1.25rem; }
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

        .status-banner {
            padding: 12px; border-radius: 0.75rem; display: flex;
            align-items: center; gap: 8px; font-size: 0.85rem;
            margin-bottom: 1.5rem; border: 1px solid;
        }
        .success-banner { background: rgba(16, 185, 129, 0.1); color: var(--success); border-color: rgba(16, 185, 129, 0.2); }

        .auth-footer { margin-top: 1.5rem; text-align: center; font-size: 0.9rem; color: var(--text-dim); }
        .auth-footer a { color: var(--accent-10); text-decoration: none; font-weight: 600; }
    </style>
</head>
<body class="light">

    <button id="themeToggle" class="theme-toggle">
        <span class="material-symbols-outlined" id="themeIcon">dark_mode</span>
    </button>

    <div class="register-card">
        <div class="brand-section">
            <div class="logo-box">
                <span class="material-symbols-outlined">confirmation_number</span>
            </div>
            <h1>Create Account</h1>
            <p>Join Concertix today.</p>
        </div>

        <?php if (isset($success)): ?>
            <div class="status-banner success-banner">
                <span class="material-symbols-outlined">check_circle</span>
                <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <form method="POST" id="registerForm" novalidate>
            <div class="form-group">
                <label for="fullname">Full Name</label>
                <div class="input-wrapper">
                    <span class="material-symbols-outlined input-icon">person</span>
                    <input type="text" name="fullname" id="fullname" 
                           class="<?php echo isset($errors['fullname']) ? 'invalid' : ''; ?>"
                           placeholder="Letters only" value="<?php echo isset($fullname) ? htmlspecialchars($fullname) : ''; ?>" required>
                </div>
                <?php if (isset($errors['fullname'])): ?>
                    <div class="field-error"><span class="material-symbols-outlined" style="font-size: 14px;">error</span><?php echo $errors['fullname']; ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <div class="input-wrapper">
                    <span class="material-symbols-outlined input-icon">mail</span>
                    <input type="email" name="email" id="email" 
                           class="<?php echo isset($errors['email']) ? 'invalid' : ''; ?>"
                           placeholder="Must start with a letter" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required>
                </div>
                <?php if (isset($errors['email'])): ?>
                    <div class="field-error"><span class="material-symbols-outlined" style="font-size: 14px;">error</span><?php echo $errors['email']; ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-wrapper">
                    <span class="material-symbols-outlined input-icon">lock</span>
                    <input type="password" name="password" id="password" 
                           class="<?php echo isset($errors['password']) ? 'invalid' : ''; ?>"
                           placeholder="Min 8 characters" required>
                </div>
                <?php if (isset($errors['password'])): ?>
                    <div class="field-error"><span class="material-symbols-outlined" style="font-size: 14px;">error</span><?php echo $errors['password']; ?></div>
                <?php endif; ?>
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
            const form = document.getElementById("registerForm");
            const toggleBtn = document.getElementById("themeToggle");
            const body = document.body;

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

            form.addEventListener("submit", (e) => {
                const name = document.getElementById("fullname");
                const email = document.getElementById("email");
                const pass = document.getElementById("password");
                let valid = true;

                // Clear previous client-side errors
                document.querySelectorAll(".field-error").forEach(el => {
                    if(!el.dataset.backend) el.remove(); 
                });
                [name, email, pass].forEach(i => i.classList.remove("invalid"));

                // 1. Full Name: Letters Only
                if (!/^[a-zA-Z\s]+$/.test(name.value.trim())) {
                    showError(name, "Name cannot contain numbers.");
                    valid = false;
                }

                // 2. Email: Starts with letter
                if (!/^[a-zA-Z]/.test(email.value)) {
                    showError(email, "Email must start with a letter.");
                    valid = false;
                } else if (!/^[^ ]+@[^ ]+\.[a-z]{2,3}$/.test(email.value)) {
                    showError(email, "Invalid email format.");
                    valid = false;
                }

                // 3. Password: Min 8
                if (pass.value.length < 8) {
                    showError(pass, "Must be at least 8 characters.");
                    valid = false;
                }

                if (!valid) e.preventDefault();
            });

            function showError(input, msg) {
                input.classList.add("invalid");
                const errorDiv = document.createElement("div");
                errorDiv.className = "field-error";
                errorDiv.innerHTML = `<span class="material-symbols-outlined" style="font-size: 14px;">error</span> ${msg}`;
                input.parentElement.parentElement.appendChild(errorDiv);
            }
        });
    </script>
</body>
</html>