<?php
// Veritabanı bağlantısı için config dosyasını dahil et
require_once 'includes/config.php';

try {
    // Settings tablosuna site_landline sütunu ekleme
    $db->exec("ALTER TABLE settings ADD COLUMN site_landline VARCHAR(20) DEFAULT NULL");
    echo "Settings tablosuna site_landline sütunu eklendi.<br>";
    
    // Contacts tablosuna landline sütunu ekleme
    $db->exec("ALTER TABLE contacts ADD COLUMN landline VARCHAR(20) DEFAULT NULL");
    echo "Contacts tablosuna landline sütunu eklendi.<br>";
    
    // Settings tablosuna contact_landline sütunu ekleme
    $db->exec("ALTER TABLE settings ADD COLUMN contact_landline VARCHAR(20) DEFAULT NULL");
    echo "Settings tablosuna contact_landline sütunu eklendi.<br>";
    
    echo "<br>Veritabanı başarıyla güncellendi!";
} catch (PDOException $e) {
    echo "Hata: " . $e->getMessage();
}
?> 