<?php
// Veritabanı bağlantısı
require_once 'includes/config.php';

// Hata raporlama
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Stil ekle
echo '<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    h2 { color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 10px; }
    h3 { color: #2980b9; margin-top: 30px; }
    pre { background-color: #f8f9fa; padding: 15px; border-radius: 5px; overflow: auto; }
    .error { color: #e74c3c; font-weight: bold; }
    .success { color: #27ae60; font-weight: bold; }
    .warning { color: #f39c12; font-weight: bold; }
    .table-info { margin-bottom: 20px; }
    .fix-button { 
        display: inline-block; 
        background-color: #3498db; 
        color: white; 
        padding: 8px 15px; 
        text-decoration: none; 
        border-radius: 4px; 
        margin-top: 10px; 
    }
    .fix-button:hover { 
        background-color: #2980b9; 
    }
</style>';

echo '<h1>Veritabanı Kontrol Paneli</h1>';

// Tabloları listele
echo '<h2>Veritabanı Tabloları</h2>';
try {
    $stmt = $db->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo '<div class="table-info">';
    echo '<p>Toplam <span class="success">' . count($tables) . '</span> tablo bulundu.</p>';
    echo '</div>';
    
    echo '<pre>';
    print_r($tables);
    echo '</pre>';
    
    // Gerekli tabloların varlığını kontrol et
    $required_tables = ['categories', 'services', 'urunler'];
    $missing_tables = array_diff($required_tables, $tables);
    
    if (!empty($missing_tables)) {
        echo '<div class="error">';
        echo '<p>Eksik tablolar tespit edildi:</p>';
        echo '<ul>';
        foreach ($missing_tables as $table) {
            echo '<li>' . htmlspecialchars($table) . '</li>';
        }
        echo '</ul>';
        echo '</div>';
    }
    
    // Kategorileri kontrol et
    echo '<h3>Kategoriler Tablosu</h3>';
    try {
        if (in_array('categories', $tables)) {
            // Tablo yapısını kontrol et
            $stmt = $db->query("DESCRIBE categories");
            $structure = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo '<div class="table-info">';
            echo '<p>Tablo yapısı:</p>';
            echo '</div>';
            
            echo '<pre>';
            print_r($structure);
            echo '</pre>';
            
            // Gerekli sütunları kontrol et
            $required_columns = ['id', 'name', 'slug', 'status'];
            $existing_columns = array_column($structure, 'Field');
            $missing_columns = array_diff($required_columns, $existing_columns);
            
            if (!empty($missing_columns)) {
                echo '<div class="error">';
                echo '<p>Eksik sütunlar tespit edildi:</p>';
                echo '<ul>';
                foreach ($missing_columns as $column) {
                    echo '<li>' . htmlspecialchars($column) . '</li>';
                }
                echo '</ul>';
                echo '</div>';
            }
            
            // Verileri kontrol et
            $stmt = $db->query("SELECT COUNT(*) FROM categories");
            $count = $stmt->fetchColumn();
            
            echo '<div class="table-info">';
            echo '<p>Toplam <span class="' . ($count > 0 ? 'success' : 'warning') . '">' . $count . '</span> kategori bulundu.</p>';
            echo '</div>';
            
            if ($count > 0) {
                $stmt = $db->query("SELECT * FROM categories LIMIT 5");
                $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                echo '<pre>';
                print_r($categories);
                echo '</pre>';
            } else {
                echo '<p class="warning">Kategoriler tablosunda veri bulunamadı!</p>';
                echo '<a href="admin/category-add.php" class="fix-button">Kategori Ekle</a>';
            }
        } else {
            echo '<p class="error">Kategoriler tablosu bulunamadı!</p>';
        }
    } catch (PDOException $e) {
        echo '<p class="error">Kategoriler tablosu hatası: ' . htmlspecialchars($e->getMessage()) . '</p>';
    }
    
    // Hizmetleri kontrol et
    echo '<h3>Hizmetler Tablosu</h3>';
    try {
        if (in_array('services', $tables)) {
            // Tablo yapısını kontrol et
            $stmt = $db->query("DESCRIBE services");
            $structure = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo '<div class="table-info">';
            echo '<p>Tablo yapısı:</p>';
            echo '</div>';
            
            echo '<pre>';
            print_r($structure);
            echo '</pre>';
            
            // Gerekli sütunları kontrol et
            $required_columns = ['id', 'name', 'description', 'image', 'status'];
            $existing_columns = array_column($structure, 'Field');
            $missing_columns = array_diff($required_columns, $existing_columns);
            
            if (!empty($missing_columns)) {
                echo '<div class="error">';
                echo '<p>Eksik sütunlar tespit edildi:</p>';
                echo '<ul>';
                foreach ($missing_columns as $column) {
                    echo '<li>' . htmlspecialchars($column) . '</li>';
                }
                echo '</ul>';
                echo '</div>';
            }
            
            // Verileri kontrol et
            $stmt = $db->query("SELECT COUNT(*) FROM services");
            $count = $stmt->fetchColumn();
            
            echo '<div class="table-info">';
            echo '<p>Toplam <span class="' . ($count > 0 ? 'success' : 'warning') . '">' . $count . '</span> hizmet bulundu.</p>';
            echo '</div>';
            
            if ($count > 0) {
                $stmt = $db->query("SELECT * FROM services LIMIT 5");
                $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                echo '<pre>';
                print_r($services);
                echo '</pre>';
            } else {
                echo '<p class="warning">Hizmetler tablosunda veri bulunamadı!</p>';
                echo '<a href="admin/service-add.php" class="fix-button">Hizmet Ekle</a>';
            }
        } else {
            echo '<p class="error">Hizmetler tablosu bulunamadı!</p>';
        }
    } catch (PDOException $e) {
        echo '<p class="error">Hizmetler tablosu hatası: ' . htmlspecialchars($e->getMessage()) . '</p>';
    }
    
    // Son eklenen ürünleri kontrol et
    echo '<h3>Ürünler Tablosu</h3>';
    try {
        if (in_array('urunler', $tables)) {
            // Tablo yapısını kontrol et
            $stmt = $db->query("DESCRIBE urunler");
            $structure = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo '<div class="table-info">';
            echo '<p>Tablo yapısı:</p>';
            echo '</div>';
            
            echo '<pre>';
            print_r($structure);
            echo '</pre>';
            
            // Gerekli sütunları kontrol et
            $required_columns = ['id', 'name', 'description', 'image', 'category_id', 'status'];
            $existing_columns = array_column($structure, 'Field');
            $missing_columns = array_diff($required_columns, $existing_columns);
            
            if (!empty($missing_columns)) {
                echo '<div class="error">';
                echo '<p>Eksik sütunlar tespit edildi:</p>';
                echo '<ul>';
                foreach ($missing_columns as $column) {
                    echo '<li>' . htmlspecialchars($column) . '</li>';
                }
                echo '</ul>';
                echo '</div>';
            }
            
            // Verileri kontrol et
            $stmt = $db->query("SELECT COUNT(*) FROM urunler");
            $count = $stmt->fetchColumn();
            
            echo '<div class="table-info">';
            echo '<p>Toplam <span class="' . ($count > 0 ? 'success' : 'warning') . '">' . $count . '</span> ürün bulundu.</p>';
            echo '</div>';
            
            if ($count > 0) {
                try {
                    $stmt = $db->query("
                        SELECT p.*, c.name as category_name 
                        FROM urunler p 
                        LEFT JOIN categories c ON p.category_id = c.id 
                        WHERE p.status = 1 
                        ORDER BY p.created_at DESC 
                        LIMIT 5
                    ");
                    $latest_products = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    echo '<pre>';
                    print_r($latest_products);
                    echo '</pre>';
                } catch (PDOException $e) {
                    echo '<p class="error">Ürün-kategori ilişkisi hatası: ' . htmlspecialchars($e->getMessage()) . '</p>';
                    
                    // Basit sorgu dene
                    $stmt = $db->query("SELECT * FROM urunler LIMIT 5");
                    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    echo '<p class="warning">Basit ürün sorgusu sonuçları:</p>';
                    echo '<pre>';
                    print_r($products);
                    echo '</pre>';
                }
            } else {
                echo '<p class="warning">Ürünler tablosunda veri bulunamadı!</p>';
                echo '<a href="admin/urun-add.php" class="fix-button">Ürün Ekle</a>';
            }
        } else {
            echo '<p class="error">Ürünler tablosu bulunamadı!</p>';
        }
    } catch (PDOException $e) {
        echo '<p class="error">Ürünler tablosu hatası: ' . htmlspecialchars($e->getMessage()) . '</p>';
    }
    
    // Veritabanı bağlantı bilgilerini kontrol et
    echo '<h3>Veritabanı Bağlantı Bilgileri</h3>';
    echo '<div class="table-info">';
    echo '<p>Veritabanı Adı: <span class="success">' . DB_NAME . '</span></p>';
    echo '<p>Veritabanı Sunucusu: <span class="success">' . DB_HOST . '</span></p>';
    echo '<p>Karakter Seti: <span class="success">utf8mb4</span></p>';
    echo '</div>';
    
} catch (PDOException $e) {
    echo '<p class="error">Genel hata: ' . htmlspecialchars($e->getMessage()) . '</p>';
}

// Düzeltme önerileri
echo '<h2>Düzeltme Önerileri</h2>';
echo '<ul>';
echo '<li>Eksik tablolar varsa, ilgili SQL dosyalarını çalıştırın.</li>';
echo '<li>Veri yoksa, admin panelinden veri ekleyin.</li>';
echo '<li>Tablo yapısında sorun varsa, veritabanı şemasını güncelleyin.</li>';
echo '</ul>';

echo '<p><a href="index.php" class="fix-button">Ana Sayfaya Dön</a></p>';
?> 