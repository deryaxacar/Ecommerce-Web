<?php
require_once __DIR__ . '../../includes/header_router.php';

include_header();
require '../config/db.php';
include '../includes/auth.php';

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT c.product_id, p.price, c.quantity FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id=?");
$stmt->execute([$user_id]);
$items = $stmt->fetchAll();

$total = 0;
foreach ($items as $item) {
    $total += $item['price'] * $item['quantity'];
}

$pdo->prepare("INSERT INTO orders (user_id, total_price) VALUES (?, ?)")->execute([$user_id, $total]);
$order_id = $pdo->lastInsertId();

foreach ($items as $item) {
    $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)")
        ->execute([$order_id, $item['product_id'], $item['quantity'], $item['price']]);
}

$pdo->prepare("DELETE FROM cart WHERE user_id=?")->execute([$user_id]);
echo "<p>Sipariş başarıyla tamamlandı!</p>";

include '../includes/footer.php';
