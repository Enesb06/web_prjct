<?php
include_once 'includes/header.php';
$error = '';
$success = '';

// Eğer kullanıcı zaten giriş yapmışsa dashboard'a yönlendir
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}

// Kayıt sonrası gelen mesaj
if (isset($_GET['status']) && $_GET['status'] === 'registered') {
    $success = "Başarıyla kayıt oldunuz! Lütfen giriş yapın.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = "E-posta ve şifre alanları zorunludur.";
    } else {
        // Kullanıcıyı e-posta adresine göre ara
        $user_data = supabase_api_request('GET', 'users', ['email' => 'eq.' . $email]);

        if ($user_data && count($user_data) > 0) {
            $user = $user_data[0];
            // Şifreleri doğrula
            if (password_verify($password, $user['password'])) {
                // Oturum bilgilerini ayarla
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_role'] = $user['role'];
                
                header('Location: dashboard.php');
                exit();
            } else {
                $error = "Geçersiz şifre.";
            }
        } else {
            $error = "Bu e-posta adresine sahip bir kullanıcı bulunamadı.";
        }
    }
}
?>

<div class="login-page">
  <div class="login-card">

    <h2>Giriş Yap</h2>

    <?php if ($error): ?>
        <div class="message error"><?php echo $error; ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="message success"><?php echo $success; ?></div>
    <?php endif; ?>

    <form action="index.php" method="POST">
        <label for="email">E-posta:</label>
        <input type="email" id="email" name="email" required>

        <label for="password">Şifre:</label>
        <input type="password" id="password" name="password" required>

        <button type="submit">Giriş Yap</button>
    </form>

    <p style="text-align:center; margin-top:12px;">
        Hesabınız yok mu?
        <a href="register.php" style="color:#27ae60; font-weight:bold;">
            Kayıt olun
        </a>
    </p>

  </div>
</div>


<?php include_once 'includes/footer.php'; ?>