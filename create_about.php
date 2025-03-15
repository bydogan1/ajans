<?php
require_once 'includes/config.php';
require_once 'admin/includes/functions.php';

try {
    // About tablosunu oluştur
    $db->exec("CREATE TABLE IF NOT EXISTS about (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255),
        content TEXT,
        mission TEXT,
        vision TEXT,
        image VARCHAR(255),
        home_title VARCHAR(255),
        home_subtitle TEXT,
        home_content TEXT,
        home_image VARCHAR(255),
        happy_customers INT DEFAULT 0,
        completed_projects INT DEFAULT 0,
        years_experience INT DEFAULT 0,
        awards INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // Örnek veri ekle
    $stmt = $db->prepare("INSERT INTO about (
        title, content, mission, vision, 
        home_title, home_subtitle, home_content,
        happy_customers, completed_projects, years_experience, awards
    ) VALUES (
        'Hakkımızda',
        'Seçkin Ajans olarak 20 yılı aşkın tecrübemizle matbaa ve promosyon sektöründe hizmet vermekteyiz.',
        'Müşterilerimize en kaliteli hizmeti sunmak ve sektörde öncü olmak.',
        'Türkiye''nin lider matbaa ve promosyon şirketi olmak.',
        'Seçkin Ajans',
        '20 Yıllık Tecrübe',
        'Profesyonel ekibimiz ve modern teknolojik altyapımızla, müşterilerimizin ihtiyaçlarına uygun çözümler üretiyoruz.',
        1000, 2500, 20, 50
    ) ON DUPLICATE KEY UPDATE id=id");
    
    $stmt->execute();
    echo "About tablosu oluşturuldu ve örnek veri eklendi.";
} catch (PDOException $e) {
    echo "Hata: " . $e->getMessage();
}
?> 