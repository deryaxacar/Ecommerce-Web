<?php
require '../config/db.php';
require '../includes/auth.php';
if (isset($_GET['id'])) {
    $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
    $stmt->execute([$_GET['id'], $_SESSION['user_id']]);
}
header("Location: " . $_SERVER['HTTP_REFERER']);
