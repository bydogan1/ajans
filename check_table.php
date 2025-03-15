<?php
require_once 'includes/config.php';
require_once 'admin/includes/functions.php';

try {
    // Tablo yapısını kontrol et
    $stmt = $db->query("DESCRIBE urunler");
    echo "<h3>Urunler Tablosu Yapısı:</h3>";
    echo "<pre>";
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
    echo "</pre>";

    // Kategorileri kontrol et
    $stmt = $db->query("SELECT * FROM categories WHERE id = 3");
    echo "<h3>Kategori Bilgisi:</h3>";
    echo "<pre>";
    print_r($stmt->fetch(PDO::FETCH_ASSOC));
    echo "</pre>";

} catch (PDOException $e) {
    echo "Hata: " . $e->getMessage();
}
?> 