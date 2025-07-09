<?php
// public/products.php

// 1) Veritabanı bağlantısını yap
require_once __DIR__ . '/../config/db.php';

// 2) Oturum ve yetki kontrolü
require_once __DIR__ . '/../includes/auth.php';
require_login();  // giriş yoksa login.php'ye yönlendirir

// 3) Header router ile uygun header'ı dahil et
require_once __DIR__ . '/../includes/header_router.php';
include_header();

// 4) Tüm ürünleri çek
$stmt = $pdo->query("SELECT * FROM products ORDER BY id DESC");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 4) Kullanıcı bilgisi ve parametreler
$user_id = $_SESSION['user_id'];
$action = $_GET['action'] ?? null;
$product_id = $_GET['id'] ?? null;
$newQty = $_POST['quantity'] ?? null;
$editingId = null;

// 9) Sepet içeriğini çek
$stmt = $pdo->prepare("
    SELECT p.id, p.name, p.price, p.image, c.quantity
    FROM cart c
    JOIN products p ON p.id = c.product_id
    WHERE c.user_id = ?
");
$stmt->execute([$user_id]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <title>Kullanıcı Kayıt</title>
    <link rel="stylesheet" href="../assets/products.css">
</head>

<body>
    <h1 style=" margin-bottom: 5px; ">Ürünler</h1><br>

    <?php if ($_SESSION['role'] === 'admin'): ?>
        <p style="text-align:left; margin-bottom:20px;">
            <a href="add_product.php"
                style="padding:10px 20px; background:#000; color:#fff; text-decoration:none; border-radius:4px;">
                + Ürün Ekle
            </a>
        </p>
    <?php endif; ?>

    <?php if (empty($products) && $_SESSION['role'] != 'admin'): ?>
        <p>Herhangi bir ürün yok.</p>
    <?php else: ?>

        <div class="products-grid">
            <?php foreach ($products as $p): ?>
                <div class="product-card">
                    <img src="<?= htmlspecialchars($p['image'] ?: 'placeholder.png') ?>"
                        alt="<?= htmlspecialchars($p['name']) ?>">
                    <h2><?= htmlspecialchars($p['name']) ?></h2>
                    <p><?= nl2br(htmlspecialchars($p['description'])) ?></p>
                    <p><strong>Fiyat:</strong> <?= number_format($p['price'], 2) ?> ₺</p>
                    <p><strong>Stok:</strong> <?= (int) $p['stock'] ?></p>

                    <?php if ($_SESSION['role'] === 'admin'): ?>
                        <p>
                            <a href="edit_product.php?id=<?= $p['id'] ?>">Güncelle</a>
                            <a href="delete_product.php?id=<?= $p['id'] ?>"
                                onclick="return confirm('Bu ürünü ve görselini silmek istediğinize emin misiniz?')">
                                Sil
                            </a>
                        </p>
                    <?php else: ?>
                        <form method="post" action="favorites.php">
                            <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                            <input type="hidden" name="action" value="add">
                            <button type="submit">❤ Favorilere Ekle</button>
                        </form>

                        <form method="post" action="cart.php?action=add&id=<?= $p['id'] ?>">
                            <button type="submit">🛒 Sepete Ekle</button>
                        </form>
                    <?php endif; ?>

                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>

</html>