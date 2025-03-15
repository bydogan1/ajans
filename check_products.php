<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'includes/config.php';

try {
    // Kartvizit kategorisindeki ürünleri getir
    $stmt = $db->prepare("
        SELECT u.*, c.name as category_name 
        FROM urunler u 
        LEFT JOIN categories c ON u.category_id = c.id 
        WHERE c.slug = ? AND u.status = 1
    ");
    $stmt->execute(['kartvizitler']);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<h2>Kartvizit Kategorisindeki Ürünler:</h2>";
    echo "<pre>";
    print_r($products);
    echo "</pre>";

    // Her ürün için resim kontrolü yap
    foreach ($products as $product) {
        echo "<hr>";
        echo "<h3>" . htmlspecialchars($product['name']) . "</h3>";
        
        if ($product['image']) {
            $image_path = "uploads/urunler/" . $product['image'];
            echo "Resim Adı: " . $product['image'] . "<br>";
            echo "Resim Yolu: " . $image_path . "<br>";
            echo "Dosya Var Mı: " . (file_exists($image_path) ? 'Evet' : 'Hayır') . "<br>";
            
            if (file_exists($image_path)) {
                echo "Dosya Boyutu: " . filesize($image_path) . " bytes<br>";
                echo "Dosya İzinleri: " . substr(sprintf('%o', fileperms($image_path)), -4) . "<br>";
                echo "<img src='" . SITE_URL . "/" . $image_path . "' style='max-width: 300px;'><br>";
            }
        } else {
            echo "Bu ürün için resim tanımlanmamış.<br>";
        }
    }

} catch (PDOException $e) {
    echo "Veritabanı Hatası: " . $e->getMessage();
}
?> 