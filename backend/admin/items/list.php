<?php
require_once __DIR__ . '/../../bootstrap.php';

// Redirect if not admin
if (!isAdmin()) {
    redirect('../../auth/login.php');
}

// Get filter parameters
$game_filter = isset($_GET['game']) ? intval($_GET['game']) : 0;

// Build query
$query = "SELECT i.*, g.name as game_name 
          FROM items i 
          JOIN games g ON i.game_id = g.id";

if ($game_filter > 0) {
    $query .= " WHERE i.game_id = $game_filter";
}

$query .= " ORDER BY i.created_at DESC";

$items = $conn->query($query)->fetch_all(MYSQLI_ASSOC);

// Get all games for filter dropdown
$games = $conn->query("SELECT id, name FROM games ORDER BY name")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Items - Admin</title>
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
        .container { max-width: 1400px; margin: 30px auto; padding: 0 20px; }
        .header { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; margin-bottom: 20px; }
        .btn {
            padding: 10px 20px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            display: inline-block;
            border: none;
            cursor: pointer;
            font-size: 14px;
        }
        .btn-primary { background: #00aaff; color: #fff; }
        .btn-primary:hover { background: #0088cc; }
        .btn-edit { background: #ffaa00; color: #000; }
        .btn-edit:hover { background: #e69900; }
        .btn-delete { background: #ff4444; color: #fff; }
        .btn-delete:hover { background: #cc0000; }
        .btn-secondary { background: #555; color: #fff; }
        .btn-secondary:hover { background: #444; }
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
        th { background: #0a0a1a; color: #00aaff; font-size: 13px; }
        td { color: #ccc; font-size: 14px; }
        .item-image { width: 40px; height: 40px; object-fit: cover; border-radius: 6px; }
        .actions a { margin-right: 5px; padding: 4px 10px; border-radius: 4px; text-decoration: none; font-size: 12px; }
        .empty { text-align: center; padding: 40px; color: #666; }
        .badge { padding: 3px 10px; border-radius: 12px; font-size: 11px; }
        .badge-weapon { background: #ff4444; color: #fff; }
        .badge-costume { background: #44aaff; color: #fff; }
        .badge-gem { background: #ffaa00; color: #000; }
        .filter-bar {
            background: #1a1a2e;
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
            border: 1px solid #2a2a4e;
        }
        .filter-bar label { color: #888; }
        .filter-bar select {
            padding: 8px 15px;
            border-radius: 6px;
            border: 1px solid #2a2a4e;
            background: #0a0a1a;
            color: #fff;
        }
        .filter-bar select:focus { outline: none; border-color: #00aaff; }
        .stats { color: #888; font-size: 14px; }
        .stats strong { color: #fff; }
    </style>
</head>
<body>
    <nav class="navbar">
        <h1>🎮 GamerVerse Admin</h1>
        <a href="../dashboard.php">← Back to Dashboard</a>
    </nav>

    <div class="container">
        <div class="header">
            <h2>📦 Manage Items</h2>
            <a href="add.php" class="btn btn-primary">+ Add New Item</a>
        </div>

        <!-- Filter Bar -->
        <div class="filter-bar">
            <label>Filter by Game:</label>
            <select onchange="window.location.href='list.php?game='+this.value">
                <option value="0">All Games</option>
                <?php foreach ($games as $game): ?>
                    <option value="<?= $game['id'] ?>" <?= $game_filter == $game['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($game['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <span class="stats">Total: <strong><?= count($items) ?></strong> items</span>
            <a href="list.php" class="btn btn-secondary" style="padding: 6px 15px;">Clear Filter</a>
        </div>

        <?php if (empty($items)): ?>
            <div class="empty">
                <p>No items found.</p>
                <br>
                <a href="add.php" class="btn btn-primary">Add Your First Item</a>
            </div>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Game</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Skill</th>
                        <th>Stock</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td>
                                <img src="<?= $item['image_url'] ?? 'default.png' ?>" 
                                     alt="<?= $item['name'] ?>" 
                                     class="item-image">
                            </td>
                            <td><strong><?= htmlspecialchars($item['name']) ?></strong></td>
                            <td><?= htmlspecialchars($item['game_name']) ?></td>
                            <td>
                                <span class="badge badge-<?= $item['category'] ?>">
                                    <?= $item['category'] ?>
                                </span>
                            </td>
                            <td>🪙 <?= number_format($item['price']) ?></td>
                            <td><?= htmlspecialchars($item['skill_value'] ?? '-') ?></td>
                            <td><?= $item['stock'] ?></td>
                            <td class="actions">
                                <a href="edit.php?id=<?= $item['id'] ?>" class="btn btn-edit">Edit</a>
                                <a href="delete.php?id=<?= $item['id'] ?>" class="btn btn-delete" 
                                   onclick="return confirm('Delete this item?')">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>