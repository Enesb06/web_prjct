<?php
include_once 'includes/header.php';
$error = '';

// Giriş yapmamış kullanıcıyı engelle
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}
$current_user_id = $_SESSION['user_id'];

// Yeni mesaj gönderildiyse (DOSYA YÜKLEME MANTIĞI EKLENDİ)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message']) && !empty(trim($_POST['message']))) {
    
    $image_path_for_db = null; // Varsayılan resim yolu

    // YENİ: Dosya yükleme mantığı
    if (isset($_FILES['post_image']) && $_FILES['post_image']['error'] === UPLOAD_ERR_OK) {
        
        $upload_dir = 'assets/images/forum_uploads/';
        // Eğer bu klasör yoksa oluşturmayı deneyebiliriz (opsiyonel ama önerilir)
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        $file = $_FILES['post_image'];
        
        // Güvenlik kontrolleri (boyut, tip, benzersiz isim)
        if ($file['size'] <= 4194304) { // Max 4MB
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime_type = finfo_file($finfo, $file['tmp_name']);
            $allowed_mime_types = ['image/jpeg', 'image/png', 'image/gif'];
            finfo_close($finfo);

            if (in_array($mime_type, $allowed_mime_types)) {
                $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                $unique_filename = 'post_' . uniqid('', true) . '.' . $file_extension;
                $destination = $upload_dir . $unique_filename;

                if (move_uploaded_file($file['tmp_name'], $destination)) {
                    $image_path_for_db = $destination; // Başarılı olursa yolu değişkene ata
                }
            }
        }
    }

    $newMessage = [
        'user_id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'],
        'message' => trim($_POST['message']),
        'image_url' => $image_path_for_db // YENİ: Resim yolunu veritabanına ekle
    ];
    
    supabase_api_request('POST', 'forum_posts', $newMessage);
    header('Location: forum.php'); // Sayfayı yenile ve yeni gönderiyi gör
    exit();
}


// =========================================================
// YENİ VE DOĞRU VERİ ÇEKME KODU (Bu kısım aynı kalıyor)
// =========================================================

// 1. Tüm gönderileri çek
$posts = supabase_api_request('GET', 'forum_posts', ['order' => 'created_at.desc']);

// 2. Tüm beğenileri, yorumları VE KULLANICILARI (avatar için) tek seferde çek
$all_likes = supabase_api_request('GET', 'post_likes');
$all_comments = supabase_api_request('GET', 'forum_comments', ['order' => 'created_at.asc']);
$all_users = supabase_api_request('GET', 'users', ['select' => 'id,avatar_url']); // Sadece id ve avatar_url al

// 3. Verileri daha hızlı erişim için grupla/haritala
$avatars_by_user_id = [];
if ($all_users) {
    foreach ($all_users as $user) {
        $avatars_by_user_id[$user['id']] = $user['avatar_url'];
    }
}

$likes_by_post = [];
$user_likes = [];
if ($all_likes) {
    foreach ($all_likes as $like) {
        $likes_by_post[$like['post_id']][] = $like['user_id'];
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

<h2>Topluluk Forumu</h2>
<p>Diğer bitki severlerle tecrübelerinizi paylaşın!</p>

<!-- ============================================= -->
<!--       DEĞİŞTİRİLEN MESAJ GÖNDERME FORMU       -->
<!-- ============================================= -->

<!-- Dosya yükleme için enctype eklendi -->
<form action="forum.php" method="POST" enctype="multipart/form-data">
    <label for="message">Yeni Mesaj:</label>
    <textarea name="message" id="message" rows="4" required placeholder="Buraya yazın..."></textarea>
    
    <!-- YENİ: Resim Yükleme Alanı Eklendi -->
    <label for="post_image" style="margin-top: 10px;">Resim Ekle (Opsiyonel):</label>
    <input type="file" id="post_image" name="post_image" accept="image/png, image/jpeg, image/gif">
    
    <button type="submit">Gönder</button>
</form>

<hr style="margin: 30px 0;">

<!-- Mevcut mesajlar -->
<div class="forum-posts-container">
    <h3>Son Mesajlar</h3>
    <?php if ($posts && count($posts) > 0): ?>
        <?php foreach ($posts as $post):
            // Değişkenleri burada tanımlayalım
            $post_id = $post['id'];
            $post_author_id = $post['user_id'];
            
            // Beğeni ve yorum sayılarını alalım
            $like_count = isset($likes_by_post[$post_id]) ? count($likes_by_post[$post_id]) : 0;
            $comments = $comments_by_post[$post_id] ?? [];
            $comment_count = count($comments);
            
            // Mevcut kullanıcı bu gönderiyi beğenmiş mi?
            $is_liked_by_user = in_array($post_id, $user_likes);
            
            // Gönderiyi yazan kullanıcının avatarını bulalım
            $author_avatar = $avatars_by_user_id[$post_author_id] ?? 'avatar1.png'; // Bulamazsa varsayılan
        ?>
            <div class="forum-post" id="post-<?php echo $post_id; ?>">
                <!-- KULLANICI BİLGİLERİNİN GÖSTERİLDİĞİ YER -->
                <div class="post-meta">
                    <img src="assets/images/avatars/<?php echo htmlspecialchars($author_avatar); ?>" alt="Avatar" class="avatar">
                    <div class="author-info">
                        <strong><?php echo htmlspecialchars($post['username']); ?></strong>
                        <div>dedi ki:</div>
                    </div>
                    <span class="post-date"><?php echo date('d M Y, H:i', strtotime($post['created_at'])); ?></span>
                </div>

                <!-- YENİ: GÖNDERİ RESMİNİ GÖSTERME KODU -->
                <?php if (!empty($post['image_url'])): ?>
                    <div class="post-image-container">
                        <img src="<?php echo htmlspecialchars($post['image_url']); ?>" alt="Forum Gönderi Resmi" class="forum-post-image">
                    </div>
                <?php endif; ?>
                <!-- YENİ KOD SONU -->

                <p class="post-content"><?php echo nl2br(htmlspecialchars($post['message'])); ?></p>
                
                <div class="post-actions">
                    <span class="action-btn like-btn <?php echo $is_liked_by_user ? 'liked' : ''; ?>" data-post-id="<?php echo $post_id; ?>">
                        ❤️ Beğen (<span class="like-count"><?php echo $like_count; ?></span>)
                    </span>
                    <span class="action-btn comment-toggle-btn">
                        💬 Yorumlar (<?php echo $comment_count; ?>)
                    </span>
                </div>

                <div class="comments-section" style="display: none;">
                    <div class="existing-comments">
                        <?php foreach($comments as $comment): ?>
                            <div class="comment">
                                <strong><?php echo htmlspecialchars($comment['username']); ?>:</strong>
                                <?php echo nl2br(htmlspecialchars($comment['message'])); ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <form class="comment-form" method="POST">
                        <input type="hidden" name="action" value="add_comment">
                        <input type="hidden" name="post_id" value="<?php echo $post_id; ?>">
                        <input type="text" name="comment_message" placeholder="Yorumunu yaz..." required>
                        <button type="submit">Gönder</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>Henüz hiç mesaj yazılmamış. İlk mesajı sen yaz!</p>
    <?php endif; ?>
</div>

<?php include_once 'includes/footer.php'; ?>