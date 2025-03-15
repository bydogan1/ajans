<?php
require_once 'app/init.php';

try {
    // Uploads dizinini kontrol et
    $uploads_dir = __DIR__ . '/uploads/urunler';
    if (!file_exists($uploads_dir)) {
        mkdir($uploads_dir, 0777, true);
    }

    // Örnek resim dosyası oluştur
    $new_filename = 'kartvizit-' . uniqid() . '.jpg';
    $destination = $uploads_dir . '/' . $new_filename;
    
    // Örnek bir resim içeriği oluştur (1x1 mavi piksel)
    $image_data = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');
    file_put_contents($destination, $image_data);

    // Veritabanını güncelle
    $stmt = $db->prepare("UPDATE urunler SET image = ? WHERE slug = 'kartvizit'");
    $stmt->execute([$new_filename]);
    
    echo "Kartvizit resmi başarıyla güncellendi.<br>";
    echo "Yüklenen resim: " . $new_filename;

} catch (Exception $e) {
    echo "Hata: " . $e->getMessage();
}
?> 