<?php
include 'includes/config.php';

// Tüm ana menüleri listele
echo "ANA MENÜLER:\n";
$stmt = $db->query("SELECT * FROM menus WHERE parent_id = 0 AND status = 1");
$main_menus = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($main_menus as $menu) {
    echo "ID: {$menu['id']} - Adı: {$menu['name']} - Slug: {$menu['slug']}\n";
    
    // Bu ana menüye ait alt menüleri listele
    $stmt = $db->prepare("SELECT * FROM menus WHERE parent_id = ? AND status = 1");
    $stmt->execute([$menu['id']]);
    $sub_menus = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($sub_menus) {
        echo "  ALT MENÜLER:\n";
        foreach ($sub_menus as $sub) {
            echo "  - ID: {$sub['id']} - Adı: {$sub['name']} - Slug: {$sub['slug']}\n";
            
            // Bu alt menüye ait ürünleri say
            $stmt = $db->prepare("SELECT COUNT(*) FROM urunler WHERE sub_menu = ? AND status = 1");
            $stmt->execute([$sub['slug']]);
            $count = $stmt->fetchColumn();
            
            echo "    Ürün Sayısı: {$count}\n";
        }
    } else {
        echo "  Alt menü yok\n";
    }
    
    // Bu ana menüye ait ürünleri say (alt menü olmadan)
    $stmt = $db->prepare("SELECT COUNT(*) FROM urunler WHERE main_menu = ? AND (sub_menu IS NULL OR sub_menu = '') AND status = 1");
    $stmt->execute([$menu['slug']]);
    $count = $stmt->fetchColumn();
    
    echo "  Ana Menüye Doğrudan Bağlı Ürün Sayısı: {$count}\n\n";
}

// Saatler alt menüsünü özel olarak kontrol et
echo "\nSAATLER ALT MENÜSÜ KONTROLÜ:\n";
$stmt = $db->prepare("SELECT * FROM menus WHERE slug = ? AND status = 1");
$stmt->execute(['saatler']);
$saatler_menu = $stmt->fetch(PDO::FETCH_ASSOC);

if ($saatler_menu) {
    echo "Saatler menüsü bulundu:\n";
    echo "ID: {$saatler_menu['id']} - Adı: {$saatler_menu['name']} - Slug: {$saatler_menu['slug']}\n";
    echo "Parent ID: {$saatler_menu['parent_id']}\n";
    
    // Eğer bir alt menü ise, ana menüsünü bul
    if ($saatler_menu['parent_id'] > 0) {
        $stmt = $db->prepare("SELECT * FROM menus WHERE id = ?");
        $stmt->execute([$saatler_menu['parent_id']]);
        $parent = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($parent) {
            echo "Ana Menü: {$parent['name']} (Slug: {$parent['slug']})\n";
        }
    }
    
    // Saatler menüsüne ait ürünleri listele
    $stmt = $db->prepare("SELECT * FROM urunler WHERE sub_menu = ? AND status = 1");
    $stmt->execute(['saatler']);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "\nSaatler menüsündeki ürünler ({$stmt->rowCount()}):\n";
    foreach ($products as $product) {
        echo "- ID: {$product['id']} - Adı: {$product['name']} - Ana Menü: {$product['main_menu']} - Alt Menü: {$product['sub_menu']}\n";
    }
} else {
    echo "Saatler adında bir menü bulunamadı!\n";
}

// URL yapısını göster
echo "\nÖRNEK URL YAPISI:\n";
echo "Ana menü için: " . SITE_URL . "/menu-page.php?main_menu=[ANA_MENU_SLUG]\n";
echo "Alt menü için: " . SITE_URL . "/menu-page.php?main_menu=[ANA_MENU_SLUG]&sub_menu=[ALT_MENU_SLUG]\n";
echo "Saatler için: " . SITE_URL . "/menu-page.php?main_menu=[ANA_MENU_SLUG]&sub_menu=saatler\n";
?> 