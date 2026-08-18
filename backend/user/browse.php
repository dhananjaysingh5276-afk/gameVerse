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

// Get filter parameters
$game_filter = isset($_GET['game']) ? intval($_GET['game']) : 0;
$category_filter = isset($_GET['category']) ? $_GET['category'] : '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';

// Build query
$query = "SELECT i.*, g.name as game_name 
          FROM items i 
          JOIN games g ON i.game_id = g.id 
          WHERE i.stock > 0";

$params = [];
$types = "";

if ($game_filter > 0) {
    $query .= " AND i.game_id = ?";
    $params[] = $game_filter;
    $types .= "i";
}

if ($category_filter) {
    $query .= " AND i.category = ?";
    $params[] = $category_filter;
    $types .= "s";
}

if ($search) {
    $query .= " AND (i.name LIKE ? OR i.description LIKE ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
    $types .= "ss";
}

// Sorting
switch ($sort) {
    case 'price_low':
        $query .= " ORDER BY i.price ASC";
        break;
    case 'price_high':
        $query .= " ORDER BY i.price DESC";
        break;
    default:
        $query .= " ORDER BY i.created_at DESC";
}

// Execute query
$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get all games for filter
$games = $conn->query("SELECT id, name FROM games WHERE is_active = 1 ORDER BY name")->fetch_all(MYSQLI_ASSOC);

