<?php
require_once 'includes/config.php';
require_once 'admin/includes/functions.php';

try {
    // Slug kolonu ekle
    $db->exec("ALTER TABLE urunler ADD COLUMN slug VARCHAR(255) AFTER name");
    
    // Mevcut ürünler için slug oluştur
    $stmt = $db->query("SELECT id, name FROM urunler");
    $products = $stmt->fetchAll();
    
    foreach ($products as $product) {
        $slug = createSlug($product['name']);
        $stmt = $db->prepare("UPDATE urunler SET slug = ? WHERE id = ?");
        $stmt->execute([$slug, $product['id']]);
    }
    
    echo "Slug kolonu eklendi ve mevcut ürünler için slug değerleri oluşturuldu.";
    
} catch (Exception $e) {
    echo "Hata oluştu: " . $e->getMessage();
}
?> 