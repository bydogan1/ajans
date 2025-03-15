<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'includes/config.php';

try {
    // 1. Kartvizitler kategorisini kontrol et
    echo "<h2>Kartvizitler Kategorisi:</h2>";
    $stmt = $db->prepare("SELECT * FROM categories WHERE slug = ?");
    $stmt->execute(['kartvizitler']);
    $category = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($category) {
        echo "<pre>";
        print_r($category);
        echo "</pre>";
        
        // 2. Bu kategorideki ürünleri kontrol et
        echo "<h2>Kategorideki Ürünler:</h2>";
        $stmt = $db->prepare("SELECT * FROM urunler WHERE category_id = ?");
        $stmt->execute([$category['id']]);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if ($products) {
            foreach ($products as $product) {
                echo "<div style='border: 1px solid #ccc; margin: 10px; padding: 10px;'>";
                echo "<h3>" . htmlspecialchars($product['name']) . "</h3>";
                echo "<p>ID: " . $product['id'] . "</p>";
                echo "<p>Kategori ID: " . $product['category_id'] . "</p>";
                echo "<p>Status: " . $product['status'] . "</p>";
                if ($product['image']) {
                    $image_path = SITE_URL . "/uploads/urunler/" . $product['image'];
                    echo "<p>Resim Yolu: " . $image_path . "</p>";
                    echo "<img src='" . $image_path . "' style='max-width: 300px;'><br>";
                }
                echo "<p>" . nl2br(htmlspecialchars($product['description'])) . "</p>";
                echo "</div>";
            }
        } else {
            echo "<p>Bu kategoride ürün bulunamadı!</p>";
            
            // 3. Veritabanı yapısını kontrol et
            echo "<h2>Urunler Tablosu Yapısı:</h2>";
            $stmt = $db->query("DESCRIBE urunler");
            $structure = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo "<pre>";
            print_r($structure);
            echo "</pre>";
            
            // 4. Tüm ürünleri listele
            echo "<h2>Tüm Ürünler:</h2>";
            $stmt = $db->query("SELECT * FROM urunler");
            $all_products = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo "<pre>";
            print_r($all_products);
            echo "</pre>";
        }
    } else {
        echo "Kartvizitler kategorisi bulunamadı!";
        
        // 5. Tüm kategorileri listele
        echo "<h2>Tüm Kategoriler:</h2>";
        $stmt = $db->query("SELECT * FROM categories");
        $all_categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "<pre>";
        print_r($all_categories);
        echo "</pre>";
    }
    
} catch (PDOException $e) {
    echo "Veritabanı Hatası: " . $e->getMessage();
}
?> 