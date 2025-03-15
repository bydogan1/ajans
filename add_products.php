<?php
require_once 'includes/config.php';

try {
    // Kartvizitler kategorisinin ID'sini al
    $stmt = $db->prepare("SELECT id FROM categories WHERE slug = ?");
    $stmt->execute(['kartvizitler']);
    $category = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$category) {
        die("Kartvizitler kategorisi bulunamadı!");
    }
    
    $category_id = $category['id'];
    
    // Ürünleri ekle
    $products = [
        [
            'name' => 'Standart Kartvizit',
            'description' => 'Klasik tasarımlı, 350gr kuşe kağıt üzerine baskılı kartvizitler.',
            'image' => 'standart-kartvizit.jpg'
        ],
        [
            'name' => 'Özel Tasarım Kartvizit',
            'description' => 'Kişiye özel tasarlanmış, yuvarlak köşeli, lamine kartvizitler.',
            'image' => 'ozel-kartvizit.jpg'
        ],
        [
            'name' => 'Lüks Kartvizit',
            'description' => 'Yaldız baskılı, kabartmalı, özel kağıt üzerine baskılı lüks kartvizitler.',
            'image' => 'luks-kartvizit.jpg'
        ]
    ];
    
    $stmt = $db->prepare("INSERT INTO urunler (name, description, category_id, status, image) VALUES (?, ?, ?, 1, ?)");
    
    foreach ($products as $product) {
        $stmt->execute([
            $product['name'],
            $product['description'],
            $category_id,
            $product['image']
        ]);
    }
    
    echo "Ürünler başarıyla eklendi!";
    
} catch (PDOException $e) {
    echo "Hata: " . $e->getMessage();
}
?> 