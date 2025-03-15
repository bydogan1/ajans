<?php
require_once 'includes/config.php';
require_once 'admin/includes/functions.php';

try {
    // Test ürünü ekle
    $stmt = $db->prepare("
        INSERT INTO urunler (
            name,
            slug,
            description,
            category_id,
            image,
            status,
            main_menu,
            sub_menu,
            created_at
        ) VALUES (
            :name,
            :slug,
            :description,
            :category_id,
            :image,
            :status,
            :main_menu,
            :sub_menu,
            NOW()
        )
    ");

    $data = [
        'name' => 'Duvar Saati',
        'slug' => 'duvar-saati',
        'description' => 'Özel tasarım, yüksek kaliteli duvar saati.',
        'category_id' => 1, // Kategori ID'si
        'image' => 'duvar-saati.jpg',
        'status' => 1,
        'main_menu' => 'promosyon-urunleri',
        'sub_menu' => 'saatler'
    ];

    $stmt->execute($data);
    echo "Test ürünü başarıyla eklendi!<br>";

} catch (PDOException $e) {
    echo "Hata: " . $e->getMessage();
    // Hata detaylarını göster
    echo "<pre>";
    print_r($stmt->errorInfo());
    echo "</pre>";
}

// Eklenen ürünleri kontrol et
$stmt = $db->prepare("SELECT * FROM urunler WHERE sub_menu = ?");
$stmt->execute(['saatler']);
$urunler = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<h3>Saatler Alt Menüsündeki Ürünler:</h3>";
echo "<pre>";
print_r($urunler);
echo "</pre>";
?> 