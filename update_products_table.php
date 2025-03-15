<?php
require_once 'includes/config.php';
require_once 'admin/includes/functions.php';

try {
    // Örnek menü-ürün eşleştirmeleri
    $menu_products = [
        // Matbaa Ürünleri
        'matbaa-urunleri' => [
            'kartvizitler' => ['Özel Tasarım Kartvizit', 'Lüks Kartvizit'],
            'brosurler' => ['A4 Broşür', 'Katlamalı Broşür'],
            'el-ilanlari' => ['El İlanı A5', 'El İlanı A6'],
            'kataloglar' => ['Ürün Kataloğu', 'Tanıtım Kataloğu'],
            'takvimler' => ['Masa Takvimi', 'Duvar Takvimi'],
            'dosyalar' => ['Sunum Dosyası', 'Cepli Dosya']
        ],
        
        // Promosyon Ürünleri
        'promosyon-urunleri' => [
            'kalemler' => ['Metal Kalem', 'Plastik Kalem'],
            'anahtarliklar' => ['Metal Anahtarlık', 'Plastik Anahtarlık'],
            'ajandalar' => ['2024 Ajanda', 'Ciltli Ajanda'],
            'cantalar' => ['Bez Çanta', 'Sırt Çantası'],
            'kupalar' => ['Seramik Kupa', 'Sihirli Kupa'],
            't-shirtler' => ['Pamuklu T-Shirt', 'Polo Yaka T-Shirt']
        ]
    ];
    
    // Her bir ana menü için
    foreach ($menu_products as $main_menu => $sub_menus) {
        // Her bir alt menü için
        foreach ($sub_menus as $sub_menu => $products) {
            // Her bir ürün için
            foreach ($products as $product_name) {
                // Ürünü kontrol et
                $stmt = $db->prepare("SELECT id FROM urunler WHERE name = ?");
                $stmt->execute([$product_name]);
                $product = $stmt->fetch();
                
                if ($product) {
                    // Mevcut ürünü güncelle
                    $stmt = $db->prepare("UPDATE urunler SET main_menu = ?, sub_menu = ? WHERE id = ?");
                    $stmt->execute([$main_menu, $sub_menu, $product['id']]);
                } else {
                    // Yeni ürün ekle
                    $stmt = $db->prepare("
                        INSERT INTO urunler (name, slug, main_menu, sub_menu, status) 
                        VALUES (?, ?, ?, ?, 1)
                    ");
                    $stmt->execute([
                        $product_name,
                        createSlug($product_name),
                        $main_menu,
                        $sub_menu
                    ]);
                }
            }
        }
    }
    
    echo "Ürünler başarıyla menülerle ilişkilendirildi ve eksik ürünler eklendi.";
    
} catch (Exception $e) {
    echo "Hata oluştu: " . $e->getMessage();
}
?> 