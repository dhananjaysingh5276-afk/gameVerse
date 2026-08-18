<?php
require_once __DIR__ . '/../../bootstrap.php';

if (!isAdmin()) {
    redirect('../../auth/login.php');
}

// Get all games for dropdown
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
        $query = "INSERT INTO items (game_id, name, category, price, skill_value, attribute, special_effect, description, stock, image_url) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ississssis", $game_id, $name, $category, $price, $skill_value, $attribute, $special_effect, $description, $stock, $image_url);
        
        if ($stmt->execute()) {
            $success = "Item added successfully!";
        } else {
            $error = "Failed to add item: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Item - Admin</title>
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
    </style>
</head>
<body>
    <nav class="navbar">
        <h1>🎮 GamerVerse Admin</h1>
        <a href="../dashboard.php">← Back to Dashboard</a>
    </nav>

    <div class="container">
        <div class="header">
            <h2>➕ Add New Item</h2>
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
                        <option value="<?= $game['id'] ?>"><?= htmlspecialchars($game['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>Item Name <span class="required">*</span></label>
                <input type="text" name="name" placeholder="e.g. Cyber Blade" required>
            </div>
            
            <div class="form-group">
                <label>Category <span class="required">*</span></label>
                <select name="category" required>
                    <option value="">Select Category</option>
                    <option value="weapon">⚔️ Weapon</option>
                    <option value="costume">👔 Costume</option>
                    <option value="gem">💎 Gem</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Price (Coins) <span class="required">*</span></label>
                <input type="number" name="price" placeholder="e.g. 500" min="1" required>
            </div>
            
            <div class="form-group">
                <label>Skill Value</label>
                <input type="text" name="skill_value" placeholder="e.g. +15 Attack">
                <div class="help-text">What skill this item provides</div>
            </div>
            
            <div class="form-group">
                <label>Attribute</label>
                <input type="text" name="attribute" placeholder="e.g. Fire, Electric, Royal">
                <div class="help-text">Element or type attribute</div>
            </div>
            
            <div class="form-group">
                <label>Special Effect</label>
                <input type="text" name="special_effect" placeholder="e.g. 10% chance to stun">
                <div class="help-text">Special ability or effect</div>
            </div>
            
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" placeholder="Describe the item..."></textarea>
            </div>
            
            <div class="form-group">
                <label>Stock Quantity</label>
                <input type="number" name="stock" value="10" min="0">
            </div>
            
            <div class="form-group">
                <label>Image URL</label>
                <input type="text" name="image_url" placeholder="https://example.com/image.png or item name">
                <div class="help-text">URL to item image or just a placeholder name</div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">✅ Add Item</button>
                <a href="list.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</body>
</html>