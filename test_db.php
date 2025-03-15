<?php
require_once 'app/init.php';

echo "<h2>Menü Yapısı:</h2>";
echo "<pre>";

// Ana menüleri getir
$stmt = $db->query("SELECT * FROM menus WHERE parent_id IS NULL ORDER BY order_number ASC");
$menus = $stmt->fetchAll();

foreach ($menus as $menu) {
    echo "Ana Menü: {$menu['name']} (ID: {$menu['id']})\n";
    
    // Alt menüleri getir
    $stmt2 = $db->prepare("SELECT * FROM menus WHERE parent_id = ? ORDER BY order_number ASC");
    $stmt2->execute([$menu['id']]);
    $submenus = $stmt2->fetchAll();
    
    foreach ($submenus as $submenu) {
        echo "  - Alt Menü: {$submenu['name']} (ID: {$submenu['id']})\n";
    }
    echo "\n";
}

echo "</pre>";

try {
    // Tüm kategorileri listele
    $stmt = $db->query("SELECT * FROM categories");
    $categories = $stmt->fetchAll();
    
    echo "<h2>Kategoriler:</h2>";
    echo "<pre>";
    print_r($categories);
    echo "</pre>";
    
    // Tüm ürünleri listele
    $stmt = $db->query("SELECT * FROM urunler");
    $products = $stmt->fetchAll();
    
    echo "<h2>Ürünler:</h2>";
    echo "<pre>";
    print_r($products);
    echo "</pre>";
    
    echo "<h2>Ürünler ve Resim Yolları:</h2>";
    echo "<pre>";

    $stmt = $db->query("SELECT id, name, image FROM urunler");
    while($row = $stmt->fetch()) {
        echo "ID: " . $row['id'] . "\n";
        echo "Ad: " . $row['name'] . "\n";
        echo "Resim: " . $row['image'] . "\n";
        echo "-------------------\n";
    }

    echo "</pre>";
    
} catch (PDOException $e) {
    die("Veritabanı hatası: " . $e->getMessage());
} 