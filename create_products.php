<?php
require_once 'includes/config.php';
require_once 'admin/includes/functions.php';

try {
    // Ürünler tablosunu oluştur
    $db->exec("CREATE TABLE IF NOT EXISTS urunler (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        slug VARCHAR(255) NOT NULL,
        description TEXT,
        image VARCHAR(255),
        main_menu VARCHAR(255),
        sub_menu VARCHAR(255),
        status TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY unique_slug (slug)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

    // Örnek ürünler ekle
    $ornek_urunler = [
        // Matbaa Ürünleri - Kartvizitler
        [
            'name' => 'Özel Tasarım Kartvizit',
            'slug' => 'ozel-tasarim-kartvizit',
            'description' => '350gr mat kuşe üzerine özel tasarım kartvizit basımı.',
            'main_menu' => 'matbaa-urunleri',
            'sub_menu' => 'kartvizitler'
        ],
        [
            'name' => 'Lüks Kartvizit',
            'slug' => 'luks-kartvizit',
            'description' => 'Yaldız baskılı lüks kartvizit çözümleri.',
            'main_menu' => 'matbaa-urunleri',
            'sub_menu' => 'kartvizitler'
        ],
        
        // Matbaa Ürünleri - Broşürler
        [
            'name' => 'A4 Broşür',
            'slug' => 'a4-brosur',
            'description' => 'Çift taraflı A4 broşür basımı.',
            'main_menu' => 'matbaa-urunleri',
            'sub_menu' => 'brosurler'
        ],
        [
            'name' => 'Katlamalı Broşür',
            'slug' => 'katlamali-brosur',
            'description' => '3 kırımlı katlamalı broşür basımı.',
            'main_menu' => 'matbaa-urunleri',
            'sub_menu' => 'brosurler'
        ],
        
        // Promosyon Ürünleri - Kalemler
        [
            'name' => 'Metal Kalem',
            'slug' => 'metal-kalem',
            'description' => 'Özel logolu metal kalem.',
            'main_menu' => 'promosyon-urunleri',
            'sub_menu' => 'kalemler'
        ],
        [
            'name' => 'Plastik Kalem',
            'slug' => 'plastik-kalem',
            'description' => 'Ekonomik plastik kalem çözümleri.',
            'main_menu' => 'promosyon-urunleri',
            'sub_menu' => 'kalemler'
        ],
        
        // Promosyon Ürünleri - Kupalar
        [
            'name' => 'Seramik Kupa',
            'slug' => 'seramik-kupa',
            'description' => 'Özel baskılı seramik kupa.',
            'main_menu' => 'promosyon-urunleri',
            'sub_menu' => 'kupalar'
        ],
        [
            'name' => 'Sihirli Kupa',
            'slug' => 'sihirli-kupa',
            'description' => 'Isıya duyarlı özel baskılı sihirli kupa.',
            'main_menu' => 'promosyon-urunleri',
            'sub_menu' => 'kupalar'
        ]
    ];

    // Örnek ürünleri ekle
    $stmt = $db->prepare("INSERT INTO urunler (name, slug, description, main_menu, sub_menu) VALUES (?, ?, ?, ?, ?)");
    
    foreach ($ornek_urunler as $urun) {
        $stmt->execute([
            $urun['name'],
            $urun['slug'],
            $urun['description'],
            $urun['main_menu'],
            $urun['sub_menu']
        ]);
    }

    echo "Ürünler tablosu oluşturuldu ve örnek ürünler eklendi.";
} catch (PDOException $e) {
    echo "Hata: " . $e->getMessage();
}
?> 