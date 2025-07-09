<?php
session_start();
require '../config/db.php';
include '../includes/auth.php';
require_admin();  // Silme işini sadece admin yapsın istiyorsan

// 1) ID parametresi kontrolü
if (!isset($_GET['id'])) {
    header('Location: products.php');
    exit;
}

$id = (int) $_GET['id'];

// 2) Önce resim yolunu al
$stmt = $pdo->prepare("SELECT image FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if ($product) {
    // 3) Diskteki dosyayı sil
    $file = __DIR__ . '/' . $product['image'];
    if ($product['image'] && file_exists($file)) {
        unlink($file);
    }

    // 4) Veritabanından kaydı sil
    $del = $pdo->prepare("DELETE FROM products WHERE id = ?");
    $del->execute([$id]);
}

// 5) Geri yönlendir
header('Location: products.php');
exit;
