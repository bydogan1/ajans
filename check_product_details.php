<?php
require_once 'app/init.php';

try {
    // Kartvizit ürününü getir
    $stmt = $db->prepare("SELECT * FROM urunler WHERE slug = 'kartvizit'");
    $stmt->execute();
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($product) {
        echo "<h2>Kartvizit Ürün Detayları:</h2>";
        echo "<pre>";
        print_r($product);
        echo "</pre>";

        // Resim dosyasını kontrol et
        $image_path = __DIR__ . '/uploads/urunler/' . $product['image'];
        echo "\nResim Dosyası Kontrolü:\n";
        echo "Dosya yolu: " . $image_path . "\n";
        echo "Dosya var mı? " . (file_exists($image_path) ? "Evet" : "Hayır") . "\n";
        if (file_exists($image_path)) {
            echo "Dosya boyutu: " . filesize($image_path) . " bytes\n";
        }
    } else {
        echo "Kartvizit ürünü bulunamadı.";
    }

} catch (Exception $e) {
    echo "Hata: " . $e->getMessage();
}
?> 