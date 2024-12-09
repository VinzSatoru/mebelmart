<?php
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../config/admin.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = new Database();
    
    // Validasi input
    if (empty($_POST['name']) || empty($_POST['description']) || 
        empty($_POST['category_id']) || empty($_POST['price']) || 
        empty($_POST['stock']) || empty($_FILES['image'])) {
        $_SESSION['error'] = 'Semua field harus diisi!';
        header('Location: create.php');
        exit;
    }

    // Validasi file gambar
    $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
    $image = $_FILES['image'];
    
    if (!in_array($image['type'], $allowed_types)) {
        $_SESSION['error'] = 'Tipe file tidak didukung! Gunakan JPG, JPEG, atau PNG.';
        header('Location: create.php');
        exit;
    }

    // Generate nama file unik
    $extension = pathinfo($image['name'], PATHINFO_EXTENSION);
    $imageName = time() . '_' . uniqid() . '.' . $extension;
    $uploadPath = '../../assets/images/products/';
    
    // Buat direktori jika belum ada
    if (!file_exists($uploadPath)) {
        mkdir($uploadPath, 0777, true);
    }

    $imagePath = $uploadPath . $imageName;

    // Upload gambar
    if (!move_uploaded_file($image['tmp_name'], $imagePath)) {
        $_SESSION['error'] = 'Gagal mengupload gambar!';
        header('Location: create.php');
        exit;
    }

    try {
        // Simpan ke database
        $product = [
            'name' => $_POST['name'],
            'description' => $_POST['description'],
            'category_id' => new MongoDB\BSON\ObjectId($_POST['category_id']),
            'price' => new MongoDB\BSON\Decimal128($_POST['price']),
            'stock' => (int)$_POST['stock'],
            'image' => $imageName,
            'created_at' => new MongoDB\BSON\UTCDateTime(),
            'updated_at' => new MongoDB\BSON\UTCDateTime()
        ];

        // Dapatkan nama kategori
        $category = $db->getCollection('categories')->findOne([
            '_id' => new MongoDB\BSON\ObjectId($_POST['category_id'])
        ]);
        
        if ($category) {
            $product['category_name'] = $category->name;
        }

        $result = $db->getCollection('products')->insertOne($product);

        if ($result->getInsertedCount() > 0) {
            $_SESSION['success'] = 'Produk berhasil ditambahkan!';
            header('Location: index.php');
            exit;
        } else {
            // Jika gagal, hapus gambar yang sudah diupload
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
            throw new Exception('Gagal menyimpan ke database');
        }

    } catch (Exception $e) {
        // Hapus gambar jika ada error
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
        $_SESSION['error'] = 'Gagal menambahkan produk: ' . $e->getMessage();
        header('Location: create.php');
        exit;
    }
} else {
    // Jika bukan POST request, redirect ke form
    header('Location: create.php');
    exit;
} 