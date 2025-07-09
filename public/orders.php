<?php
// public/orders.php

// 1) DB bağlantısı
require_once __DIR__ . '/../config/db.php';

// 2) Oturum & yetki kontrolü
require_once __DIR__ . '/../includes/auth.php';
require_login();

// 3) Header router
require_once __DIR__ . '/../includes/header_router.php';
include_header();

// 4) Sorguyu hazırlıyoruz
if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    // Admin: tüm siparişler
    $stmt = $pdo->prepare("SELECT
          o.id           AS order_id,
          o.user_id,
          u.username,
          o.total_price,
          o.created_at,
          p.id           AS product_id,
          p.name         AS product_name,
          oi.quantity,
          oi.price       AS unit_price
        FROM orders o
        JOIN users u        ON u.id          = o.user_id
        JOIN order_items oi ON oi.order_id   = o.id
        JOIN products p     ON p.id          = oi.product_id
        ORDER BY o.created_at DESC, o.id DESC");
    $stmt->execute();
} else {
    // Kullanıcı: yalnızca kendi siparişleri
    $stmt = $pdo->prepare("SELECT
          o.id           AS order_id,
          o.user_id,
          o.total_price,
          o.created_at,
          p.id           AS product_id,
          p.name         AS product_name,
          oi.quantity,
          oi.price       AS unit_price
        FROM orders o
        JOIN order_items oi ON oi.order_id   = o.id
        JOIN products p     ON p.id          = oi.product_id
        WHERE o.user_id = ?
        ORDER BY o.created_at DESC, o.id DESC");
    $stmt->execute([$_SESSION['user_id']]);
}

$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 5) Satırları sipariş bazında grupla
$orders = [];
foreach ($rows as $r) {
    $oid = $r['order_id'];
    if (!isset($orders[$oid])) {
        $orders[$oid] = [
            'user_id' => $r['user_id'] ?? null,
            'username' => $r['username'] ?? null,
            'total_price' => $r['total_price'],
            'created_at' => $r['created_at'],
            'items' => []
        ];
    }
    $orders[$oid]['items'][] = [
        'product_id' => $r['product_id'],
        'name' => $r['product_name'],
        'quantity' => $r['quantity'],
        'unit_price' => $r['unit_price'],
    ];
}
?>

<h1><?= ($_SESSION['role'] === 'admin') ? 'Gelen Siparişler' : 'Siparişlerim' ?></h1>

<?php if (empty($orders)): ?>
    <p><?= ($_SESSION['role'] === 'admin')
        ? 'Henüz hiç sipariş alınmamış.'
        : 'Henüz bir siparişiniz yok.' ?></p>
<?php else: ?>
    <?php foreach ($orders as $order_id => $order): ?>
        <section style="margin-bottom:2em; border:1px solid #ccc; padding:1em; border-radius:4px;">
            <h2>Sipariş</h2>
            <?php if ($_SESSION['role'] === 'admin'): ?>
                <p><strong>Kullanıcı:</strong> <?= htmlspecialchars($order['username'], ENT_QUOTES) ?></p>
            <?php endif; ?>
            <p>
                <strong>Tarih:</strong> <?= $order['created_at'] ?><br>
                <strong>Toplam:</strong> <?= number_format($order['total_price'], 2) ?> ₺
            </p>

            <table style="width:100%; border-collapse:collapse;" border="1" cellpadding="6">
                <thead>
                    <tr>
                        <th>Ürün</th>
                        <th>Adet</th>
                        <th>Birim Fiyat</th>
                        <th>Ara Toplam</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($order['items'] as $item): ?>
                        <tr>
                            <td><?= htmlspecialchars($item['name'], ENT_QUOTES) ?></td>
                            <td style="text-align:center;"><?= (int) $item['quantity'] ?></td>
                            <td style="text-align:right;"><?= number_format($item['unit_price'], 2) ?> ₺</td>
                            <td style="text-align:right;"><?= number_format($item['unit_price'] * $item['quantity'], 2) ?> ₺
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <?php if ($_SESSION['role'] !== 'admin'): ?>
                <p style="margin-top:10px;">
                    <a href="delete_orders.php?order_id=<?= $order_id ?>"
                        onclick="return confirm('Siparişi iptal etmek istediğinize emin misiniz?');">
                        🛑 Siparişi İptal Et
                    </a>
                </p>
            <?php endif; ?>
        </section>
    <?php endforeach; ?>
<?php endif; ?>

<?php
// Footer
require_once __DIR__ . '/../includes/footer.php';
?>