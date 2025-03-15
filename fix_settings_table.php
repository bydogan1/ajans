<?php
// Veritabanı bağlantısı için config dosyasını dahil et
require_once 'includes/config.php';

try {
    // Önce eklediğimiz sütunları kaldıralım (eğer varsa)
    try {
        $db->exec("ALTER TABLE settings DROP COLUMN site_landline");
        echo "Settings tablosundan site_landline sütunu kaldırıldı.<br>";
    } catch (PDOException $e) {
        echo "site_landline sütunu kaldırılamadı (muhtemelen yok): " . $e->getMessage() . "<br>";
    }
    
    try {
        $db->exec("ALTER TABLE settings DROP COLUMN contact_landline");
        echo "Settings tablosundan contact_landline sütunu kaldırıldı.<br>";
    } catch (PDOException $e) {
        echo "contact_landline sütunu kaldırılamadı (muhtemelen yok): " . $e->getMessage() . "<br>";
    }
    
    // Şimdi site_landline ve contact_landline için satırlar ekleyelim
    // Önce bu satırların var olup olmadığını kontrol edelim
    $stmt = $db->prepare("SELECT COUNT(*) FROM settings WHERE setting_key = ?");
    
    // site_landline kontrolü
    $stmt->execute(['site_landline']);
    $count = $stmt->fetchColumn();
    
    if ($count == 0) {
        // site_landline satırı ekle
        $insert = $db->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)");
        $insert->execute(['site_landline', NULL]);
        echo "Settings tablosuna site_landline satırı eklendi.<br>";
    } else {
        echo "site_landline satırı zaten var.<br>";
    }
    
    // contact_landline kontrolü
    $stmt->execute(['contact_landline']);
    $count = $stmt->fetchColumn();
    
    if ($count == 0) {
        // contact_landline satırı ekle
        $insert = $db->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)");
        $insert->execute(['contact_landline', NULL]);
        echo "Settings tablosuna contact_landline satırı eklendi.<br>";
    } else {
        echo "contact_landline satırı zaten var.<br>";
    }
    
    echo "<br>Veritabanı başarıyla güncellendi!";
} catch (PDOException $e) {
    echo "Hata: " . $e->getMessage();
}
?> 