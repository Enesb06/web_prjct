<?php
include_once 'includes/header.php';

// Giriş yapmış mı ve rolü admin mi diye kontrol et
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    echo "<div class='message error'>Bu sayfaya erişim yetkiniz yok.</div>";
    include_once 'includes/footer.php';
    exit();
}

// Tüm verileri çek (Normal fonksiyonu kullanabiliriz, çünkü sadece okuma yapıyoruz)
$all_users = supabase_api_request('GET', 'users', ['order' => 'created_at.desc']);
$all_posts = supabase_api_request('GET', 'forum_posts', ['order' => 'created_at.desc']);
$all_comments = supabase_api_request('GET', 'forum_comments', ['order' => 'created_at.desc']);
?>

<style>
    /* Admin paneli için basit stil iyileştirmeleri */
    h2, h3 { border-bottom: 2px solid #eee; padding-bottom: 10px; margin-top: 40px; }
    table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 0.9em; }
    th, td { padding: 12px 15px; border: 1px solid #ddd; text-align: left; }
    th { background-color: #f4f4f4; }
    tr:nth-child(even) { background-color: #f9f9f9; }
    .delete-btn {
        background-color: #e74c3c; color: white; border: none;
        padding: 5px 10px; border-radius: 4px; cursor: pointer;
    }
    .delete-btn:hover { background-color: #c0392b; }
    td.actions { text-align: center; }
</style>

<h2>Admin Paneli</h2>
<p>Sistemdeki tüm verileri buradan yönetebilirsiniz.</p>

<!-- KULLANICILAR TABLOSU -->
<h3>Kullanıcılar (<?php echo $all_users ? count($all_users) : 0; ?>)</h3>
<table border="1">
    <thead>
        <tr>
            <th>ID</th>
            <th>Kullanıcı Adı</th>
            <th>Email</th>
            <th>Rol</th>
            <th>Kayıt Tarihi</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($all_users): foreach($all_users as $user): ?>
        <tr>
            <td><?php echo $user['id']; ?></td>
            <td><?php echo htmlspecialchars($user['username']); ?></td>
            <td><?php echo htmlspecialchars($user['email']); ?></td>
            <td><?php echo htmlspecialchars($user['role']); ?></td>
            <td><?php echo date('d M Y H:i', strtotime($user['created_at'])); ?></td>
        </tr>
        <?php endforeach; else: ?>
            <tr><td colspan="5">Hiç kullanıcı bulunamadı.</td></tr>
        <?php endif; ?>
    </tbody>
</table>


<!-- GÖNDERİLER TABLOSU -->
<h3 id="posts">Tüm Forum Gönderileri (<?php echo $all_posts ? count($all_posts) : 0; ?>)</h3>
<table border="1">
    <thead>
        <tr>
            <th>ID</th>
            <th>Kullanıcı Adı</th>
            <th>Mesaj</th>
            <th>Tarih</th>
            <th style="width: 80px;">İşlemler</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($all_posts): foreach($all_posts as $post): ?>
        <tr id="post-row-<?php echo $post['id']; ?>">
            <td><?php echo $post['id']; ?></td>
            <td><?php echo htmlspecialchars($post['username']); ?></td>
            <td><?php echo nl2br(htmlspecialchars(mb_strimwidth($post['message'], 0, 100, "..."))); ?></td>
            <td><?php echo date('d M Y H:i', strtotime($post['created_at'])); ?></td>
            <td class="actions">
                <button class="delete-btn delete-post-btn" data-post-id="<?php echo $post['id']; ?>">Sil</button>
            </td>
        </tr>
        <?php endforeach; else: ?>
            <tr><td colspan="5">Hiç gönderi bulunamadı.</td></tr>
        <?php endif; ?>
    </tbody>
</table>


<!-- YORUMLAR TABLOSU -->
<h3 id="comments">Tüm Yorumlar (<?php echo $all_comments ? count($all_comments) : 0; ?>)</h3>
<table border="1">
    <thead>
        <tr>
            <th>ID</th>
            <th>Gönderi ID</th>
            <th>Kullanıcı Adı</th>
            <th>Yorum</th>
            <th>Tarih</th>
            <th style="width: 80px;">İşlemler</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($all_comments): foreach($all_comments as $comment): ?>
        <tr id="comment-row-<?php echo $comment['id']; ?>">
            <td><?php echo $comment['id']; ?></td>
            <td><a href="#post-row-<?php echo $comment['post_id']; ?>"><?php echo $comment['post_id']; ?></a></td>
            <td><?php echo htmlspecialchars($comment['username']); ?></td>
            <td><?php echo nl2br(htmlspecialchars(mb_strimwidth($comment['message'], 0, 100, "..."))); ?></td>
            <td><?php echo date('d M Y H:i', strtotime($comment['created_at'])); ?></td>
            <td class="actions">
                <button class="delete-btn delete-comment-btn" data-comment-id="<?php echo $comment['id']; ?>">Sil</button>
            </td>
        </tr>
        <?php endforeach; else: ?>
            <tr><td colspan="6">Hiç yorum bulunamadı.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<?php include_once 'includes/footer.php'; ?>

<!-- ============================================= -->
<!-- YENİ JAVASCRIPT KODLARI BURADA -->
<!-- ============================================= -->
<script>
document.addEventListener('DOMContentLoaded', () => {

    // Gönderi Silme İşlemi
    document.querySelectorAll('.delete-post-btn').forEach(button => {
        button.addEventListener('click', function() {
            const postId = this.dataset.postId;
            
            if (confirm('Bu gönderiyi silmek istediğinizden emin misiniz? Bu işlem geri alınamaz ve gönderiye ait tüm yorumlar ve beğeniler de silinir.')) {
                const formData = new FormData();
                formData.append('action', 'delete_post_admin');
                formData.append('post_id', postId);

                fetch('api_handler.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Başarılı olursa, tablo satırını animasyonla kaldır
                        const row = document.getElementById('post-row-' + postId);
                        if(row) {
                           row.style.transition = 'opacity 0.5s ease';
                           row.style.opacity = '0';
                           setTimeout(() => row.remove(), 500);
                        }
                    } else {
                        alert('Hata: ' + data.error);
                    }
                });
            }
        });
    });

    // Yorum Silme İşlemi
    document.querySelectorAll('.delete-comment-btn').forEach(button => {
        button.addEventListener('click', function() {
            const commentId = this.dataset.commentId;
            
            if (confirm('Bu yorumu silmek istediğinizden emin misiniz? Bu işlem geri alınamaz.')) {
                const formData = new FormData();
                formData.append('action', 'delete_comment_admin');
                formData.append('comment_id', commentId);

                fetch('api_handler.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const row = document.getElementById('comment-row-' + commentId);
                         if(row) {
                           row.style.transition = 'opacity 0.5s ease';
                           row.style.opacity = '0';
                           setTimeout(() => row.remove(), 500);
                        }
                    } else {
                        alert('Hata: ' + data.error);
                    }
                });
            }
        });
    });

});
</script>