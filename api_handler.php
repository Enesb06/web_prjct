<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// db.php'yi dahil et, içinde supabase_api_request fonksiyonu var
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

    // BU KOD BLOĞUNU api_handler.php'de BULUP AŞAĞIDAKİ İLE DEĞİŞTİRİN
    case 'update_profile':
        // DEĞİŞİKLİK: Artık sadece username ve avatar_url alıyoruz.
        $new_username = trim($_POST['username']);
        $new_avatar = $_POST['avatar_url'];

        // DEĞİŞİKLİK: E-posta kontrolünü kaldırdık.
        if (empty($new_username)) {
            echo json_encode(['success' => false, 'error' => 'Kullanıcı adı boş olamaz.']);
            exit();
        }

        // DEĞİŞİKLİK: Güncellenecek veri dizisinden email'i çıkardık.
        $updateData = [
            'username' => $new_username,
            'avatar_url' => $new_avatar
        ];
        
        $path = 'users?id=eq.' . $user_id;
        
        // DÜZELTİLEN SATIR BURASI
        $result = supabase_api_request('PATCH', $path, $updateData);

        if ($result) {
            $_SESSION['username'] = $new_username; // Session'ı da güncelle
            echo json_encode(['success' => true, 'message' => 'Profil başarıyla güncellendi!']);
        } else {
            // DEĞİŞİKLİK: Hata mesajını basitleştirdik.
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

    default:
        echo json_encode(['success' => false, 'error' => 'Geçersiz işlem.']);
        break;
}
?>