<?php
require_once __DIR__ . '/../../bootstrap.php';

if (!isAdmin()) {
    redirect('../../auth/login.php');
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    die("Invalid game ID");
}

// Get game data
$query = "SELECT * FROM games WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$game = $stmt->get_result()->fetch_assoc();

if (!$game) {
    die("Game not found");
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $developer = trim($_POST['developer']);
    $genre = trim($_POST['genre']);
    $description = trim($_POST['description']);
    $release_date = $_POST['release_date'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    if (empty($name)) {
        $error = "Game name is required!";
    } else {
        $image_name = $game['cover_image'];
        if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] == 0) {
            $target_dir = '../../uploads/games/';
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            $image_name = time() . '_' . basename($_FILES['cover_image']['name']);
            move_uploaded_file($_FILES['cover_image']['tmp_name'], $target_dir . $image_name);
        }
        
        $query = "UPDATE games SET name=?, developer=?, genre=?, description=?, cover_image=?, release_date=?, is_active=? 
                  WHERE id=?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssssssii", $name, $developer, $genre, $description, $image_name, $release_date, $is_active, $id);
        
        if ($stmt->execute()) {
            $success = "Game updated successfully!";
            $game = $conn->query("SELECT * FROM games WHERE id = $id")->fetch_assoc();
        } else {
            $error = "Failed to update game: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Game - Admin</title>
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
        textarea { resize: vertical; min-height: 100px; }
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
        .checkbox-group { display: flex; align-items: center; gap: 10px; }
        .checkbox-group input { width: auto; }
        .checkbox-group label { margin: 0; cursor: pointer; }
        .current-image { margin: 10px 0; }
        .current-image img { max-width: 100px; border-radius: 6px; }
    </style>
</head>
<body>
    <nav class="navbar">
        <h1>🎮 GamerVerse Admin</h1>
        <a href="../dashboard.php">← Back to Dashboard</a>
    </nav>

    <div class="container">
        <div class="header">
            <h2>✏️ Edit Game: <?= htmlspecialchars($game['name']) ?></h2>
            <a href="list.php" class="btn btn-secondary">← Back to Games</a>
        </div>

        <?php if ($error): ?>
            <div class="error">❌ <?= $error ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="success">✅ <?= $success ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label>Game Name *</label>
                <input type="text" name="name" value="<?= htmlspecialchars($game['name']) ?>" required>
            </div>
            
            <div class="form-group">
                <label>Developer</label>
                <input type="text" name="developer" value="<?= htmlspecialchars($game['developer'] ?? '') ?>">
            </div>
            
            <div class="form-group">
                <label>Genre</label>
                <select name="genre">
                    <option value="">Select Genre</option>
                    <option value="Action" <?= $game['genre'] == 'Action' ? 'selected' : '' ?>>Action</option>
                    <option value="Adventure" <?= $game['genre'] == 'Adventure' ? 'selected' : '' ?>>Adventure</option>
                    <option value="RPG" <?= $game['genre'] == 'RPG' ? 'selected' : '' ?>>RPG</option>
                    <option value="FPS" <?= $game['genre'] == 'FPS' ? 'selected' : '' ?>>FPS</option>
                    <option value="Strategy" <?= $game['genre'] == 'Strategy' ? 'selected' : '' ?>>Strategy</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Description</label>
                <textarea name="description"><?= htmlspecialchars($game['description'] ?? '') ?></textarea>
            </div>
            
            <?php if ($game['cover_image']): ?>
                <div class="form-group current-image">
                    <label>Current Image</label>
                    <img src="../../uploads/games/<?= $game['cover_image'] ?>">
                </div>
            <?php endif; ?>
            
            <div class="form-group">
                <label>Change Image (optional)</label>
                <input type="file" name="cover_image" accept="image/*">
            </div>
            
            <div class="form-group">
                <label>Release Date</label>
                <input type="date" name="release_date" value="<?= $game['release_date'] ?? '' ?>">
            </div>
            
            <div class="form-group checkbox-group">
                <input type="checkbox" name="is_active" id="is_active" <?= $game['is_active'] ? 'checked' : '' ?>>
                <label for="is_active">Game is Active</label>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">💾 Update Game</button>
                <a href="list.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</body>
</html>