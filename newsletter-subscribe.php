<?php
require_once 'includes/config.php';

// AJAX isteği kontrolü
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

// Yanıt formatını ayarla
header('Content-Type: application/json');

// Hata mesajı
$response = [
    'success' => false,
    'message' => 'Bir hata oluştu. Lütfen tekrar deneyiniz.'
];

// POST isteği kontrolü
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // E-posta adresi kontrolü
    if (empty($_POST['email'])) {
        $response['message'] = 'E-posta adresi gereklidir.';
        echo json_encode($response);
        exit;
    }
    
    $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
    if (!$email) {
        $response['message'] = 'Geçerli bir e-posta adresi giriniz.';
        echo json_encode($response);
        exit;
    }
    
    try {
        // E-posta adresinin daha önce kaydedilip kaydedilmediğini kontrol et
        $stmt = $db->prepare("SELECT id FROM newsletter_subscribers WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->rowCount() > 0) {
            $response['message'] = 'Bu e-posta adresi zaten bültenimize kayıtlı.';
            echo json_encode($response);
            exit;
        }
        
        // Yeni abone ekle
        $stmt = $db->prepare("INSERT INTO newsletter_subscribers (email) VALUES (?)");
        $stmt->execute([$email]);
        
        if ($stmt->rowCount() > 0) {
            $response['success'] = true;
            $response['message'] = 'Bültenimize başarıyla abone oldunuz. Teşekkür ederiz!';
        }
    } catch (PDOException $e) {
        error_log("Bülten aboneliği hatası: " . $e->getMessage());
        $response['message'] = 'Bir hata oluştu. Lütfen daha sonra tekrar deneyiniz.';
    }
}

echo json_encode($response);
exit; 