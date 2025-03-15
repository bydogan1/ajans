<?php
require_once 'includes/config.php';
require_once 'admin/includes/functions.php';

try {
    // Önce ana menülerin ID'lerini alalım
    $stmt = $db->prepare("SELECT id FROM menus WHERE slug = ?");
    
    // Matbaa Ürünleri ID'sini al
    $stmt->execute(['matbaa-urunleri']);
    $matbaa_id = $stmt->fetchColumn();
    
    // Promosyon Ürünleri ID'sini al
    $stmt->execute(['promosyon-urunleri']);
    $promosyon_id = $stmt->fetchColumn();
    
    // Matbaa alt menüleri
    $matbaa_alt_menuler = [
        ['Kartvizitler', 'kartvizitler', $matbaa_id, 1],
        ['Broşürler', 'brosurler', $matbaa_id, 2],
        ['El İlanları', 'el-ilanlari', $matbaa_id, 3],
        ['Kataloglar', 'kataloglar', $matbaa_id, 4],
        ['Takvimler', 'takvimler', $matbaa_id, 5],
        ['Dosyalar', 'dosyalar', $matbaa_id, 6]
    ];
    
    // Promosyon alt menüleri
    $promosyon_alt_menuler = [
        ['Kalemler', 'kalemler', $promosyon_id, 1],
        ['Anahtarlıklar', 'anahtarliklar', $promosyon_id, 2],
        ['Ajandalar', 'ajandalar', $promosyon_id, 3],
        ['Çantalar', 'cantalar', $promosyon_id, 4],
        ['Kupalar', 'kupalar', $promosyon_id, 5],
        ['T-Shirtler', 't-shirtler', $promosyon_id, 6]
    ];
    
    // Alt menüleri ekle
    $stmt = $db->prepare("INSERT INTO menus (name, slug, parent_id, order_number, status) VALUES (?, ?, ?, ?, 1)");
    
    // Matbaa alt menülerini ekle
    foreach ($matbaa_alt_menuler as $menu) {
        $stmt->execute($menu);
    }
    
    // Promosyon alt menülerini ekle
    foreach ($promosyon_alt_menuler as $menu) {
        $stmt->execute($menu);
    }
    
    echo "Alt menüler başarıyla eklendi.";
} catch (PDOException $e) {
    echo "Hata: " . $e->getMessage();
}
?> 