<?php
include_once 'includes/db.php'; // Session'ı ve API fonksiyonunu başlatır

// 1. Güvenlik: Kullanıcı giriş yapmış mı?
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

// 2. Silinecek bitkinin ID'sini al
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    // ID yoksa veya sayı değilse ana sayfaya yönlendir
    header('Location: dashboard.php');
    exit();
}
$plant_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// 3. Veritabanından bu bitkinin gerçekten bu kullanıcıya ait olup olmadığını kontrol et (Çok Önemli!)
// Başka bir kullanıcının bitkisini silmesini engeller.
$plant_check = supabase_api_request('GET', 'plants', ['id' => 'eq.' . $plant_id, 'user_id' => 'eq.' . $user_id]);

if ($plant_check && count($plant_check) > 0) {
    // Bitki bu kullanıcıya ait, silme işlemini yap
    supabase_api_request('DELETE', 'plants', ['id' => 'eq.' . $plant_id]);
}


// 4. İşlem bittikten sonra ana sayfaya geri dön
header('Location: dashboard.php');
exit();
?>