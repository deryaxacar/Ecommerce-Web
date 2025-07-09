<?php
require '../config/db.php';

$sql = "
    SELECT f.user_id, f.product_id, p.name, u.email
    FROM favorites f
    JOIN products p ON f.product_id = p.id
    JOIN users u ON f.user_id = u.id
    WHERE p.stock > 0 AND f.notified = 0
";
$stmt = $pdo->query($sql);
$updates = $stmt->fetchAll();

foreach ($updates as $item) {
    $to = $item['email'];
    $subject = "Favori Ürün Stokta: " . $item['name'];
    $message = "Takip ettiğiniz '" . $item['name'] . "' ürünü tekrar stokta!";
    $headers = "From: info@siteniz.com";

    mail($to, $subject, $message, $headers);

    $updateStmt = $pdo->prepare("UPDATE favorites SET notified = 1 WHERE user_id = ? AND product_id = ?");
    $updateStmt->execute([$item['user_id'], $item['product_id']]);
}
?>