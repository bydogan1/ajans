<?php
require_once 'includes/config.php';
require_once 'admin/includes/functions.php';

try {
    // Önce mevcut menüleri temizle
    $db->exec("DELETE FROM menus");
    
    // Ana menüleri ekle
    $menuler = [
        ['Anasayfa', 'anasayfa', NULL, 1],
        ['Hakkımızda', 'hakkimizda', NULL, 2],
        ['Matbaa Ürünleri', 'matbaa-urunleri', NULL, 3],
        ['Promosyon Ürünleri', 'promosyon-urunleri', NULL, 4],
        ['Blog', 'blog', NULL, 5]
    ];
    
    $stmt = $db->prepare("INSERT INTO menus (name, slug, parent_id, order_number, status) VALUES (?, ?, ?, ?, 1)");
    
    foreach ($menuler as $menu) {
        $stmt->execute($menu);
    }
    
    echo "Menüler başarıyla düzenlendi.";
} catch (PDOException $e) {
    echo "Hata: " . $e->getMessage();
}
?> 