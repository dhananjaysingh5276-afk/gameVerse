<?php
require_once __DIR__ . '/../bootstrap.php';

// Redirect if not logged in
if (!isLoggedIn()) {
    redirect('../auth/login.php');
}

// Redirect regular users to user dashboard
if (!isAdmin()) {
    redirect('../user/dashboard.php');
}

$username = $_SESSION['username'];

// Get statistics
$stats = [];

// Total users
$result = $conn->query("SELECT COUNT(*) as count FROM users");
$stats['users'] = $result->fetch_assoc()['count'];

// Total games
$result = $conn->query("SELECT COUNT(*) as count FROM games");
$stats['games'] = $result->fetch_assoc()['count'];

// Total items
$result = $conn->query("SELECT COUNT(*) as count FROM items");
$stats['items'] = $result->fetch_assoc()['count'];

// Total transactions
$result = $conn->query("SELECT COUNT(*) as count FROM transactions");
$stats['transactions'] = $result->fetch_assoc()['count'];

// Total revenue (from purchases)
$result = $conn->query("SELECT SUM(amount) as total FROM transactions WHERE type = 'purchase'");
$stats['revenue'] = $result->fetch_assoc()['total'] ?? 0;

// Recent transactions (last 10)
$transactions_query = "SELECT t.*, u.username, i.name as item_name 
                       FROM transactions t 
                       LEFT JOIN users u ON t.user_id = u.id 
                       LEFT JOIN items i ON t.item_id = i.id 
                       ORDER BY t.created_at DESC 
                       LIMIT 10";
$recent_transactions = $conn->query($transactions_query)->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard - GamerVerse</title>
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
        .navbar .badge {
            background: #ff4444;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
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
            max-width: 1400px;
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
        .admin-actions {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }
        .admin-actions a {
            padding: 12px 25px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: 0.3s;
            background: #1a1a2e;
            color: #00aaff;
            border: 1px solid #00aaff;
        }
        .admin-actions a:hover {
            background: #00aaff;
            color: #fff;
            transform: translateY(-2px);
        }
        .section-title {
            font-size: 20px;
            color: #00aaff;
            margin-bottom: 15px;
        }
        table {
            width: 100%;
            background: #1a1a2e;
            border-radius: 10px;
            border-collapse: collapse;
            overflow: hidden;
        }
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #2a2a4e;
        }
        th {
            background: #0a0a1a;
            color: #00aaff;
            font-weight: 600;
        }
        td {
            color: #ccc;
        }
        .status-completed { color: #44ff88; }
        .status-pending { color: #ffaa00; }
        .status-failed { color: #ff4444; }
        .empty {
            color: #666;
            text-align: center;
            padding: 40px;
        }
        .coins {
            color: #ffd700;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <h1>🎮 GamerVerse Admin</h1>
        <div class="user-info">
            <span>👤 <?= htmlspecialchars($username) ?></span>
            <span class="badge">Admin</span>
            <a href="../auth/logout.php" class="logout">Logout</a>
        </div>
    </nav>

    <div class="container">
        <div class="welcome">
            <h2>Welcome, Admin <?= htmlspecialchars($username) ?>! 👑</h2>
            <p>Manage your gaming marketplace from here.</p>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="number"><?= $stats['users'] ?></div>
                <div class="label">👤 Total Users</div>
            </div>
            <div class="stat-card">
                <div class="number"><?= $stats['games'] ?></div>
                <div class="label">🎮 Total Games</div>
            </div>
            <div class="stat-card">
                <div class="number"><?= $stats['items'] ?></div>
                <div class="label">📦 Total Items</div>
            </div>
            <div class="stat-card">
                <div class="number"><?= $stats['transactions'] ?></div>
                <div class="label">💳 Transactions</div>
            </div>
            <div class="stat-card">
                <div class="number coins">🪙 <?= number_format($stats['revenue']) ?></div>
                <div class="label">💰 Total Revenue</div>
            </div>
        </div>

        <div class="admin-actions">
            <a href="../admin/games/list.php">🎮 Manage Games</a>
            <a href="../admin/items/list.php">📦 Manage Items</a>
            <a href="../admin/users/list.php">👤 Manage Users</a>
            <a href="../admin/transactions/list.php">💳 View All Transactions</a>
        </div>

        <h3 class="section-title">📋 Recent Transactions</h3>
        <?php if (empty($recent_transactions)): ?>
            <div class="empty">No transactions yet.</div>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Item</th>
                        <th>Amount</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_transactions as $txn): ?>
                        <tr>
                            <td><?= htmlspecialchars($txn['username'] ?? 'Unknown') ?></td>
                            <td><?= htmlspecialchars($txn['item_name'] ?? 'N/A') ?></td>
                            <td class="coins">🪙 <?= number_format($txn['amount']) ?></td>
                            <td><?= $txn['type'] ?></td>
                            <td class="status-<?= $txn['status'] ?>"><?= $txn['status'] ?></td>
                            <td><?= date('M d, Y H:i', strtotime($txn['created_at'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>