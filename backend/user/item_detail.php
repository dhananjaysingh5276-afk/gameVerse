<?php
require_once __DIR__ . '/../bootstrap.php';

// Redirect if not logged in
if (!isLoggedIn()) {
    redirect('../auth/login.php');
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    die("Invalid item ID");
}

// Get item details
$query = "SELECT i.*, g.name as game_name, g.developer, g.genre 
          FROM items i 
          JOIN games g ON i.game_id = g.id 
          WHERE i.id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$item = $stmt->get_result()->fetch_assoc();

if (!$item) {
    die("Item not found");
}

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
    <title><?= htmlspecialchars($item['name']) ?> - GamerVerse</title>
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
        .navbar h1 { color: #00aaff; cursor: pointer; font-size: 22px; }
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

        .container { max-width: 800px; margin: 30px auto; padding: 0 20px; }

        .back-link {
            display: inline-block;
            color: #00aaff;
            text-decoration: none;
            margin-bottom: 20px;
        }
        .back-link:hover { text-decoration: underline; }

        .item-card {
            background: #1a1a2e;
            border-radius: 12px;
            border: 1px solid #2a2a4e;
            overflow: hidden;
        }
        .item-card .item-image {
            width: 100%;
            height: 300px;
            background: #0a0a1a;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 100px;
        }
        .item-card .item-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .item-card .item-body {
            padding: 30px;
        }
        .item-card .item-body h1 {
            font-size: 28px;
            margin-bottom: 5px;
        }
        .item-card .item-body .game-name {
            color: #888;
            font-size: 16px;
            margin-bottom: 15px;
        }
        .item-card .item-body .game-name a {
            color: #00aaff;
            text-decoration: none;
        }
        .item-card .item-body .game-name a:hover { text-decoration: underline; }

        .item-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin: 20px 0;
        }
        .detail-item {
            background: #0a0a1a;
            padding: 12px 15px;
            border-radius: 8px;
            border: 1px solid #2a2a4e;
        }
        .detail-item .label {
            color: #888;
            font-size: 12px;
            text-transform: uppercase;
        }
        .detail-item .value {
            color: #fff;
            font-size: 16px;
            font-weight: 600;
            margin-top: 2px;
        }
        .detail-item .value.weapon { color: #ff4444; }
        .detail-item .value.costume { color: #44aaff; }
        .detail-item .value.gem { color: #ffaa00; }

        .price-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            background: #0a0a1a;
            border-radius: 8px;
            margin-top: 20px;
            border: 1px solid #2a2a4e;
        }
        .price-section .price {
            font-size: 24px;
            font-weight: 700;
            color: #ffd700;
        }
        .price-section .balance {
            color: #888;
        }
        .price-section .btn {
            padding: 12px 30px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            font-size: 16px;
            transition: 0.3s;
        }
        .price-section .btn-buy {
            background: linear-gradient(135deg, #00aaff, #0088cc);
            color: #fff;
        }
        .price-section .btn-buy:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,170,255,0.3);
        }
        .price-section .btn-buy:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }
        .price-section .btn-own {
            background: #44ff88;
            color: #000;
            cursor: default;
        }
        .price-section .btn-own:hover { transform: none; }

        .badge {
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 12px;
        }
        .badge-weapon { background: #ff4444; color: #fff; }
        .badge-costume { background: #44aaff; color: #fff; }
        .badge-gem { background: #ffaa00; color: #000; }

        .description {
            margin-top: 20px;
            padding: 20px;
            background: #0a0a1a;
            border-radius: 8px;
            border: 1px solid #2a2a4e;
        }
        .description h3 { color: #00aaff; margin-bottom: 10px; }
        .description p { color: #ccc; line-height: 1.6; }

        @media (max-width: 768px) {
            .item-details { grid-template-columns: 1fr; }
            .price-section { flex-direction: column; gap: 15px; text-align: center; }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <h1 onclick="window.location.href='dashboard.php'">🎮 GamerVerse</h1>
        <div class="nav-links">
            <a href="../user/dashboard.php">🏠 Dashboard</a>
            <a href="../user/browse.php">🛒 Marketplace</a>
            <span class="coins">🪙 <?= number_format($user_coins) ?></span>
            <a href="../auth/logout.php" class="logout">Logout</a>
        </div>
    </nav>

    <div class="container">
        <a href="browse.php" class="back-link">← Back to Marketplace</a>

        <div class="item-card">
            <div class="item-image">
                <img src="<?= $item['image_url'] ?? 'default.png' ?>" alt="<?= $item['name'] ?>" 
                     onerror="this.parentElement.innerHTML='🎮'">
            </div>
            <div class="item-body">
                <span class="badge badge-<?= $item['category'] ?>">
                    <?= ucfirst($item['category']) ?>
                </span>
                <h1><?= htmlspecialchars($item['name']) ?></h1>
                <div class="game-name">
                    🎮 <a href="browse.php?game=<?= $item['game_id'] ?>"><?= htmlspecialchars($item['game_name']) ?></a>
                </div>

                <div class="item-details">
                    <div class="detail-item">
                        <div class="label">Skill Value</div>
                        <div class="value"><?= htmlspecialchars($item['skill_value'] ?? 'N/A') ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="label">Attribute</div>
                        <div class="value"><?= htmlspecialchars($item['attribute'] ?? 'N/A') ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="label">Special Effect</div>
                        <div class="value"><?= htmlspecialchars($item['special_effect'] ?? 'None') ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="label">Stock</div>
                        <div class="value"><?= $item['stock'] > 0 ? $item['stock'] . ' available' : 'Out of Stock' ?></div>
                    </div>
                </div>

                <?php if ($item['description']): ?>
                    <div class="description">
                        <h3>📝 Description</h3>
                        <p><?= nl2br(htmlspecialchars($item['description'])) ?></p>
                    </div>
                <?php endif; ?>

                <div class="price-section">
                    <div>
                        <div class="price">🪙 <?= number_format($item['price']) ?></div>
                        <div class="balance">Your balance: 🪙 <?= number_format($user_coins) ?></div>
                    </div>
                    
                    <?php
                    // Check if user already owns this item
                    $check_query = "SELECT id FROM inventory WHERE user_id = ? AND item_id = ?";
                    $stmt = $conn->prepare($check_query);
                    $stmt->bind_param("ii", $_SESSION['user_id'], $item['id']);
                    $stmt->execute();
                    $owns_item = $stmt->get_result()->num_rows > 0;
                    ?>
                    
                    <?php if ($owns_item): ?>
                        <button class="btn btn-own">✅ You Own This Item</button>
                    <?php elseif ($item['stock'] <= 0): ?>
                        <button class="btn btn-buy" disabled>❌ Out of Stock</button>
                    <?php else: ?>
                        <a href="../purchase/buy.php?id=<?= $item['id'] ?>">
                            <button class="btn btn-buy" <?= $user_coins < $item['price'] ? 'disabled' : '' ?>>
                                Buy Now (<?= number_format($item['price']) ?> Coins)
                            </button>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>