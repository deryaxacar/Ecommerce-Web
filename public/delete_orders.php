<?php
require '../config/db.php';
include '../includes/auth.php';

if (isset($_GET['order_id'])) {
    $orderId = (int) $_GET['order_id'];
    $userId = $_SESSION['user_id'];

    // 1) Önce sipariş öğelerini sil
    $stmt = $pdo->prepare("
      DELETE FROM order_items
      WHERE order_id = ?
        AND order_id IN (
          SELECT id FROM orders WHERE user_id = ?
        )
    ");
    $stmt->execute([$orderId, $userId]);

    // 2) Ardından siparişi sil
    $stmt2 = $pdo->prepare("
      DELETE FROM orders
      WHERE id = ?
        AND user_id = ?
    ");
    $stmt2->execute([$orderId, $userId]);
}

// Silme işleminden sonra siparişler sayfasına geri dön
header('Location: orders.php');
exit;
