<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'includes/config.php';

try {
    echo "<h2>SITE_URL Değeri:</h2>";
    echo SITE_URL;
    
    echo "<h2>Kartvizit Kategorisi:</h2>";
    $stmt = $db->prepare("SELECT * FROM categories WHERE slug = ?");
    $stmt->execute(['kartvizitler']);
    $category = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<pre>";
    print_r($category);
    echo "</pre>";
    
    if ($category) {
        echo "<h2>Kategorideki Ürünler:</h2>";
        $stmt = $db->prepare("SELECT * FROM urunler WHERE category_id = ? AND status = 1");
        $stmt->execute([$category['id']]);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if ($products) {
            foreach ($products as $product) {
                echo "<div style='border: 1px solid #ccc; margin: 10px; padding: 10px;'>";
                echo "<h3>" . htmlspecialchars($product['name']) . "</h3>";
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
            
            // Tüm ürünleri listele
            echo "<h2>Tüm Ürünler:</h2>";
            $stmt = $db->query("SELECT * FROM urunler WHERE status = 1");
            $all_products = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "<pre>";
            print_r($all_products);
            echo "</pre>";
        }
    } else {
        echo "<p>Kartvizitler kategorisi bulunamadı!</p>";
        
        // Tüm kategorileri listele
        echo "<h2>Tüm Kategoriler:</h2>";
        $stmt = $db->query("SELECT * FROM categories WHERE status = 1");
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<pre>";
        print_r($categories);
        echo "</pre>";
    }
    
} catch (PDOException $e) {
    echo "Veritabanı Hatası: " . $e->getMessage();
}
?> 