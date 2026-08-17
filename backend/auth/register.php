<?php
// Include bootstrap (database connection + session)
require_once __DIR__ . '/../bootstrap.php';

// If user is already logged in, redirect to dashboard
if (isLoggedIn()) {
    if (isAdmin()) {
        redirect('../admin/dashboard.php');
    } else {
        redirect('../user/dashboard.php');
    }
}

$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate inputs
    if (empty($username) || empty($email) || empty($password)) {
        $error = "All fields are required!";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match!";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email address!";
    } else {
        // Check if user already exists
        $check_query = "SELECT id FROM users WHERE username = ? OR email = ?";
        $stmt = $conn->prepare($check_query);
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = "Username or email already exists!";
        } else {
            // Hash password and insert user
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            
            $insert_query = "INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($insert_query);
            $stmt->bind_param("sss", $username, $email, $password_hash);
            
            if ($stmt->execute()) {
                $success = "Registration successful! Please <a href='login.php'>login here</a>.";
            } else {
                $error = "Registration failed. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register - GamerVerse</title>
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
        .success { 
            color: #44ff88; 
            background: rgba(68,255,136,0.1); 
            padding: 12px; 
            border-radius: 8px; 
            margin-bottom: 15px; 
            border-left: 4px solid #44ff88;
            font-size: 14px;
        }
        .success a {
            color: #00aaff;
            text-decoration: none;
        }
        .success a:hover {
            text-decoration: underline;
        }
        .login-link { 
            text-align: center; 
            margin-top: 20px; 
            color: #888; 
            font-size: 14px;
        }
        .login-link a { 
            color: #00aaff; 
            text-decoration: none; 
            font-weight: 500;
        }
        .login-link a:hover { 
            text-decoration: underline; 
        }
        .game-icon {
            text-align: center;
            font-size: 40px;
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="game-icon">🎮</div>
        <h1>GamerVerse</h1>
        <p class="subtitle">Create your gaming account</p>
        
        <?php if ($error): ?>
            <div class="error">❌ <?= $error ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="success">✅ <?= $success ?></div>
        <?php else: ?>
        
        <form method="POST">
            <div class="form-group">
                <label>👤 Username</label>
                <input type="text" name="username" placeholder="Choose a username" required>
            </div>
            <div class="form-group">
                <label>📧 Email</label>
                <input type="email" name="email" placeholder="your@email.com" required>
            </div>
            <div class="form-group">
                <label>🔒 Password (min 6 characters)</label>
                <input type="password" name="password" placeholder="Create a password" required>
            </div>
            <div class="form-group">
                <label>🔐 Confirm Password</label>
                <input type="password" name="confirm_password" placeholder="Confirm your password" required>
            </div>
            <button type="submit" class="btn">🚀 Create Account</button>
        </form>
        
        <?php endif; ?>
        
        <div class="login-link">
            Already have an account? <a href="login.php">Login here</a>
        </div>
    </div>
</body>
</html>