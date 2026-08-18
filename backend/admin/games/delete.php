<?php
require_once __DIR__ . '/../../bootstrap.php';

if (!isAdmin()) {
    redirect('../../auth/login.php');
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    die("Invalid game ID");
}

// Get game name
$query = "SELECT name FROM games WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$game = $stmt->get_result()->fetch_assoc();

if (!$game) {
    die("Game not found");
}

// Delete game (items will be deleted due to CASCADE)
$delete = $conn->prepare("DELETE FROM games WHERE id = ?");
$delete->bind_param("i", $id);

if ($delete->execute()) {
    $_SESSION['success'] = "Game '" . $game['name'] . "' deleted successfully!";
} else {
    $_SESSION['error'] = "Failed to delete game: " . $conn->error;
}

header('Location: list.php');
exit();
?>