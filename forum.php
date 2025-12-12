<?php
include_once 'includes/header.php';
$error = '';

// Giriş yapmamış kullanıcıyı engelle
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

// Yeni mesaj gönderildiyse
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty(trim($_POST['message']))) {
    $newMessage = [
        'user_id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'], // Gösterim kolaylığı için
        'message' => trim($_POST['message'])
    ];
    supabase_api_request('POST', 'forum_posts', $newMessage);
    // Sayfanın yeniden post edilmesini önlemek için yönlendir
    header('Location: forum.php');
    exit();
}

// Tüm forum mesajlarını en yeniden eskiye doğru çek
$posts = supabase_api_request('GET', 'forum_posts', ['order' => 'created_at.desc']);
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
        <?php foreach ($posts as $post): ?>
            <div class="forum-post">
                <div class="post-meta">
                    <strong><?php echo htmlspecialchars($post['username']); ?></strong> dedi ki 
                    <span style="float:right;"><?php echo date('d M Y, H:i', strtotime($post['created_at'])); ?></span>
                </div>
                <p><?php echo nl2br(htmlspecialchars($post['message'])); ?></p>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>Henüz hiç mesaj yazılmamış. İlk mesajı sen yaz!</p>
    <?php endif; ?>
</div>

<?php include_once 'includes/footer.php'; ?>