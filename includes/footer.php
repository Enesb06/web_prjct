<?php 
   
    if (!isset($is_landing_page)): 
    ?>
</div>
    <?php 
    endif; 
    ?>


<div class="lottie-overlay" id="animation-overlay">
    
    <lottie-player 
        id="lottie-water-player" 
        class="lottie-animation"
        src="assets/animations/watering-animation.json"  
        background="transparent"  
        speed="1"  
        style="width: 250px; height: 250px; display: none;">
    </lottie-player>

    
    <lottie-player 
        id="lottie-fertilize-player" 
        class="lottie-animation"
        src="assets/animations/fertilizing-animation.json"  
        background="transparent"  
        speed="1"  
        style="width: 250px; height: 250px; display: none;">
    </lottie-player>
</div>

<script src="assets/script.js"></script>


<div class="auth-overlay" id="profileModal">
    <div class="profile-card">
        <span class="close-btn" id="closeProfileModal">&times;</span>

        
        <img src="assets/images/avatars/avatar1.png" alt="Profil Resmi" id="profile_avatar_display">
        
        <h2>Profil Yönetimi</h2>

        <!-- Profil Formu -->
        <form id="profileUpdateForm">
            <h4>Bilgileri Güncelle</h4>
            <div id="profile-message-container"></div> 
            <label for="profile_username">Kullanıcı Adı:</label>
            <input type="text" id="profile_username" name="username" required>
            
            
            <label for="profile_email">E-posta Adresi (Değiştirilemez):</label>
            <input type="email" id="profile_email" name="email" required disabled>
            
            <button type="submit">Bilgileri Kaydet</button>
        </form>

        <hr>

        
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

        
        <div class="logout-section" style="text-align: center;">
            <a href="logout.php" class="btn btn-danger" style="width: 100%;">Güvenli Çıkış Yap</a>
        </div>
    </div>
</div>


<div id="notification-container"></div>

<script>

function showNotification(message, type = 'info') {
    const container = document.getElementById('notification-container');
    
   
    const notification = document.createElement('div');
    notification.className = `notification ${type}`; 
    notification.innerHTML = message;
    
    
    container.appendChild(notification);
    

    setTimeout(() => {
        notification.classList.add('show');
    }, 100); 

   
    setTimeout(() => {
        notification.classList.remove('show');
        notification.classList.add('hide');
    }, 5000);
    
    
    setTimeout(() => {
        notification.remove();
    }, 5500);
}


<?php

if (isset($_SESSION['notification'])) {
    $notification = $_SESSION['notification'];
   
    echo "showNotification('" . addslashes($notification['message']) . "', '" . addslashes($notification['type']) . "');";
    
    unset($_SESSION['notification']);
}


if (isset($daily_notification_data)) {
     echo "showNotification('" . addslashes($daily_notification_data['message']) . "', '" . addslashes($daily_notification_data['type']) . "');";
}
?>

</script>

</body>
</html>