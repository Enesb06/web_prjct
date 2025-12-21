<!-- YENİ LOTTIE ANİMASYON KATMANI (İKİ ANİMASYONLU) -->
<div class="lottie-overlay" id="animation-overlay">
    <!-- Sulama Animasyonu (Başlangıçta gizli) -->
    <lottie-player 
        id="lottie-water-player" 
        class="lottie-animation"
        src="assets/animations/watering-animation.json"  
        background="transparent"  
        speed="1"  
        style="width: 250px; height: 250px; display: none;">
    </lottie-player>

    <!-- Gübreleme Animasyonu (Başlangıçta gizli) -->
    <lottie-player 
        id="lottie-fertilize-player" 
        class="lottie-animation"
        src="assets/animations/fertilizing-animation.json"  
        background="transparent"  
        speed="1"  
        style="width: 250px; height: 250px; display: none;">
    </lottie-player>
</div>


</div> <!-- .container kapanışı -->
    <script src="assets/script.js"></script>

<!-- =================================================================== -->
<!--                  PROFİL MODAL PENCERESİ                             -->
<!-- =================================================================== -->
<div class="auth-overlay" id="profileModal">
    <div class="profile-card">
        <span class="close-btn" id="closeProfileModal">&times;</span>

        <!-- YENİ: Mevcut Avatar Gösterim Alanı -->
        <img src="assets/images/avatars/avatar1.png" alt="Profil Resmi" id="profile_avatar_display">
        
        <h2>Profil Yönetimi</h2>

        <!-- Profil Formu -->
        <form id="profileUpdateForm">
            <h4>Bilgileri Güncelle</h4>
            <div id="profile-message-container"></div> <!-- Hata/başarı mesajları için -->
            <label for="profile_username">Kullanıcı Adı:</label>
            <input type="text" id="profile_username" name="username" required>
            
            <!-- DEĞİŞİKLİK: E-posta alanı artık "disabled" -->
            <label for="profile_email">E-posta Adresi (Değiştirilemez):</label>
            <input type="email" id="profile_email" name="email" required disabled>
            
            <button type="submit">Bilgileri Kaydet</button>
        </form>

        <hr>

        <!-- Şifre Değiştirme Formu (Aynı kalıyor) -->
        <form id="passwordUpdateForm">
            <h4>Şifre Değiştir</h4>
            <div id="password-message-container"></div>
            <label for="current_password">Mevcut Şifre:</label>
            <input type="password" id="current_password" name="current_password" required>
            <label for="new_password">Yeni Şifre:</label>
            <input type="password" id="new_password" name="new_password" required>
            <button type="submit">Şifreyi Değiştir</button>
        </form>

        <hr>

        <!-- Avatar Seçimi (Aynı kalıyor) -->
        <div class="avatar-selection">
             <h4>Avatar Değiştir</h4>
             <div class="avatar-options">
                <img src="assets/images/avatars/avatar1.png" data-avatar="avatar1.png" class="avatar-option" alt="Avatar 1">
                <img src="assets/images/avatars/avatar2.png" data-avatar="avatar2.png" class="avatar-option" alt="Avatar 2">
                <img src="assets/images/avatars/avatar3.png" data-avatar="avatar3.png" class="avatar-option" alt="Avatar 3">
                <img src="assets/images/avatars/avatar4.png" data-avatar="avatar4.png" class="avatar-option" alt="Avatar 4">
                <img src="assets/images/avatars/avatar5.png" data-avatar="avatar5.png" class="avatar-option" alt="Avatar 5">
             </div>
        </div>

        <hr>

        <!-- Çıkış Yap Butonu (Aynı kalıyor) -->
        <div class="logout-section" style="text-align: center;">
            <a href="logout.php" class="btn btn-danger" style="width: 100%;">Güvenli Çıkış Yap</a>
        </div>
    </div>
</div>

<!-- =================================================================== -->
<!--                  BİLDİRİM SİSTEMİ İÇİN GEREKLİ KODLAR               -->
<!-- =================================================================== -->

<!-- Bildirimlerin Ekleneceği Alan -->
<div id="notification-container"></div>

<script>
/**
 * Ekranda bir bildirim gösterir.
 * @param {string} message Gösterilecek mesaj.
 * @param {string} type Bildirim türü ('success', 'info', 'error').
 */
function showNotification(message, type = 'info') {
    const container = document.getElementById('notification-container');
    
    // Yeni bildirim elementini oluştur
    const notification = document.createElement('div');
    notification.className = `notification ${type}`; // Örn: 'notification success'
    notification.innerHTML = message;
    
    // Ekrana ekle
    container.appendChild(notification);
    
    // Görünür yap (animasyon için)
    setTimeout(() => {
        notification.classList.add('show');
    }, 100); // Küçük bir gecikme animasyonun düzgün çalışmasını sağlar

    // 5 saniye sonra gizle
    setTimeout(() => {
        notification.classList.remove('show');
        notification.classList.add('hide');
    }, 5000);
    
    // Animasyon bittikten sonra DOM'dan tamamen kaldır
    setTimeout(() => {
        notification.remove();
    }, 5500);
}

// =================================================================== //
//          PHP'DEN GELEN BİLDİRİMLERİ OTOMATİK TETİKLEME              //
// =================================================================== //
<?php
// Session'da bir bildirim ayarlanmışsa, onu göster ve sonra temizle.
// Bu, sayfalar arası yönlendirmelerde mesaj taşımak için kullanılır.
if (isset($_SESSION['notification'])) {
    $notification = $_SESSION['notification'];
    // JavaScript kodunu echo ile basıyoruz
    echo "showNotification('" . addslashes($notification['message']) . "', '" . addslashes($notification['type']) . "');";
    // Bildirimi gösterdikten sonra session'dan siliyoruz ki tekrar görünmesin.
    unset($_SESSION['notification']);
}

// Sadece dashboard.php'de hesaplanan günlük karşılama bildirimini göster
if (isset($daily_notification_data)) {
     echo "showNotification('" . addslashes($daily_notification_data['message']) . "', '" . addslashes($daily_notification_data['type']) . "');";
}
?>

</script>

</body>
</html>