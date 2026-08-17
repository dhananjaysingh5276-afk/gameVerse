<?php
require_once __DIR__ . '/../bootstrap.php';

// Redirect if not logged in
if (!isLoggedIn()) {
    redirect('../auth/login.php');
}

// Redirect admin to admin dashboard
if (isAdmin()) {
    redirect('../admin/dashboard.php');
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Get user data
$user_query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Get recent purchases (max 5)
$purchases_query = "SELECT i.*, p.purchase_date 
                    FROM inventory p 
                    JOIN items i ON p.item_id = i.id 
                    WHERE p.user_id = ? 
                    ORDER BY p.purchase_date DESC 
                    LIMIT 5";
$stmt = $conn->prepare($purchases_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$recent_items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard - GamerVerse</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Segoe UI', Arial, sans-serif; 
            background: #0a0a0a; 
            color: #fff;
            min-height: 100vh;
        }
        .navbar {
            background: #1a1a2e;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #2a2a4e;
        }
        .navbar h1 {
            color: #00aaff;
            font-size: 22px;
        }
        .navbar .user-info {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        .navbar .coins {
            background: linear-gradient(135deg, #ffd700, #ffaa00);
            color: #000;
            padding: 6px 15px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 14px;
        }
        .navbar .logout {
            color: #ff4444;
            text-decoration: none;
            padding: 6px 15px;
            border: 1px solid #ff4444;
            border-radius: 6px;
            transition: 0.3s;
        }
        .navbar .logout:hover {
            background: #ff4444;
            color: #fff;
        }
        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }
        .welcome {
            margin-bottom: 30px;
        }
        .welcome h2 {
            font-size: 28px;
            color: #00aaff;
        }
        .welcome p {
            color: #888;
            margin-top: 5px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: #1a1a2e;
            padding: 20px;
            border-radius: 10px;
            border: 1px solid #2a2a4e;
            text-align: center;
        }
        .stat-card .number {
            font-size: 32px;
            font-weight: 700;
            color: #00aaff;
        }
        .stat-card .label {
            color: #888;
            font-size: 14px;
            margin-top: 5px;
        }
        .section-title {
            font-size: 20px;
            color: #00aaff;
            margin-bottom: 15px;
        }
        .recent-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 15px;
        }
        .recent-item {
            background: #1a1a2e;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid #2a2a4e;
            text-align: center;
            transition: 0.3s;
        }
        .recent-item:hover {
            border-color: #00aaff;
            transform: translateY(-3px);
        }
        .recent-item img {
            width: 60px;
            height: 60px;
            border-radius: 8px;
            object-fit: cover;
        }
        .recent-item h4 {
            font-size: 14px;
            margin: 8px 0 5px;
            color: #fff;
        }
        .recent-item .date {
            font-size: 11px;
            color: #666;
        }
        .empty {
            color: #666;
            text-align: center;
            padding: 40px;
            background: #1a1a2e;
            border-radius: 10px;
            border: 1px dashed #2a2a4e;
        }
        .quick-actions {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }
        .quick-actions a {
            padding: 12px 25px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: 0.3s;
        }
        .btn-primary {
            background: linear-gradient(135deg, #00aaff, #0088cc);
            color: #fff;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,170,255,0.3);
        }
        .btn-secondary {
            background: #1a1a2e;
            color: #00aaff;
            border: 1px solid #00aaff;
        }
        .btn-secondary:hover {
            background: #00aaff;
            color: #fff;
        }
        .btn-danger {
            background: #ff4444;
            color: #fff;
        }
        .btn-danger:hover {
            background: #cc0000;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <h1>🎮 GamerVerse</h1>
        <div class="user-info">
            <span>👤 <?= htmlspecialchars($username) ?></span>
            <span class="coins">🪙 <?= number_format($user['coins']) ?></span>
            <a href="../auth/logout.php" class="logout">Logout</a>
        </div>
    </nav>

    <div class="container">
        <div class="welcome">
            <h2>Welcome back, <?= htmlspecialchars($username) ?>! 👋</h2>
            <p>Your gaming collection is waiting for you.</p>
        </div>

        <div class="quick-actions">
            <a href="../user/browse.php" class="btn-primary">🛒 Browse Marketplace</a>
            <a href="../user/inventory.php" class="btn-secondary">🎮 My Collection</a>
            <a href="../user/profile.php" class="btn-secondary">⚙️ Edit Profile</a>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="number"><?= number_format($user['coins']) ?></div>
                <div class="label">🪙 Coins Available</div>
            </div>
            <div class="stat-card">
                <div class="number"><?= count($recent_items) ?></div>
                <div class="label">🎮 Recent Purchases</div>
            </div>
            <div class="stat-card">
                <div class="number"><?= date('M d, Y', strtotime($user['created_at'])) ?></div>
                <div class="label">📅 Joined Date</div>
            </div>
        </div>

        <h3 class="section-title">📦 Recent Purchases</h3>
        <?php if (empty($recent_items)): ?>
            <div class="empty">
                <p>No purchases yet. Start shopping!</p>
                <br>
                <a href="../user/browse.php" class="btn-primary" style="display:inline-block;">Browse Marketplace</a>
            </div>
        <?php else: ?>
            <div class="recent-grid">
                <?php foreach ($recent_items as $item): ?>
                    <div class="recent-item">
                        <img src="<?= $item['image_url'] ?? 'default.png' ?>" alt="<?= $item['name'] ?>">
                        <h4><?= htmlspecialchars($item['name']) ?></h4>
                        <div class="date"><?= date('M d, Y', strtotime($item['purchase_date'])) ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>