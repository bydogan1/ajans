<?php
include 'includes/config.php';

// Tüm benzersiz alt menüleri listele
$stmt = $db->query('SELECT DISTINCT sub_menu FROM urunler');
$submenus = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Veritabanındaki Alt Menüler:\n";
foreach ($submenus as $submenu) {
    echo "- " . $submenu['sub_menu'] . "\n";
}

// Saatler alt menüsündeki ürünleri listele
$stmt = $db->query("SELECT * FROM urunler WHERE sub_menu = 'saatler'");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "\nSaatler Alt Menüsündeki Ürünler:\n";
foreach ($products as $product) {
    echo "ID: " . $product['id'] . " - Adı: " . $product['name'] . "\n";
}
?> 