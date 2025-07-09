<?php
// public/cart.php

// 1) Veritabanı bağlantısını yap
require_once __DIR__ . '/../config/db.php';

// 2) Oturum ve yetki kontrolü
require_once __DIR__ . '/../includes/auth.php';
require_login();  // giriş yoksa login.php'ye yönlendirir

// 3) Header router ile uygun header'ı dahil et
require_once __DIR__ . '/../includes/header_router.php';
include_header();

// 4) Kullanıcı bilgisi ve parametreler
$user_id = $_SESSION['user_id'];
$action = $_GET['action'] ?? null;
$product_id = $_GET['id'] ?? null;
$newQty = $_POST['quantity'] ?? null;
$editingId = null;

// 5) Sepete ekle
if ($action === 'add' && $product_id) {
    $qty = (isset($newQty) && is_numeric($newQty))
        ? max(1, (int) $newQty)
        : 1;

    $stmt = $pdo->prepare("SELECT * FROM cart WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$user_id, $product_id]);

    if ($stmt->rowCount() > 0) {
        $pdo->prepare("UPDATE cart SET quantity = quantity + ? WHERE user_id = ? AND product_id = ?")
            ->execute([$qty, $user_id, $product_id]);
    } else {
        $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)")
            ->execute([$user_id, $product_id, $qty]);
    }

    header("Location: cart.php");
    exit;
}

// 6) Düzenleme formunu göstermek (GET)
if ($action === 'edit' && $product_id && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $editingId = (int) $product_id;
}

// 7) Düzenleme işlemi (POST)
if ($action === 'edit' && $product_id && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $qty = (isset($newQty) && is_numeric($newQty))
        ? max(1, (int) $newQty)
        : 1;

    $pdo->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?")
        ->execute([$qty, $user_id, $product_id]);

    header("Location: cart.php");
    exit;
}

// 8) Sepetten sil
if ($action === 'remove' && $product_id) {
    $pdo->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?")
        ->execute([$user_id, $product_id]);

    header("Location: cart.php");
    exit;
}

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

<main>
    <h1>Sepetiniz</h1>

    <?php if (empty($items)): ?>
        <p>Sepetinizde henüz ürün yok.</p>
    <?php else: ?>
        <ul style="list-style:none; padding:0;">
            <?php foreach ($items as $item): ?>
                <li style="margin-bottom:15px;">
                    <?php if (!empty($item['image'])): ?>
                        <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>"
                            style="width:50px; vertical-align:middle; margin-right:10px;">
                    <?php endif; ?>

                    <?php if ($item['id'] === $editingId): ?>
                        <form method="post" action="cart.php?action=edit&id=<?= $item['id'] ?>" style="display:inline;">
                            <label>
                                Adeti düzenleyin:
                                <input type="number" name="quantity" value="<?= $item['quantity'] ?>" min="1" style="width:60px;">
                            </label>
                            <button type="submit">Kaydet</button>
                            <a href="cart.php" style="margin-left:8px;">İptal</a>
                        </form>
                    <?php else: ?>
                        <strong><?= htmlspecialchars($item['name']) ?></strong>
                        &mdash; <?= (int) $item['quantity'] ?> adet
                        &mdash; <?= number_format($item['price'] * $item['quantity'], 2) ?> ₺
                        [<a href="cart.php?action=edit&id=<?= $item['id'] ?>">Düzenle</a>]
                        [<a href="cart.php?action=remove&id=<?= $item['id'] ?>" style="color:red;"
                            onclick="return confirm('Bu ürünü sepetten çıkarmak istediğinize emin misiniz?');">Sil</a>]
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>

        <?php if ($_SESSION['role'] !== 'admin'): ?>
            <p style="margin-top:20px;">
                <a href="checkout.php"
                    style="padding:10px 20px; background:#28a745; color:#fff; text-decoration:none; border-radius:4px;">
                    Satın Al
                </a>
            </p>
        <?php endif; ?>
    <?php endif; ?>
</main>

<?php
// 10) Footer
require_once __DIR__ . '/../includes/footer.php';
?>