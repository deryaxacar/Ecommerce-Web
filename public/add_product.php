<?php
session_start();
require '../config/db.php';
include '../includes/auth.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1) uploads klasörü yoksa oluştur
    $uploadDir = __DIR__ . '/uploads';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // 2) Görsel yükleme işlemi
    $imagePath = null;
    if (
        isset($_FILES['image']) &&
        $_FILES['image']['error'] === UPLOAD_ERR_OK &&
        is_uploaded_file($_FILES['image']['tmp_name'])
    ) {
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $extLower = strtolower($ext);

        if (in_array($extLower, $allowed, true)) {
            $newName = uniqid('img_') . '.' . $extLower;
            $target = $uploadDir . '/' . $newName;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
                // Veritabanına kaydedeceğimiz yol (public dizini altından)
                $imagePath = 'uploads/' . $newName;
            }
        }
    }

    // 3) Ürünü veritabanına kaydet
    $stmt = $pdo->prepare("
        INSERT INTO products (name, description, price, stock, image)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $_POST['name'],
        $_POST['description'],
        $_POST['price'],
        $_POST['stock'],
        $imagePath
    ]);

    header("Location: products.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <title>Ürün Ekle</title>
    <style>
        /* --- Genel Reset & Font --- */

        body {
            font-family: Arial, sans-serif;
            background-color: #f4f6f8;
            color: #333;
        }

        a {
            color: #007bff;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        /* --- Ana Konteyner --- */
        .container {
            max-width: 300px;
            margin: 20px auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        /* --- Form Grupları --- */
        .form-group {
            margin-bottom: 10px;
        }

        .form-group label {
            display: block;
            margin-bottom: 6px;
            font-weight: 500;
        }

        .form-group input[type="text"],
        .form-group input[type="number"],
        .form-group input[type="file"],
        .form-group textarea {
            width: 92%;
            padding: 8px 10px;
            border: 1px solid #ccd0d5;
            border-radius: 4px;
            background: #fafbfc;
        }

        .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }

        /* --- Görsel Önizleme & Buton --- */
        #preview-container {
            text-align: center;
            /* zaten var, img'i ortalamak için yeterli */
            display: none;
        }

        #image-preview {
            display: none;
            /* eklenecek satırlar: */
            display: block;
            /* görünürken block yap */
            margin: 0 auto 15px;
            /* yatayda ortala, alt boşluk ver */
            max-width: 100%;
            max-height: 200px;
            border-radius: 4px;
            border: 1px solid #ccc;
        }

        #image-preview:hover {
            cursor: pointer;
            opacity: 0.8;
        }

        .btn-change-image {
            display: none;
            margin: 3px auto 10px;
            padding: 8px 12px;
            font-weight: 500;
            color: #fff;
            background: #000;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            transition: background .2s ease;
        }

        .btn-change-image:hover {
            background: #5a6268;
        }

        /* --- Gönder Butonu --- */
        .btn-submit {
            width: 100%;
            padding: 12px;
            font-size: 1rem;
            font-weight: 600;
            color: #fff;
            background: #000;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background .2s ease;
        }

        .btn-submit:hover {
            background: #0056b3;
        }


        button {
            align-self: stretch;
            width: 100%;
            align-items: center;
            background: #333;
            color: #fff;
            border: none;
            margin-top: 3px;
            cursor: pointer;
            transition: background 0.2s;
            height: 40px;
            width: 100% !important;
        }

        button:hover {
            background: #5a6268;
        }
    </style>
</head>

<body>
    <?php include '../includes/header_admin.php'; ?>
    <h1>Ürün Ekle</h1>
    <div class="container">
        <!-- Önizleme alanı -->
        <div id="preview-container">
            <img id="image-preview" src="#" alt="Görsel Önizleme">
            <button type="button" id="change-image-btn" class="btn-change-image">Görseli Değiştir</button>
        </div>

        <form method="post" enctype="multipart/form-data">
            <!-- Görsel seçme grubu -->
            <div class="form-group" id="image-input-group">
                <input id="image" name="image" type="file" accept=".jpg,.jpeg,.png,.gif">
            </div>

            <div class="form-group">
                <label for="name">Ürün Adı</label>
                <input id="name" name="name" type="text" placeholder="Ürün Adı" required>
            </div>
            <div class="form-group">
                <label for="description">Açıklama</label>
                <textarea id="description" name="description" placeholder="Açıklama"></textarea>
            </div>
            <div class="form-group">
                <label for="price">Fiyat (TL)</label>
                <input id="price" name="price" type="number" step="0.01" placeholder="Fiyat" required>
            </div>
            <div class="form-group">
                <label for="stock">Stok</label>
                <input id="stock" name="stock" type="number" placeholder="Stok" required>
            </div>

            <button type="submit">Ekle</button>
        </form>
    </div>

    <?php include '../includes/footer.php'; ?>

    <script>
        const previewContainer = document.getElementById('preview-container');
        const imageInputGroup = document.getElementById('image-input-group');
        const imageInput = document.getElementById('image');
        const preview = document.getElementById('image-preview');
        const changeBtn = document.getElementById('change-image-btn');

        imageInput.addEventListener('change', e => {
            const file = e.target.files[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = ev => {
                preview.src = ev.target.result;
                // görünürlüğü JS ile kontrol ediyoruz
                previewContainer.style.display = 'block';
                preview.style.display = 'block';
                imageInputGroup.style.display = 'none';
                changeBtn.style.display = 'inline-block';
            };
            reader.readAsDataURL(file);
        });

        changeBtn.addEventListener('click', () => {
            imageInput.value = '';
            // resetle ve tekrar gizle
            previewContainer.style.display = 'none';
            changeBtn.style.display = 'none';
            imageInputGroup.style.display = 'block';
        });
    </script>
</body>

</html>