<?php
require_once __DIR__ . '/../../bootstrap.php';

// Redirect if not admin
if (!isAdmin()) {
    redirect('../../auth/login.php');
}

// Get all games
$games = $conn->query("SELECT * FROM games ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Games - Admin</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #0a0a0a; color: #fff; }
        .navbar {
            background: #1a1a2e;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #2a2a4e;
        }
        .navbar h1 { color: #00aaff; }
        .navbar a { color: #00aaff; text-decoration: none; }
        .container { max-width: 1200px; margin: 30px auto; padding: 0 20px; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .btn {
            padding: 10px 20px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            display: inline-block;
        }
        .btn-primary { background: #00aaff; color: #fff; }
        .btn-primary:hover { background: #0088cc; }
        .btn-edit { background: #ffaa00; color: #000; }
        .btn-edit:hover { background: #e69900; }
        .btn-delete { background: #ff4444; color: #fff; }
        .btn-delete:hover { background: #cc0000; }
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
        th { background: #0a0a1a; color: #00aaff; }
        td { color: #ccc; }
        .empty { text-align: center; padding: 40px; color: #666; }
        .actions a { margin-right: 5px; padding: 4px 10px; border-radius: 4px; text-decoration: none; font-size: 12px; }
        .badge { padding: 3px 10px; border-radius: 12px; font-size: 12px; }
        .badge-active { background: #44ff88; color: #000; }
        .badge-inactive { background: #ff4444; color: #fff; }
    </style>
</head>
<body>
    <nav class="navbar">
        <h1>🎮 GamerVerse Admin</h1>
        <a href="../dashboard.php">← Back to Dashboard</a>
    </nav>

    <div class="container">
        <div class="header">
            <h2>📦 Manage Games</h2>
            <a href="add.php" class="btn btn-primary">+ Add New Game</a>
        </div>

        <?php if (empty($games)): ?>
            <div class="empty">
                <p>No games added yet.</p>
                <br>
                <a href="add.php" class="btn btn-primary">Add Your First Game</a>
            </div>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Developer</th>
                        <th>Genre</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($games as $game): ?>
                        <tr>
                            <td>#<?= $game['id'] ?></td>
                            <td><strong><?= htmlspecialchars($game['name']) ?></strong></td>
                            <td><?= htmlspecialchars($game['developer'] ?? 'N/A') ?></td>
                            <td><?= htmlspecialchars($game['genre'] ?? 'N/A') ?></td>
                            <td>
                                <span class="badge badge-<?= $game['is_active'] ? 'active' : 'inactive' ?>">
                                    <?= $game['is_active'] ? 'Active' : 'Inactive' ?>
                                </span>
                            </td>
                            <td class="actions">
                                <a href="edit.php?id=<?= $game['id'] ?>" class="btn btn-edit">Edit</a>
                                <a href="delete.php?id=<?= $game['id'] ?>" class="btn btn-delete" 
                                   onclick="return confirm('Delete this game?')">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>