<?php
include_once 'includes/header.php';
$error = '';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}
$current_user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message']) && !empty(trim($_POST['message']))) {
    
    $image_path_for_db = null;

    // =============== DEĞİŞTİRİLEN DOSYA YÜKLEME BLOĞU BAŞLANGICI ===============
    if (isset($_FILES['post_image']) && $_FILES['post_image']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['post_image'];

        if ($file['size'] > 4194304) { // Max 4MB
            $error = 'Hata: Dosya boyutu 4MB\'den büyük olamaz.';
        } else {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime_type = finfo_file($finfo, $file['tmp_name']);
            $allowed_mime_types = ['image/jpeg', 'image/png', 'image/gif'];
            finfo_close($finfo);

            if (in_array($mime_type, $allowed_mime_types)) {
                $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                
                $unique_filename = 'post_' . uniqid('', true) . '.' . $file_extension;
                $storage_path = 'forum_uploads/' . $_SESSION['user_id'] . '/' . $unique_filename;

                
                $image_path_for_db = upload_to_supabase_storage($file['tmp_name'], $storage_path, $mime_type);
                
                if ($image_path_for_db === null) {
                    $error = 'Resim Supabase\'e yüklenirken bir hata oluştu.';
                    
                }
            } else {
                 
            }
        }
    }
    


    $newMessage = [
        'user_id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'],
        'message' => trim($_POST['message']),
        'image_url' => $image_path_for_db // Başarılıysa URL, değilse null olacak
    ];
    
    supabase_api_request('POST', 'forum_posts', $newMessage);
    header('Location: forum.php'); 
    exit();
}





// Veri çekme kodları 
$posts = supabase_api_request('GET', 'forum_posts', ['order' => 'created_at.desc']);
$all_likes = supabase_api_request('GET', 'post_likes');
$all_comments = supabase_api_request('GET', 'forum_comments', ['order' => 'created_at.asc']);
$all_users = supabase_api_request('GET', 'users', ['select' => 'id,avatar_url']);

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

<!-- ========================================================= -->
<!--          YENİ EKLENEN KÜTÜPHANE DOSYALARI                 -->
<!-- ========================================================= -->
<!-- Font Awesome (Kalem ikonu için) -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<!-- Swiper.js CSS (Slider'ın görünümü için) -->
<link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css" />


<!-- YENİ SLIDER KISMI -->
<div class="swiper-container forum-header-slider">
    <div class="swiper-wrapper">
        <!-- Slide 1 -->
        <div class="swiper-slide" style="background-image:url('assets/images/slider/slider1.jpg');">
            
        </div>
        <!-- Slide 2 -->
        <div class="swiper-slide" style="background-image:url('assets/images/slider/slider2.png');">
           
        </div>
        <!-- Slide 3 -->
        <div class="swiper-slide" style="background-image:url('assets/images/slider/slider3.png');">
            
        </div>
    </div>
    <!-- Navigasyon Okları -->
    <div class="swiper-button-next" style="color: #fff;"></div>
    <div class="swiper-button-prev" style="color: #fff;"></div>
</div>


<h2>Topluluk Forumu</h2>
<p>Diğer bitki severlerle tecrübelerinizi paylaşın!</p>
<hr style="margin: 30px 0;">


<!-- Mevcut mesajlar -->
<div class="forum-posts-container">
    <h3>Son Mesajlar</h3>
    <?php if ($posts && count($posts) > 0): ?>
        <?php foreach ($posts as $post):
            
            $post_id = $post['id'];
            $post_author_id = $post['user_id'];
            $like_count = isset($likes_by_post[$post_id]) ? count($likes_by_post[$post_id]) : 0;
            $comments = $comments_by_post[$post_id] ?? [];
            $comment_count = count($comments);
            $is_liked_by_user = in_array($post_id, $user_likes);
            $author_avatar = $avatars_by_user_id[$post_author_id] ?? 'avatar1.png';
        ?>
            <div class="forum-post" id="post-<?php echo $post_id; ?>">
                <div class="post-meta">
                    <img src="assets/images/avatars/<?php echo htmlspecialchars($author_avatar); ?>" alt="Avatar" class="avatar">
                    <div class="author-info">
                        <strong><?php echo htmlspecialchars($post['username']); ?></strong>
                        <div>dedi ki:</div>
                    </div>
                    <span class="post-date"><?php echo date('d M Y, H:i', strtotime($post['created_at'])); ?></span>
                </div>

                <?php if (!empty($post['image_url'])): ?>
                    <div class="post-image-container">
                        <img src="<?php echo htmlspecialchars($post['image_url']); ?>" alt="Forum Gönderi Resmi" class="forum-post-image">
                    </div>
                <?php endif; ?>

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


<!-- YENİ PAYLAŞIM YAP BUTONU -->
<a href="#" id="fab-share-button" class="fab-share-post">
    <i class="fas fa-pencil-alt"></i>
    Sen de Görüşlerini Paylaş
</a>


<!-- YENİ POP-UP GÖNDERİ FORMU  -->
<div class="post-modal-overlay" id="post-modal">
    <div class="post-modal-card">
        <span class="close-btn" id="close-modal-btn">&times;</span>
        <h2>Yeni Gönderi Oluştur</h2>
        <form action="forum.php" method="POST" enctype="multipart/form-data">
            <label for="message_modal">Mesajın:</label>
            <textarea name="message" id="message_modal" rows="5" required placeholder="Buraya yazın..."></textarea>
            
            <label for="post_image_modal" style="margin-top: 15px;">Resim Ekle (Opsiyonel):</label>
            <input type="file" id="post_image_modal" name="post_image" accept="image/png, image/jpeg, image/gif">
            
            <button type="submit" style="margin-top: 20px; width: 100%;">Gönder</button>
        </form>
    </div>
</div>


<?php include_once 'includes/footer.php'; ?>


<!-- ========================================================= -->
<!--        YENİ EKLENEN KÜTÜPHANE VE KODLAR                   -->
<!-- ========================================================= -->
<!-- Swiper.js JS (Slider'ın çalışması için) -->
<script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. Swiper Slider'ı Başlatma
    const swiper = new Swiper('.forum-header-slider', {
        loop: true,
        autoplay: {
            delay: 4000,
            disableOnInteraction: false,
        },
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },
    });

    
    const fabButton = document.getElementById('fab-share-button');
    const postModal = document.getElementById('post-modal');
    const closeModalBtn = document.getElementById('close-modal-btn');

    fabButton.addEventListener('click', function (e) {
        e.preventDefault();
        postModal.classList.add('active');
    });

    closeModalBtn.addEventListener('click', function () {
        postModal.classList.remove('active');
    });

    postModal.addEventListener('click', function (e) {
        if (e.target === postModal) {
            postModal.classList.remove('active');
        }
    });
});
</script>