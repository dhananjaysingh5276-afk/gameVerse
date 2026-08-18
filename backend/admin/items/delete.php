<?php
require_once __DIR__ . '/../../bootstrap.php';

if (!isAdmin()) {
    redirect('../../auth/login.php');
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    die("Invalid item ID");
}

// Get item name for confirmation
$query = "SELECT name FROM items WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$item = $stmt->get_result()->fetch_assoc();

if (!$item) {
    die("Item not found");
}

// Delete the item
$delete = $conn->prepare("DELETE FROM items WHERE id = ?");
$delete->bind_param("i", $id);

if ($delete->execute()) {
    $_SESSION['success'] = "Item '" . $item['name'] . "' deleted successfully!";
} else {
    $_SESSION['error'] = "Failed to delete item: " . $conn->error;
}

header('Location: list.php');
exit();
?>