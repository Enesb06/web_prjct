<?php
// db.php'yi dahil et, bu dosya oturumu zaten başlatıyor
include_once 'db.php';
include_once 'includes/header.php';
$error = '';

// Giriş yapmamış kullanıcıyı engelle
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}
$current_user_id = $_SESSION['user_id'];

// Yeni mesaj gönderildiyse (Bu kısım aynı kalıyor)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty(trim($_POST['message']))) {
    // ... (Mevcut mesaj gönderme kodunuz burada kalacak)
}

// =========================================================
// YENİ: Gönderileri, beğenileri ve yorumları verimli bir şekilde çekme
// =========================================================

// 1. Tüm gönderileri çek
$posts = supabase_api_request('GET', 'forum_posts', ['order' => 'created_at.desc']);

// 2. Tüm beğenileri ve yorumları tek seferde çek
$all_likes = supabase_api_request('GET', 'post_likes');
$all_comments = supabase_api_request('GET', 'forum_comments', ['order' => 'created_at.asc']); // Yorumları eskiden yeniye sırala

// 3. Beğenileri ve yorumları gönderi ID'sine göre grupla
$likes_by_post = [];
$user_likes = []; // Mevcut kullanıcının hangi postları beğendiğini tutmak için
if ($all_likes) {
    foreach ($all_likes as $like) {
        if (!isset($likes_by_post[$like['post_id']])) {
            $likes_by_post[$like['post_id']] = 0;
        }
        $likes_by_post[$like['post_id']]++;

        if ($like['user_id'] == $current_user_id) {
            $user_likes[] = $like['post_id'];
        }
    }
}

$comments_by_post = [];
if ($all_comments) {
    foreach ($all_comments as $comment) {
        $comments_by_post[$comment['post_id']][] = $comment;
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Bitki Bakım Takipçisi</title>
    <link rel="stylesheet" href="assets/style.css">
    
    <!-- ========================================================= -->
    <!-- YENİ EKLENEN LOTTIE PLAYER KÜTÜPHANESİ (ANİMASYON İÇİN GEREKLİ) -->
    <script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>
    <!-- ========================================================= -->

</head>
<body>
    <header>
        <h1>Bitki Bakım Takipçisi</h1>
    </header>
    <!-- Sarmaşık Efektleri -->
    <div class="ivy-overlay ivy-left"></div>
    <div class="ivy-overlay ivy-right"></div>
    <nav>
        <ul>
            <?php if (isset($_SESSION['user_id'])): ?>
                <li><a href="dashboard.php">Ana Sayfa</a></li>
                <li><a href="add_plant.php">Bitki Ekle</a></li>
                <li><a href="encyclopedia.php">Bitki Ansiklopedisi</a></li> 
                <li><a href="forum.php">Forum</a></li>
                <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                    <li><a href="admin.php">Admin Paneli</a></li>
                <?php endif; ?>
                    <li><a href="#" id="showProfileModal">Profilim</a></li> 
            <?php else: ?>
                <!-- Not: Giriş/Kayıt butonlarının ID'lerini JS ile uyumlu hale getirdim -->
             <li><a href="index.php">Giriş Yap</a></li>
                <li><a href="register.php">Kayıt Ol</a></li>
            <?php endif; ?>
        </ul>
    </nav>
    <div class="container">