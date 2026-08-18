<?php
require_once __DIR__ . '/../../bootstrap.php';

if (!isAdmin()) {
    redirect('../../auth/login.php');
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    die("Invalid item ID");
}

// Get item data
$query = "SELECT * FROM items WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$item = $stmt->get_result()->fetch_assoc();

if (!$item) {
    die("Item not found");
}

// Get games for dropdown
$games = $conn->query("SELECT id, name FROM games ORDER BY name")->fetch_all(MYSQLI_ASSOC);

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $game_id = intval($_POST['game_id']);
    $name = trim($_POST['name']);
    $category = $_POST['category'];
    $price = intval($_POST['price']);
    $skill_value = trim($_POST['skill_value']);
    $attribute = trim($_POST['attribute']);
    $special_effect = trim($_POST['special_effect']);
    $description = trim($_POST['description']);
    $stock = intval($_POST['stock']);
    $image_url = trim($_POST['image_url']);
    
    if (empty($game_id) || empty($name) || empty($category) || $price <= 0) {
        $error = "Please fill in all required fields!";
    } else {
        $query = "UPDATE items SET game_id=?, name=?, category=?, price=?, skill_value=?, attribute=?, special_effect=?, description=?, stock=?, image_url=? 
                  WHERE id=?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ississssisi", $game_id, $name, $category, $price, $skill_value, $attribute, $special_effect, $description, $stock, $image_url, $id);
        
        if ($stmt->execute()) {
            $success = "Item updated successfully!";
            // Refresh item data
            $item = $conn->query("SELECT * FROM items WHERE id = $id")->fetch_assoc();
        } else {
            $error = "Failed to update item: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Item - Admin</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #0a0a0a; color: #fff; }
        .navbar {
            background: #1a1a2e;
            padding: 15px 30px;
            border-bottom: 2px solid #2a2a4e;
        }
        .navbar h1 { color: #00aaff; }
        .navbar a { color: #00aaff; text-decoration: none; }
        .container { max-width: 600px; margin: 30px auto; padding: 0 20px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; color: #ccc; margin-bottom: 6px; font-weight: 500; }
        input, select, textarea {
            width: 100%;
            padding: 10px 15px;
            border: 2px solid #2a2a4e;
            border-radius: 6px;
            background: #0a0a1a;
            color: #fff;
            font-size: 14px;
        }
        input:focus, select:focus, textarea:focus { outline: none; border-color: #00aaff; }
        textarea { resize: vertical; min-height: 80px; }
        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            font-size: 16px;
        }
        .btn-primary { background: #00aaff; color: #fff; }
        .btn-primary:hover { background: #0088cc; }
        .btn-secondary { background: #555; color: #fff; }
        .btn-secondary:hover { background: #444; }
        .error { color: #ff4444; background: rgba(255,68,68,0.1); padding: 12px; border-radius: 6px; margin-bottom: 15px; border-left: 4px solid #ff4444; }
        .success { color: #44ff88; background: rgba(68,255,136,0.1); padding: 12px; border-radius: 6px; margin-bottom: 15px; border-left: 4px solid #44ff88; }
        .form-actions { display: flex; gap: 15px; margin-top: 20px; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .required { color: #ff4444; }
        .help-text { color: #666; font-size: 12px; margin-top: 4px; }
        .current-info { background: #0a0a1a; padding: 15px; border-radius: 6px; border: 1px solid #2a2a4e; margin-bottom: 20px; }
        .current-info span { color: #888; }
        .current-info strong { color: #fff; }
    </style>
</head>
<body>
    <nav class="navbar">
        <h1>🎮 GamerVerse Admin</h1>
        <a href="../dashboard.php">← Back to Dashboard</a>
    </nav>

    <div class="container">
        <div class="header">
            <h2>✏️ Edit Item: <?= htmlspecialchars($item['name']) ?></h2>
            <a href="list.php" class="btn btn-secondary">← Back to Items</a>
        </div>

        <?php if ($error): ?>
            <div class="error">❌ <?= $error ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="success">✅ <?= $success ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Game <span class="required">*</span></label>
                <select name="game_id" required>
                    <option value="">Select Game</option>
                    <?php foreach ($games as $game): ?>
                        <option value="<?= $game['id'] ?>" <?= $item['game_id'] == $game['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($game['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>Item Name <span class="required">*</span></label>
                <input type="text" name="name" value="<?= htmlspecialchars($item['name']) ?>" required>
            </div>
            
            <div class="form-group">
                <label>Category <span class="required">*</span></label>
                <select name="category" required>
                    <option value="">Select Category</option>
                    <option value="weapon" <?= $item['category'] == 'weapon' ? 'selected' : '' ?>>⚔️ Weapon</option>
                    <option value="costume" <?= $item['category'] == 'costume' ? 'selected' : '' ?>>👔 Costume</option>
                    <option value="gem" <?= $item['category'] == 'gem' ? 'selected' : '' ?>>💎 Gem</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Price (Coins) <span class="required">*</span></label>
                <input type="number" name="price" value="<?= $item['price'] ?>" min="1" required>
            </div>
            
            <div class="form-group">
                <label>Skill Value</label>
                <input type="text" name="skill_value" value="<?= htmlspecialchars($item['skill_value'] ?? '') ?>" placeholder="e.g. +15 Attack">
                <div class="help-text">What skill this item provides</div>
            </div>
            
            <div class="form-group">
                <label>Attribute</label>
                <input type="text" name="attribute" value="<?= htmlspecialchars($item['attribute'] ?? '') ?>" placeholder="e.g. Fire, Electric, Royal">
            </div>
            
            <div class="form-group">
                <label>Special Effect</label>
                <input type="text" name="special_effect" value="<?= htmlspecialchars($item['special_effect'] ?? '') ?>" placeholder="e.g. 10% chance to stun">
            </div>
            
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" placeholder="Describe the item..."><?= htmlspecialchars($item['description'] ?? '') ?></textarea>
            </div>
            
            <div class="form-group">
                <label>Stock Quantity</label>
                <input type="number" name="stock" value="<?= $item['stock'] ?>" min="0">
            </div>
            
            <div class="form-group">
                <label>Image URL</label>
                <input type="text" name="image_url" value="<?= htmlspecialchars($item['image_url'] ?? '') ?>" placeholder="https://example.com/image.png">
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">💾 Update Item</button>
                <a href="list.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</body>
</html>