// Get user's coins
$user_query = "SELECT coins FROM users WHERE id = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$user_coins = $stmt->get_result()->fetch_assoc()['coins'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Marketplace - GamerVerse</title>
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
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .navbar h1 { color: #00aaff; cursor: pointer; font-size: 22px; }
        .navbar h1:hover { color: #33bbff; }
        .navbar .nav-links {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .navbar .nav-links a {
            color: #ccc;
            text-decoration: none;
            padding: 8px 15px;
            border-radius: 6px;
            transition: 0.3s;
            font-size: 14px;
        }
        .navbar .nav-links a:hover { background: #2a2a4e; color: #fff; }
        .navbar .nav-links .logout {
            color: #ff4444;
            border: 1px solid #ff4444;
        }
        .navbar .nav-links .logout:hover {
            background: #ff4444;
            color: #fff;
        }
        .navbar .coins {
            background: linear-gradient(135deg, #ffd700, #ffaa00);
            color: #000;
            padding: 6px 15px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 14px;
        }

        .container { max-width: 1400px; margin: 20px auto; padding: 0 20px; }

        .filter-bar {
            background: #1a1a2e;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            border: 1px solid #2a2a4e;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 15px;
        }
        .filter-bar select, .filter-bar input {
            padding: 10px 15px;
            border-radius: 6px;
            border: 1px solid #2a2a4e;
            background: #0a0a1a;
            color: #fff;
            font-size: 14px;
            min-width: 150px;
        }
        .filter-bar select:focus, .filter-bar input:focus {
            outline: none;
            border-color: #00aaff;
        }
        .filter-bar .search-box {
            flex: 1;
            min-width: 200px;
        }
        .filter-bar .btn {
            padding: 10px 20px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            font-weight: 600;
            background: #00aaff;
            color: #fff;
            transition: 0.3s;
        }
        .filter-bar .btn:hover { background: #0088cc; }
        .filter-bar .btn-secondary {
            background: #555;
        }
        .filter-bar .btn-secondary:hover { background: #444; }

        .items-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .items-header h2 { color: #00aaff; }
        .items-header .count { color: #888; }

        .items-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 20px;
        }

        .item-card {
            background: #1a1a2e;
            border-radius: 10px;
            border: 1px solid #2a2a4e;
            overflow: hidden;
            transition: 0.3s;
        }
        .item-card:hover {
            transform: translateY(-5px);
            border-color: #00aaff;
            box-shadow: 0 10px 30px rgba(0,170,255,0.1);
        }
        .item-card .item-image {
            width: 100%;
            height: 150px;
            background: #0a0a1a;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 60px;
        }
        .item-card .item-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .item-card .item-body {
            padding: 15px;
        }
        .item-card .item-body h3 {
            font-size: 16px;
            margin-bottom: 5px;
        }
        .item-card .item-body .game-name {
            color: #888;
            font-size: 13px;
            margin-bottom: 8px;
        }
        .item-card .item-body .item-stats {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
            margin: 8px 0;
        }
        .item-card .item-body .item-stats span {
            background: #0a0a1a;
            padding: 2px 10px;
            border-radius: 12px;
            font-size: 11px;
            color: #00aaff;
            border: 1px solid #2a2a4e;
        }
        .item-card .item-body .price {
            font-size: 18px;
            font-weight: 700;
            color: #ffd700;
            margin: 8px 0;
        }
        .item-card .item-body .special-effect {
            font-size: 12px;
            color: #aa88ff;
            margin-bottom: 8px;
        }
        .item-card .item-body .btn {
            width: 100%;
            padding: 10px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            transition: 0.3s;
            text-align: center;
            display: block;
            text-decoration: none;
        }
        .item-card .item-body .btn-view {
            background: #00aaff;
            color: #fff;
        }
        .item-card .item-body .btn-view:hover {
            background: #0088cc;
        }

        .badge {
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 11px;
        }
        .badge-weapon { background: #ff4444; color: #fff; }
        .badge-costume { background: #44aaff; color: #fff; }
        .badge-gem { background: #ffaa00; color: #000; }

        .empty {
            text-align: center;
            padding: 60px;
            color: #666;
            background: #1a1a2e;
            border-radius: 10px;
            border: 1px dashed #2a2a4e;
        }
        .empty .icon { font-size: 60px; margin-bottom: 20px; }

        @media (max-width: 768px) {
            .filter-bar { flex-direction: column; }
            .filter-bar select, .filter-bar input, .filter-bar .search-box {
                width: 100%;
                min-width: auto;
            }
            .items-grid {
                grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
            }
            .navbar { flex-wrap: wrap; gap: 10px; }
            .navbar .nav-links { flex-wrap: wrap; justify-content: center; }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <h1 onclick="window.location.href='dashboard.php'">🎮 GamerVerse</h1>
        <div class="nav-links">
            <a href="../user/dashboard.php">🏠 Dashboard</a>
            <a href="../user/browse.php" style="background:#2a2a4e;">🛒 Marketplace</a>
            <span class="coins">🪙 <?= number_format($user_coins) ?></span>
            <a href="../auth/logout.php" class="logout">Logout</a>
        </div>
    </nav>

    <div class="container">
        <form method="GET" class="filter-bar">
            <select name="game">
                <option value="0">All Games</option>
                <?php foreach ($games as $game): ?>
                    <option value="<?= $game['id'] ?>" <?= $game_filter == $game['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($game['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <select name="category">
                <option value="">All Categories</option>
                <option value="weapon" <?= $category_filter == 'weapon' ? 'selected' : '' ?>>⚔️ Weapons</option>
                <option value="costume" <?= $category_filter == 'costume' ? 'selected' : '' ?>>👔 Costumes</option>
                <option value="gem" <?= $category_filter == 'gem' ? 'selected' : '' ?>>💎 Gems</option>
            </select>

            <select name="sort">
                <option value="newest" <?= $sort == 'newest' ? 'selected' : '' ?>>Newest</option>
                <option value="price_low" <?= $sort == 'price_low' ? 'selected' : '' ?>>Price: Low to High</option>
                <option value="price_high" <?= $sort == 'price_high' ? 'selected' : '' ?>>Price: High to Low</option>
            </select>

            <input type="text" name="search" class="search-box" placeholder="🔍 Search items..." value="<?= htmlspecialchars($search) ?>">

            <button type="submit" class="btn">Apply Filters</button>
            <a href="browse.php" class="btn btn-secondary">Reset</a>
        </form>

        <div class="items-header">
            <h2>🛒 Marketplace</h2>
            <span class="count"><?= count($items) ?> items available</span>
        </div>

        <?php if (empty($items)): ?>
            <div class="empty">
                <div class="icon">🎯</div>
                <h3>No items found</h3>
                <p>Try adjusting your filters or check back later.</p>
            </div>
        <?php else: ?>
            <div class="items-grid">
                <?php foreach ($items as $item): ?>
                    <div class="item-card">
                        <div class="item-image">
                            <img src="<?= $item['image_url'] ?? 'default.png' ?>" alt="<?= $item['name'] ?>" 
                                 onerror="this.parentElement.innerHTML='🎮'">
                        </div>
                        <div class="item-body">
                            <h3><?= htmlspecialchars($item['name']) ?></h3>
                            <div class="game-name">🎮 <?= htmlspecialchars($item['game_name']) ?></div>
                            
                            <span class="badge badge-<?= $item['category'] ?>">
                                <?= $item['category'] ?>
                            </span>

                            <?php if ($item['skill_value'] || $item['attribute']): ?>
                                <div class="item-stats">
                                    <?php if ($item['skill_value']): ?>
                                        <span>⚡ <?= htmlspecialchars($item['skill_value']) ?></span>
                                    <?php endif; ?>
                                    <?php if ($item['attribute']): ?>
                                        <span>🔥 <?= htmlspecialchars($item['attribute']) ?></span>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>

                            <?php if ($item['special_effect']): ?>
                                <div class="special-effect">✨ <?= htmlspecialchars($item['special_effect']) ?></div>
                            <?php endif; ?>

                            <div class="price">🪙 <?= number_format($item['price']) ?></div>
                            
                            <a href="item_detail.php?id=<?= $item['id'] ?>" class="btn btn-view">
                                View Details
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>