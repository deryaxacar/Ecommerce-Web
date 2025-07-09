<?php
require '../config/db.php';

$stmt = $pdo->query("SELECT f.user_id, f.product_id, u.email, p.name 
    FROM favorites f 
    JOIN users u ON f.user_id = u.id 
    JOIN products p ON f.product_id = p.id 
    WHERE p.stock > 0");

$notifications = $stmt->fetchAll();

foreach ($notifications as $note) {
    echo "Bildirim: {$note['email']} adresine '{$note['name']}' ürünü stokta diye bildirim gönderildi.<br>";
}
