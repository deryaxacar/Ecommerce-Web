<?php
session_start();
require '../config/db.php';
include '../includes/auth.php';

$id = $_GET['id'];
// 1) Mevcut ürünü, image yolunu da çek
$stmt = $pdo->prepare("SELECT stock, name, description, price, image FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newStock = (int) $_POST['stock'];

    // 2) Eğer yeni bir görsel yüklendiyse
    $imagePath = $product['image'];
    if (!empty($_FILES['image']['name'])) {
        // Eski görsel dosyasını sil (varsa)
        if ($product['image'] && file_exists(__DIR__ . '/uploads/' . basename($product['image']))) {
            unlink(__DIR__ . '/uploads/' . basename($product['image']));
        }
        // Yeni dosyayı kaydet
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array(strtolower($ext), $allowed)) {
            $newName = uniqid('img_') . '.' . $ext;
            $target = __DIR__ . '/uploads/' . $newName;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
                $imagePath = 'uploads/' . $newName;
            }
        }
    }

    // 3) Ürünü güncelle (image alanını da dahil ettik)
    $stmt = $pdo->prepare("
        UPDATE products
        SET name = ?, description = ?, price = ?, stock = ?, image = ?
        WHERE id = ?
    ");
    $stmt->execute([
        $_POST['name'],
        $_POST['description'],
        $_POST['price'],
        $newStock,
        $imagePath,
        $id
    ]);

    // 4) Stok 0→>0 geçişi bildirimleri
    if ($product['stock'] === 0 && $newStock > 0) {
        $stmtFav = $pdo->prepare("SELECT user_id FROM favorites WHERE product_id = ?");
        $stmtFav->execute([$id]);
        $userIds = $stmtFav->fetchAll(PDO::FETCH_COLUMN);

        $message = sprintf('“%s” ürününe tekrar stok eklendi.', $product['name']);
        $stmtNot = $pdo->prepare("
            INSERT INTO notifications (user_id, product_id, message)
            VALUES (?, ?, ?)
        ");
        foreach ($userIds as $uid) {
            $stmtNot->execute([$uid, $id, $message]);
        }
    }

    header("Location: products.php");
    exit;
}
?>

<?php
require_once __DIR__ . '/../includes/header_router.php';
include_header();
?>

<main style="margin:30px; margin-bottom: 70px;">
    <h1 style="margin-bottom: 5px;">Ürün Düzenle</h1>
    <form method="post" enctype="multipart/form-data">
        <div>
            <label style="font-size: 12px;">Ürün Adı</label>
            <input name="name" value="<?= htmlspecialchars($product['name']) ?>" required>
        </div>
        <div>
            <label style="font-size: 12px;">Açıklama</label><br>
            <textarea name="description"><?= htmlspecialchars($product['description']) ?></textarea>
        </div>
        <div>
            <label style="font-size: 12px;">Fiyat (TL)</label><br>
            <input name="price" type="number" step="0.01" value="<?= $product['price'] ?>" required>
        </div>
        <div>
            <label style="font-size: 12px;">Stok</label><br>
            <input name="stock" type="number" value="<?= $product['stock'] ?>" required>
        </div>
        <div>
            <?php if ($product['image']): ?>
                <img src="uploads/<?= basename($product['image']) ?>" width="100" alt=""><br>
            <?php else: ?>
                <small>Henüz görsel yok.</small><br>
            <?php endif; ?>
        </div>
        <div style=" justify-content: space-between; ">
            <label>Yeni Görsel Yükle</label><br>
            <input type="file" name="image" accept="image/*">
        </div>
        <div style="margin-top:10px;">
            <a href="products.php" style="margin-right:8px;">İptal</a>
            <button type="submit">Güncelle</button>
        </div>
    </form>
</main>

<?php include '../includes/footer.php'; ?>