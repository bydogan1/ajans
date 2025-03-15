<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'includes/config.php';
require_once 'admin/includes/functions.php';

echo "<h2>Debug Bilgileri</h2>";

// URL parametrelerini kontrol et
echo "<h3>URL Parametreleri:</h3>";
echo "<pre>";
print_r($_GET);
echo "</pre>";

// page_id'yi al ve temizle
$page_id = clean($_GET['id'] ?? '');
echo "<h3>Temizlenmiş page_id:</h3>";
echo $page_id;

// Kategoriyi kontrol et
$stmt = $db->prepare("SELECT * FROM categories WHERE slug = ? AND status = 1");
$stmt->execute([$page_id]);
$category = $stmt->fetch(PDO::FETCH_ASSOC);

echo "<h3>Kategori Bilgileri:</h3>";
if ($category) {
    echo "<pre>";
    print_r($category);
    echo "</pre>";

    // Ürünleri kontrol et
    echo "<h3>Kategori Ürünleri:</h3>";
    $stmt = $db->prepare("SELECT * FROM urunler WHERE category_id = ? AND status = 1");
    $stmt->execute([$category['id']]);
    $urunler = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($urunler)) {
        echo "Bu kategoride ürün bulunamadı.";
    } else {
        echo "<pre>";
        print_r($urunler);
        echo "</pre>";
    }
} else {
    echo "Kategori bulunamadı!";
}

// PDO hata bilgilerini kontrol et
echo "<h3>PDO Hata Bilgileri:</h3>";
echo "<pre>";
print_r($db->errorInfo());
echo "</pre>";
?> 