<?php
// public/favorites.php

// 1) Veritabanı bağlantısını yap
require_once __DIR__ . '/../config/db.php';

// 2) Oturum kontrolü
require_once __DIR__ . '/../includes/auth.php';
require_login();

// 3) Header router ile uygun header'ı dahil et
require_once __DIR__ . '/../includes/header_router.php';
include_header();

$user_id = $_SESSION['user_id'];

// 4) Favori ekle/kaldır (POST ile)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['product_id'])) {
    $product_id = (int) $_POST['product_id'];
    if ($_POST['action'] === 'add') {
        // Aynı ürünü bir kere ekle
        $check = $pdo->prepare(
            "SELECT COUNT(*) FROM favorites WHERE user_id = ? AND product_id = ?"
        );
        $check->execute([$user_id, $product_id]);
        if ($check->fetchColumn() == 0) {
            $stmt = $pdo->prepare(
                "INSERT INTO favorites (user_id, product_id) VALUES (?, ?)"
            );
            $stmt->execute([$user_id, $product_id]);
        }
    } elseif ($_POST['action'] === 'remove') {
        $stmt = $pdo->prepare(
            "DELETE FROM favorites WHERE user_id = ? AND product_id = ?"
        );
        $stmt->execute([$user_id, $product_id]);
    }
    header('Location: favorites.php');
    exit;
}

// 5) Favorileri getir (image, description, price dahil)
$stmt = $pdo->prepare(
    "SELECT p.id, p.name, p.stock, p.image, p.description, p.price
     FROM favorites f
     JOIN products p ON f.product_id = p.id
     WHERE f.user_id = ?"
);
$stmt->execute([$user_id]);
$favs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Favoriler</title>
    <link rel="stylesheet" href="../assets/products.css">
</head>

<body>
    <h1>Favoriler</h1>

    <?php if (empty($favs)): ?>
        <p>Henüz favori ürün yok.</p>
    <?php else: ?>
        <div class="products-grid">
            <?php foreach ($favs as $f): ?>
                <div class="product-card">
                    <img src="<?= htmlspecialchars($f['image'] ?: 'placeholder.png') ?>"
                        alt="<?= htmlspecialchars($f['name']) ?>">
                    <h2 style="margin:10px 0;"><?= htmlspecialchars($f['name']) ?></h2>
                    <p>
                        <?= nl2br(htmlspecialchars($f['description'])) ?>
                    </p>
                    <p><strong>Fiyat:</strong> <?= number_format($f['price'], 2) ?> ₺</p>
                    <p><strong>Stok:</strong> <?= (int) $f['stock'] ?> adet</p>
                    <div>
                        <form method="post" action="favorites.php" style="flex:1;">
                            <input type="hidden" name="product_id" value="<?= $f['id'] ?>">
                            <input type="hidden" name="action" value="remove">
                            <button type="submit">❤ Favorilerden Kaldır</button>
                        </form>
                        <form method="post" action="cart.php?action=add&id=<?= $f['id'] ?>" style="flex:1;">
                            <button type="submit">🛒 Sepete Ekle</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php
    // 6) Footer
    require_once __DIR__ . '/../includes/footer.php';
    ?>
</body>

</html>