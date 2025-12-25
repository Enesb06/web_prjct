<?php
include_once 'includes/header.php';

// Giriş yapmış mı ve rolü admin mi diye kontrol et
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    echo "<div class='message error'>Bu sayfaya erişim yetkiniz yok.</div>";
    include_once 'includes/footer.php';
    exit();
}

// Tüm verileri çek
$all_users = supabase_api_request('GET', 'users', ['order' => 'created_at.desc']);
$all_posts = supabase_api_request('GET', 'forum_posts', ['order' => 'created_at.desc']);
$all_comments = supabase_api_request('GET', 'forum_comments', ['order' => 'created_at.desc']);
$all_plants = supabase_api_request('GET', 'plants', ['order' => 'id.asc']); // YENİ EKLENDİ
?>

<style>
    /* Admin paneli için genel stiller */
    h2, h3 { border-bottom: 2px solid #eee; padding-bottom: 10px; margin-top: 40px; }
    table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 0.9em; }
    th, td { padding: 12px 15px; border: 1px solid #ddd; text-align: left; vertical-align: middle; }
    th { background-color: #f4f4f4; }
    tr:nth-child(even) { background-color: #f9f9f9; }
    .actions { text-align: center; white-space: nowrap; }
    .actions button { margin: 0 4px; }
    
    /* Buton stilleri */
    .btn { border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer; color: white; }
    .delete-btn { background-color: #e74c3c; }
    .delete-btn:hover { background-color: #c0392b; }
    .edit-btn { background-color: #3498db; }
    .edit-btn:hover { background-color: #2980b9; }

    /* Modal (Pop-up) Stilleri - YENİ EKLENDİ */
    .modal-overlay {
        display: none; position: fixed; z-index: 1000;
        left: 0; top: 0; width: 100%; height: 100%;
        background-color: rgba(0,0,0,0.5);
        justify-content: center; align-items: center;
    }
    .modal-content {
        background-color: #fff; padding: 25px; border-radius: 5px;
        width: 90%; max-width: 400px; box-shadow: 0 5px 15px rgba(0,0,0,0.3);
    }
    .modal-content h4 { margin-top: 0; }
    .modal-content label { display: block; margin-top: 15px; margin-bottom: 5px; }
    .modal-content input[type="date"] { width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; }
    .modal-actions { text-align: right; margin-top: 20px; }
    .modal-actions .btn-save { background-color: #2ecc71; color: white; padding: 8px 15px; }
    .modal-actions .btn-cancel { background-color: #95a5a6; color: white; padding: 8px 15px; }
</style>

<h2>Admin Paneli</h2>
<p>Sistemdeki tüm verileri buradan yönetebilirsiniz.</p>

<!-- BİTKİLER TABLOSU  -->
<h3 id="plants">Tüm Bitkiler (<?php echo $all_plants ? count($all_plants) : 0; ?>)</h3>
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Bitki Adı</th>
            <th>Sahip ID</th>
            <th>Son Sulanma</th>
            <th>Son Gübrelenme</th>
            <th class="actions">İşlemler</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($all_plants): foreach($all_plants as $plant): ?>
        <tr id="plant-row-<?php echo $plant['id']; ?>">
            <td><?php echo $plant['id']; ?></td>
            <td class="plant-name"><?php echo htmlspecialchars($plant['plant_name']); ?></td>
            <td><?php echo $plant['user_id']; ?></td>

            
            <td class="last-watered">
                <?php echo !empty($plant['last_watered_date']) ? date('d M Y', strtotime($plant['last_watered_date'])) : 'Tarih Yok'; ?>
            </td>
            
            
            <td class="last-fertilized">
                <?php echo !empty($plant['last_fertilized_date']) ? date('d M Y', strtotime($plant['last_fertilized_date'])) : 'Tarih Yok'; ?>
            </td>

            <td class="actions">
                
                <button class="btn edit-btn" 
                        data-plant-id="<?php echo $plant['id']; ?>"
                        data-last-watered="<?php echo !empty($plant['last_watered_date']) ? date('Y-m-d', strtotime($plant['last_watered_date'])) : date('Y-m-d'); ?>"
                        data-last-fertilized="<?php echo !empty($plant['last_fertilized_date']) ? date('Y-m-d', strtotime($plant['last_fertilized_date'])) : date('Y-m-d'); ?>">
                    Düzenle
                </button>
                <button class="btn delete-btn delete-plant-btn" data-plant-id="<?php echo $plant['id']; ?>">Sil</button>
            </td>
        </tr>
        <?php endforeach; else: ?>
            <tr><td colspan="6">Hiç bitki bulunamadı.</td></tr>
        <?php endif; ?>
    </tbody>
</table>


<!-- KULLANICILAR TABLOSU -->
<h3>Kullanıcılar (<?php echo $all_users ? count($all_users) : 0; ?>)</h3>

<table>
    <thead><tr><th>ID</th><th>Kullanıcı Adı</th><th>Email</th><th>Rol</th><th>Kayıt Tarihi</th></tr></thead>
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

<table>
    <thead><tr><th>ID</th><th>Kullanıcı Adı</th><th>Mesaj</th><th>Tarih</th><th>İşlemler</th></tr></thead>
    <tbody>
        <?php if ($all_posts): foreach($all_posts as $post): ?>
        <tr id="post-row-<?php echo $post['id']; ?>">
            <td><?php echo $post['id']; ?></td><td><?php echo htmlspecialchars($post['username']); ?></td>
            <td><?php echo nl2br(htmlspecialchars(mb_strimwidth($post['message'], 0, 100, "..."))); ?></td>
            <td><?php echo date('d M Y H:i', strtotime($post['created_at'])); ?></td>
            <td class="actions"><button class="btn delete-btn delete-post-btn" data-post-id="<?php echo $post['id']; ?>">Sil</button></td>
        </tr>
        <?php endforeach; else: ?>
            <tr><td colspan="5">Hiç gönderi bulunamadı.</td></tr>
        <?php endif; ?>
    </tbody>
</table>


<!-- YORUMLAR TABLOSU -->
<h3 id="comments">Tüm Yorumlar (<?php echo $all_comments ? count($all_comments) : 0; ?>)</h3>

<table>
    <thead><tr><th>ID</th><th>Gönderi ID</th><th>Kullanıcı Adı</th><th>Yorum</th><th>Tarih</th><th>İşlemler</th></tr></thead>
    <tbody>
        <?php if ($all_comments): foreach($all_comments as $comment): ?>
        <tr id="comment-row-<?php echo $comment['id']; ?>">
            <td><?php echo $comment['id']; ?></td><td><a href="#post-row-<?php echo $comment['post_id']; ?>"><?php echo $comment['post_id']; ?></a></td>
            <td><?php echo htmlspecialchars($comment['username']); ?></td>
            <td><?php echo nl2br(htmlspecialchars(mb_strimwidth($comment['message'], 0, 100, "..."))); ?></td>
            <td><?php echo date('d M Y H:i', strtotime($comment['created_at'])); ?></td>
            <td class="actions"><button class="btn delete-btn delete-comment-btn" data-comment-id="<?php echo $comment['id']; ?>">Sil</button></td>
        </tr>
        <?php endforeach; else: ?>
            <tr><td colspan="6">Hiç yorum bulunamadı.</td></tr>
        <?php endif; ?>
    </tbody>
</table>


<!-- BİTKİ DÜZENLEME MODAL'I -->
<div id="editPlantModal" class="modal-overlay">
    <div class="modal-content">
        <h4>Bitki Bakım Tarihlerini Düzenle</h4>
        <form id="editPlantForm">
            <input type="hidden" id="edit_plant_id" name="plant_id">
            
            <label for="edit_last_watered">Son Sulanma Tarihi:</label>
            <input type="date" id="edit_last_watered" name="last_watered" required>
            
            <label for="edit_last_fertilized">Son Gübrelenme Tarihi:</label>
            <input type="date" id="edit_last_fertilized" name="last_fertilized" required>
            
            <div class="modal-actions">
                <button type="button" class="btn btn-cancel" id="cancelEditBtn">İptal</button>
                <button type="submit" class="btn btn-save">Değişiklikleri Kaydet</button>
            </div>
        </form>
    </div>
</div>


<?php include_once 'includes/footer.php'; ?>

<!-- ============================================= -->
<!-- JAVASCRIPT KODLARI -->
<!-- ============================================= -->
<script>
document.addEventListener('DOMContentLoaded', () => {

    // Mevcut silme fonksiyonları (post ve comment)
    function attachDeleteListener(selector, idAttribute, action) {
        document.querySelectorAll(selector).forEach(button => {
            button.addEventListener('click', function() {
                const id = this.getAttribute(idAttribute);
                const confirmMessage = action === 'delete_post_admin' 
                    ? 'Bu gönderiyi, tüm yorumları ve beğenileriyle birlikte silmek istediğinizden emin misiniz?'
                    : 'Bu öğeyi silmek istediğinizden emin misiniz?';

                if (confirm(confirmMessage)) {
                    const formData = new FormData();
                    formData.append('action', action);
                    const idKey = idAttribute.split('-')[1] + '_id'; // data-post-id -> post_id
                    formData.append(idKey, id);

                    fetch('api_handler.php', { method: 'POST', body: formData })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const row = button.closest('tr');
                            if (row) {
                                row.style.transition = 'opacity 0.5s ease';
                                row.style.opacity = '0';
                                setTimeout(() => row.remove(), 500);
                            }
                        } else {
                            alert('Hata: ' + (data.error || 'İşlem gerçekleştirilemedi.'));
                        }
                    });
                }
            });
        });
    }

    attachDeleteListener('.delete-post-btn', 'data-post-id', 'delete_post_admin');
    attachDeleteListener('.delete-comment-btn', 'data-comment-id', 'delete_comment_admin');
    
    // YENİ EKLENDİ - Bitki Silme İşlemi
    attachDeleteListener('.delete-plant-btn', 'data-plant-id', 'delete_plant_admin');


    // YENİ EKLENDİ - Bitki Düzenleme Modal İşlevleri
    const editModal = document.getElementById('editPlantModal');
    const editForm = document.getElementById('editPlantForm');
    const cancelEditBtn = document.getElementById('cancelEditBtn');
    const plantIdInput = document.getElementById('edit_plant_id');
    const wateredDateInput = document.getElementById('edit_last_watered');
    const fertilizedDateInput = document.getElementById('edit_last_fertilized');

    // "Düzenle" butonlarına tıklama olayı
    document.querySelectorAll('.edit-btn').forEach(button => {
        button.addEventListener('click', function() {
            // Butondaki verileri al
            const plantId = this.dataset.plantId;
            const lastWatered = this.dataset.lastWatered;
            const lastFertilized = this.dataset.lastFertilized;
            
            // Modal'daki form alanlarını doldur
            plantIdInput.value = plantId;
            wateredDateInput.value = lastWatered;
            fertilizedDateInput.value = lastFertilized;
            
            // Modal'ı göster
            editModal.style.display = 'flex';
        });
    });

    // Modal'ı kapatma fonksiyonu
    function closeModal() {
        editModal.style.display = 'none';
    }
    
    cancelEditBtn.addEventListener('click', closeModal);
    editModal.addEventListener('click', (e) => {
        if (e.target === editModal) { // Sadece dış arka plana tıklanırsa kapat
            closeModal();
        }
    });

    // Formu gönderme (Kaydetme) işlemi
    editForm.addEventListener('submit', function(e) {
        e.preventDefault(); // Formun normal gönderimini engelle
        
        const formData = new FormData(this);
        formData.append('action', 'update_plant_dates_admin');

        fetch('api_handler.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Arayüzdeki tablo satırını anında güncelle
                const updatedPlant = data.updated_plant;
                const row = document.getElementById('plant-row-' + updatedPlant.id);
                if (row) {
                    // Tarih formatını dd M YYYY'ye çevir
                    const options = { day: '2-digit', month: 'short', year: 'numeric' };
                    const watered = new Date(updatedPlant.last_watered + 'T00:00:00').toLocaleDateString('tr-TR', options);
                    const fertilized = new Date(updatedPlant.last_fertilized + 'T00:00:00').toLocaleDateString('tr-TR', options);

                    row.querySelector('.last-watered').textContent = watered;
                    row.querySelector('.last-fertilized').textContent = fertilized;
                    
                    // Butonların data-attributelarını da güncellemek iyi bir pratiktir
                    const editButton = row.querySelector('.edit-btn');
                    editButton.dataset.lastWatered = updatedPlant.last_watered;
                    editButton.dataset.lastFertilized = updatedPlant.last_fertilized;
                }
                closeModal(); // İşlem başarılıysa modal'ı kapat
            } else {
                alert('Hata: ' + (data.error || 'Güncelleme başarısız.'));
            }
        });
    });
});
</script>