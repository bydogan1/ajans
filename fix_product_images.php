<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Tüm ürünleri getir
$stmt = $db->query("SELECT id, name, image FROM urunler WHERE image LIKE '%Array%'");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($products as $product) {
    try {
        // Array olarak kaydedilmiş resmi düzelt
        $image_data = unserialize($product['image']);
        $new_image_name = '';
        
        if (is_array($image_data)) {
            if (isset($image_data[0])) {
                $new_image_name = basename($image_data[0]);
            } elseif (isset($image_data['name'])) {
                $new_image_name = $image_data['name'];
            }
        }
        
        if ($new_image_name) {
            // Veritabanını güncelle
            $stmt = $db->prepare("UPDATE urunler SET image = ? WHERE id = ?");
            $stmt->execute([$new_image_name, $product['id']]);
            
            echo "Ürün düzeltildi - ID: {$product['id']}, İsim: {$product['name']}, Yeni resim: {$new_image_name}\n";
        } else {
            echo "Resim düzeltilemedi - ID: {$product['id']}, İsim: {$product['name']}\n";
        }
    } catch (Exception $e) {
        echo "Hata - ID: {$product['id']}, İsim: {$product['name']}, Hata: {$e->getMessage()}\n";
    }
}
?> 