<?php
include_once 'includes/header.php';
$error = '';

// Giriş yapmamış kullanıcıyı engelle
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}
$current_user_id = $_SESSION['user_id'];

// Yeni mesaj gönderildiyse (Bu kısım aynı)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message']) && !empty(trim($_POST['message']))) {
    $newMessage = [
        'user_id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'],
        'message' => trim($_POST['message'])
    ];
    supabase_api_request('POST', 'forum_posts', $newMessage);
    header('Location: forum.php');
    exit();
}

// =========================================================
// YENİ VE DOĞRU VERİ ÇEKME KODU
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

<!-- Mesaj gönderme formu -->
<form action="forum.php" method="POST">
    <label for="message">Yeni Mesaj:</label>
    <textarea name="message" id="message" rows="4" required placeholder="Buraya yazın..."></textarea>
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
                <!-- KULLANICI BİLGİLERİNİN GÖSTERİLDİĞİ YER (EKSİK OLAN KISIM) -->
                <div class="post-meta">
                    <img src="assets/images/avatars/<?php echo htmlspecialchars($author_avatar); ?>" alt="Avatar" class="avatar">
                    <div class="author-info">
                        <strong><?php echo htmlspecialchars($post['username']); ?></strong>
                        <div>dedi ki:</div>
                    </div>
                    <span class="post-date"><?php echo date('d M Y, H:i', strtotime($post['created_at'])); ?></span>
                </div>

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