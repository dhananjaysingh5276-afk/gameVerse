<?php
// Include bootstrap
require_once __DIR__ . '/../bootstrap.php';

// If already logged in, redirect to dashboard
if (isLoggedIn()) {
    if (isAdmin()) {
        redirect('../admin/dashboard.php');
    } else {
        redirect('../user/dashboard.php');
    }
}

$error = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        $error = "Please enter username and password!";
    } else {
        // Check user credentials
        $query = "SELECT id, username, password_hash, role FROM users WHERE username = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Verify password
            if (password_verify($password, $user['password_hash'])) {
                // Set session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                
                // Update last login
                $update_query = "UPDATE users SET last_login = NOW() WHERE id = ?";
                $stmt = $conn->prepare($update_query);
                $stmt->bind_param("i", $user['id']);
                $stmt->execute();
                
                // Redirect based on role
                if ($user['role'] === 'admin') {
                    redirect('../admin/dashboard.php');
                } else {
                    redirect('../user/dashboard.php');
                }
            } else {
                $error = "Invalid password!";
            }
        } else {
            $error = "User not found!";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login - GamerVerse</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Segoe UI', Arial, sans-serif; 
            background: #0a0a0a; 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            height: 100vh; 
            background-image: radial-gradient(circle at 20% 50%, #0a1628 0%, #0a0a0a 70%);
        }
        .container { 
            background: #1a1a2e; 
            padding: 40px; 
            border-radius: 12px; 
            width: 420px; 
            box-shadow: 0 0 40px rgba(0,150,255,0.15), 0 0 80px rgba(0,150,255,0.05);
            border: 1px solid #2a2a4e;
        }
        h1 { 
            color: #00aaff; 
            text-align: center; 
            margin-bottom: 10px;
            font-size: 28px;
            letter-spacing: 1px;
        }
        .subtitle {
            color: #888;
            text-align: center;
            margin-bottom: 30px;
            font-size: 14px;
        }
        .form-group { 
            margin-bottom: 20px; 
        }
        label { 
            display: block; 
            color: #ccc; 
            margin-bottom: 6px; 
            font-size: 14px;
            font-weight: 500;
        }
        input { 
            width: 100%; 
            padding: 12px 15px; 
            border: 2px solid #2a2a4e; 
            border-radius: 8px; 
            background: #0a0a1a; 
            color: #fff; 
            font-size: 14px;
            transition: border-color 0.3s ease;
        }
        input:focus { 
            outline: none; 
            border-color: #00aaff; 
        }
        .btn { 
            width: 100%; 
            padding: 14px; 
            background: linear-gradient(135deg, #00aaff, #0088cc); 
            color: #fff; 
            border: none; 
            border-radius: 8px; 
            cursor: pointer; 
            font-size: 16px; 
            font-weight: 600;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .btn:hover { 
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,170,255,0.3);
        }
        .btn:active {
            transform: translateY(0);
        }
        .error { 
            color: #ff4444; 
            background: rgba(255,68,68,0.1); 
            padding: 12px; 
            border-radius: 8px; 
            margin-bottom: 15px; 
            border-left: 4px solid #ff4444;
            font-size: 14px;
        }
        .register-link { 
            text-align: center; 
            margin-top: 20px; 
            color: #888; 
            font-size: 14px;
        }
        .register-link a { 
            color: #00aaff; 
            text-decoration: none; 
            font-weight: 500;
        }
        .register-link a:hover { 
            text-decoration: underline; 
        }
        .game-icon {
            text-align: center;
            font-size: 40px;
            margin-bottom: 5px;
        }
        .demo-note {
            text-align: center;
            color: #666;
            font-size: 12px;
            margin-top: 15px;
            padding: 10px;
            background: rgba(0,170,255,0.05);
            border-radius: 6px;
            border: 1px solid #1a2a3e;
        }
        .demo-note strong {
            color: #00aaff;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="game-icon">🎮</div>
        <h1>GamerVerse</h1>
        <p class="subtitle">Login to your gaming account</p>
        
        <?php if ($error): ?>
            <div class="error">❌ <?= $error ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label>👤 Username</label>
                <input type="text" name="username" placeholder="Enter your username" required>
            </div>
            <div class="form-group">
                <label>🔒 Password</label>
                <input type="password" name="password" placeholder="Enter your password" required>
            </div>
            <button type="submit" class="btn">🔑 Login</button>
        </form>
        
        <div class="register-link">
            Don't have an account? <a href="register.php">Register here</a>
        </div>
        
        <div class="demo-note">
            🔑 <strong>Demo Accounts:</strong><br>
            Admin: username: <strong>admin</strong> | password: <strong>admin123</strong><br>
            (Or login with the account you registered)
        </div>
    </div>
</body>
</html>