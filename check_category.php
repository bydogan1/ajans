<?php
require_once 'includes/config.php';

try {
    // Hata raporlamayı aktifleştir
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    
    // Kartvizitler kategorisini kontrol et
    $stmt = $db->prepare("SELECT * FROM categories WHERE slug = ?");
    $stmt->execute(['kartvizitler']);
    $category = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<h2>Kategori Bilgileri:</h2>";
    if ($category) {
        echo "<pre>";
        print_r($category);
        echo "</pre>";
        
        // Bu kategorideki ürünleri kontrol et
        $stmt = $db->prepare("SELECT * FROM urunler WHERE category_id = ? AND status = 1");
        $stmt->execute([$category['id']]);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h2>Kategorideki Ürünler:</h2>";
        if ($products) {
            echo "<pre>";
            print_r($products);
            echo "</pre>";
        } else {
            echo "Bu kategoride hiç ürün bulunamadı!";
            
            // Veritabanı yapısını kontrol et
            echo "<h2>Urunler Tablosu Yapısı:</h2>";
            $stmt = $db->query("DESCRIBE urunler");
            $structure = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo "<pre>";
            print_r($structure);
            echo "</pre>";
        }
    } else {
        echo "Kartvizitler kategorisi bulunamadı! Kategori eklememiz gerekiyor.";
        
        // Yeni kategori ekle
        $stmt = $db->prepare("INSERT INTO categories (name, slug, status) VALUES (?, ?, 1)");
        $stmt->execute(['Kartvizitler', 'kartvizitler']);
        echo "<br>Kategori eklendi! ID: " . $db->lastInsertId();
    }
    
} catch (PDOException $e) {
    echo "Veritabanı Hatası: " . $e->getMessage();
}
?> 