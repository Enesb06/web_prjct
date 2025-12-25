<?php
include_once 'includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Güncelleme işlemi tetiklendiyse
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $new_username = trim($_POST['username']);
    $new_email = trim($_POST['email']);
    
    if (empty($new_username) || empty($new_email)) {
        $error = "Alanlar boş bırakılamaz.";
    } else {
        $updateData = [
            'username' => $new_username,
            'email' => $new_email
        ];
        
        // PATCH isteği atarken URL'ye filtre eklemeliyiz
        $path = 'users?id=eq.' . $user_id;
        $result = supabase_api_request('PATCH', $path, $updateData);

        if ($result !== null) {
            $_SESSION['username'] = $new_username; // Session'ı güncelle
            $success = "Profil bilgilerin başarıyla güncellendi!";
        } else {
            $error = "Güncelleme sırasında bir hata oluştu.";
        }
    }
}

// Mevcut kullanıcı bilgilerini çek
$user_info = supabase_api_request('GET', 'users', ['id' => 'eq.' . $user_id]);

// Kullanıcı bulunamazsa veya bir hata oluşursa işlemi durdur
if (!$user_info || count($user_info) === 0) {
    echo "<div class='message error'>Kullanıcı bilgileri alınamadı. Lütfen daha sonra tekrar deneyin.</div>";
    include_once 'includes/footer.php';
    exit();
}
$user = $user_info[0];
?>

<div class="profile-container" style="max-width: 500px; margin: 40px auto; padding: 20px; background: #fff; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
    <h2 style="text-align: center; color: #1b4332;">Profil Bilgileri</h2>
    <p style="text-align: center; color: #666; margin-bottom: 25px;">Bilgilerini buradan güncelleyebilirsin.</p>

    <?php if ($error): ?>
        <div class="message error"><?php echo $error; ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="message success"><?php echo $success; ?></div>
    <?php endif; ?>

    <form action="profile.php" method="POST" style="display: flex; flex-direction: column; gap: 15px;">
        <div class="form-group">
            <label>Kullanıcı Adı:</label>
            <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
        </div>

        <div class="form-group">
            <label>E-posta Adresi:</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
        </div>

        <div class="form-group">
            <label>Hesap Tipi:</label>
            <input type="text" value="<?php echo ucfirst($user['role']); ?>" disabled style="background: #f4f4f4; cursor: not-allowed;">
        </div>

        <button type="submit" name="update_profile" style="background-color: #27ae60;">Bilgileri Güncelle</button>
    </form>

    <hr style="margin: 30px 0; border: 0; border-top: 1px solid #eee;">

    <div class="logout-section" style="text-align: center;">
        <p style="color: #999; font-size: 0.9em;">Oturumu kapatmak mı istiyorsun?</p>
        <a href="logout.php" class="btn btn-danger" style="display: inline-block; width: 100%; padding: 12px; text-decoration: none; font-weight: bold;">Güvenli Çıkış Yap</a>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?>