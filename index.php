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

// GİRİŞ FORMU İŞLEMLERİ
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_form'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = "E-posta ve şifre alanları zorunludur.";
    } else {
        $user_data = supabase_api_request('GET', 'users', ['email' => 'eq.' . $email]);
        if ($user_data && count($user_data) > 0) {
            $user = $user_data[0];
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['avatar_url'] = $user['avatar_url'];
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

<!-- ARKA PLAN GÖRSELİ VE LAYOUT -->
<div class="login-page">

    <!-- ÜST NAVİGASYON KARTI -->
    <div class="auth-header-wrapper">
        <nav class="auth-nav-card">
            <a href="index.php" class="auth-logo">PlantCare.com</a>
            <div class="auth-nav-links">
                <a href="#" class="auth-link" id="showLogin">Giriş Yap</a>
                <a href="#" class="auth-btn-register" id="showRegister">Kayıt Ol</a>
            </div>
        </nav>
    </div>

    <!-- KARŞILAMA METNİ (HERO) -->
    <div class="container">
        <div class="hero-container">
            <div class="hero-content">
                <h1>Bitkilerini hayatta tut.</h1>
                <p>Bitkileriniz için özel bakım programları, hatırlatıcılar, adım adım rehberler ve daha fazlası. PlantCare ile bitkilerinizi hayatta tutun!</p>
            </div>
        </div>
    </div>
</div>


<!-- POP-UP (MODAL) PENCERELERİ -->

<!-- GİRİŞ MODALI -->
<div class="auth-overlay <?php if (!empty($error) || !empty($success)) echo 'active'; ?>" id="loginModal">
    <div class="login-card">
        <span class="close-btn">&times;</span>
        <h2>Giriş Yap</h2>

        <?php if ($error): ?>
            <div class="message error"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="message success"><?php echo $success; ?></div>
        <?php endif; ?>

        <form action="index.php" method="POST">
            <input type="hidden" name="login_form" value="1">
            <label for="email">E-posta:</label>
            <input type="email" id="email" name="email" required>

            <label for="password">Şifre:</label>
            <input type="password" id="password" name="password" required>

            <button type="submit">Giriş Yap</button>
        </form>

        <p style="text-align:center; margin-top:12px;">
            Hesabınız yok mu?
            <a href="#" style="color:#27ae60; font-weight:bold;" id="switchToRegister">
                Kayıt olun
            </a>
        </p>
    </div>
</div>


<!-- KAYIT OL MODALI -->
<div class="auth-overlay" id="registerModal">
    <div class="register-card">
        <span class="close-btn">&times;</span>
        <h2>Kayıt Ol</h2>
        <p>Bitkilerinizi takip etmeye başlamak için bir hesap oluşturun.</p>
        
        <div id="register-error-container"></div>

        <form action="register.php" method="POST">
            <label for="username">Kullanıcı Adı:</label>
            <input type="text" id="username" name="username" required>

            <label for="email">E-posta:</label>
            <input type="email" id="email" name="email" required>

            <label for="password">Şifre:</label>
            <input type="password" id="password" name="password" required>

            <button type="submit">Kayıt Ol</button>
        </form>
         <p style="text-align:center; margin-top:12px;">
            Zaten bir hesabın var mı?
            <a href="#" style="color:#27ae60; font-weight:bold;" id="switchToLogin">
                Giriş yap
            </a>
        </p>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?>