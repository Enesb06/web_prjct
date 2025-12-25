<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// db.php'yi dahil et, içinde supabase_api_request ve supabase_admin_api_request fonksiyonları var
include_once 'includes/db.php';

// Güvenlik: Sadece giriş yapmış kullanıcılar bu API'yi kullanabilir
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Yetkisiz erişim.']);
    exit();
}

$user_id = $_SESSION['user_id'];
$action = $_POST['action'] ?? $_GET['action'] ?? '';

header('Content-Type: application/json');

switch ($action) {

    case 'toggle_like':
        $post_id = $_POST['post_id'];
        if (empty($post_id)) {
            echo json_encode(['success' => false, 'error' => 'Geçersiz gönderi.']);
            exit();
        }

        // Kullanıcı bu gönderiyi daha önce beğenmiş mi?
        $existing_like = supabase_api_request('GET', 'post_likes', [
            'post_id' => 'eq.' . $post_id,
            'user_id' => 'eq.' . $user_id
        ]);

        if ($existing_like && count($existing_like) > 0) {
            // Beğeni varsa, sil (unlike)
            supabase_api_request('DELETE', 'post_likes', [
                'post_id' => 'eq.' . $post_id,
                'user_id' => 'eq.' . $user_id
            ]);
            $liked = false;
        } else {
            // Beğeni yoksa, ekle (like)
            supabase_api_request('POST', 'post_likes', [
                'post_id' => $post_id,
                'user_id' => $user_id
            ]);
            $liked = true;
        }

        // Güncel beğeni sayısını geri döndür
        $all_likes_for_post = supabase_api_request('GET', 'post_likes', ['post_id' => 'eq.' . $post_id]);
        $new_count = $all_likes_for_post ? count($all_likes_for_post) : 0;

        echo json_encode(['success' => true, 'liked' => $liked, 'new_count' => $new_count]);
        break;

    case 'add_comment':
        $post_id = $_POST['post_id'];
        $message = trim($_POST['comment_message']);

        if (empty($post_id) || empty($message)) {
            echo json_encode(['success' => false, 'error' => 'Eksik bilgi.']);
            exit();
        }

        $new_comment_data = [
            'post_id' => $post_id,
            'user_id' => $user_id,
            'username' => $_SESSION['username'],
            'message' => $message
        ];

        // API'nin "return=representation" özelliği sayesinde eklenen veri geri döner.
        $result = supabase_api_request('POST', 'forum_comments', $new_comment_data);

        if ($result && isset($result[0])) {
            echo json_encode(['success' => true, 'comment' => $result[0]]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Yorum eklenemedi.']);
        }
        break;

        
    case 'get_user_data':
        $user_info = supabase_api_request('GET', 'users', ['id' => 'eq.' . $user_id, 'select' => 'username,email,avatar_url']);
        if ($user_info && !empty($user_info)) {
            echo json_encode(['success' => true, 'data' => $user_info[0]]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Kullanıcı verisi bulunamadı.']);
        }
        break;

    case 'update_profile':
        $new_username = trim($_POST['username']);
        $new_avatar = $_POST['avatar_url'];

        if (empty($new_username)) {
            echo json_encode(['success' => false, 'error' => 'Kullanıcı adı boş olamaz.']);
            exit();
        }

        $updateData = [
            'username' => $new_username,
            'avatar_url' => $new_avatar
        ];
        
        $path = 'users?id=eq.' . $user_id;
        
        $result = supabase_api_request('PATCH', $path, $updateData);

        if ($result) {
            $_SESSION['username'] = $new_username; // Session'ı da güncelle
            echo json_encode(['success' => true, 'message' => 'Profil başarıyla güncellendi!']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Güncelleme sırasında bir hata oluştu. Bu kullanıcı adı başkası tarafından kullanılıyor olabilir.']);
        }
        break;


    case 'change_password':
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];

        if (empty($current_password) || empty($new_password)) {
            echo json_encode(['success' => false, 'error' => 'Tüm şifre alanları zorunludur.']);
            exit();
        }

        // 1. Mevcut kullanıcıyı şifresiyle birlikte çek
        $user_info = supabase_api_request('GET', 'users', ['id' => 'eq.' . $user_id]);
        if (!$user_info || empty($user_info)) {
            echo json_encode(['success' => false, 'error' => 'Kullanıcı doğrulaması başarısız.']);
            exit();
        }
        $user = $user_info[0];

        // 2. Mevcut şifreyi doğrula
        if (password_verify($current_password, $user['password'])) {
            // 3. Yeni şifreyi hash'le ve güncelle
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $path = 'users?id=eq.' . $user_id;
            $result = supabase_api_request('PATCH', $path, ['password' => $hashed_password]);

            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Şifreniz başarıyla değiştirildi.']);
            } else {
                echo json_encode(['success' => false, 'error' => 'Şifre güncellenirken bir hata oluştu.']);
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'Mevcut şifreniz yanlış.']);
        }
        break;
        
    // ==========================================================
    //        ADMİN FORUM İÇERİĞİ SİLME İŞLEVLERİ
    // ==========================================================
    case 'delete_post_admin':
        // Güvenlik: Sadece adminler bu işlemi yapabilir
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            echo json_encode(['success' => false, 'error' => 'Yetkisiz işlem.']);
            exit();
        }

        $post_id = $_POST['post_id'];
        if (empty($post_id)) {
            echo json_encode(['success' => false, 'error' => 'Geçersiz gönderi ID.']);
            exit();
        }

        // Önce ilgili yorumları ve beğenileri sil
        supabase_admin_api_request('DELETE', 'forum_comments', ['post_id' => 'eq.' . $post_id]);
        supabase_admin_api_request('DELETE', 'post_likes', ['post_id' => 'eq.' . $post_id]);

        // Sonra ana gönderiyi sil
        $result = supabase_admin_api_request('DELETE', 'forum_posts', ['id' => 'eq.' . $post_id]);
        
        echo json_encode(['success' => true]);
        break;

    case 'delete_comment_admin':
        // Güvenlik: Sadece adminler bu işlemi yapabilir
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            echo json_encode(['success' => false, 'error' => 'Yetkisiz işlem.']);
            exit();
        }

        $comment_id = $_POST['comment_id'];
        if (empty($comment_id)) {
            echo json_encode(['success' => false, 'error' => 'Geçersiz yorum ID.']);
            exit();
        }

        // Yorumu sil
        $result = supabase_admin_api_request('DELETE', 'forum_comments', ['id' => 'eq.' . $comment_id]);
        
        echo json_encode(['success' => true]);
        break;

    // ==========================================================
    //        YENİ EKLENEN ADMİN BİTKİ YÖNETİM İŞLEVLERİ
    // ==========================================================
    case 'delete_plant_admin':
        // Güvenlik: Sadece adminler
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            echo json_encode(['success' => false, 'error' => 'Yetkisiz işlem.']);
            exit();
        }

        $plant_id = $_POST['plant_id'];
        if (empty($plant_id)) {
            echo json_encode(['success' => false, 'error' => 'Geçersiz bitki ID.']);
            exit();
        }
        
        // Admin API'sini kullanarak bitkiyi sil
        supabase_admin_api_request('DELETE', 'plants', ['id' => 'eq.' . $plant_id]);
        
        echo json_encode(['success' => true]);
        break;

 

    case 'update_plant_dates_admin':
        // Güvenlik: Sadece adminler
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            echo json_encode(['success' => false, 'error' => 'Yetkisiz işlem.']);
            exit();
        }

        $plant_id = $_POST['plant_id'];
        $new_watered_date = $_POST['last_watered'];
        $new_fertilized_date = $_POST['last_fertilized'];

        if (empty($plant_id) || empty($new_watered_date) || empty($new_fertilized_date)) {
            echo json_encode(['success' => false, 'error' => 'Tüm alanlar zorunludur.']);
            exit();
        }

        // =================== DÜZELTME: Veritabanı sütun adları doğru yazıldı ===================
        $updateData = [
            'last_watered_date' => $new_watered_date,
            'last_fertilized_date' => $new_fertilized_date
        ];
        
        $path = 'plants?id=eq.' . $plant_id;
        
        $result = supabase_admin_api_request('PATCH', $path, $updateData);

        if ($result && isset($result[0])) {
            // Başarılı olursa güncellenmiş veriyi geri döndür
            echo json_encode(['success' => true, 'updated_plant' => $result[0]]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Bitki güncellenirken bir hata oluştu.']);
        }
        break;



    default:
        echo json_encode(['success' => false, 'error' => 'Geçersiz işlem.']);
        break;
}
?>