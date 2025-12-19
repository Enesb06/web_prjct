<?php
include_once 'includes/header.php';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($username) || empty($email) || empty($password)) {
        $error = "Tüm alanlar zorunludur.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Geçersiz e-posta adresi.";
    } else {
        // Şifreyi hash'le
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Supabase'e kullanıcıyı ekle
        $newUser = [
            'username' => $username,
            'email' => $email,
            'password' => $hashed_password,
            'role' => 'user' // Varsayılan rol
        ];

        $result = supabase_api_request('POST', 'users', $newUser);

        if ($result !== null) {
            header('Location: index.php?status=registered');
            exit();
        } else {
            $error = "Bu kullanıcı adı veya e-posta zaten kullanılıyor olabilir.";
        }
    }
}
?>

<div class="register-page">

<!-- YENİ PLANTA STİLİ HEADER -->
    <div class="auth-header-wrapper">
        <nav class="auth-nav-card">
            <a href="index.php" class="auth-logo">PlantCare.com</a>
            
            <div class="auth-nav-links">
                <a href="index.php" class="auth-link">Giriş Yap</a>
                <a href="register.php" class="auth-btn-register">Kayıt Ol</a>
            </div>
        </nav>
    </div>

    <!-- Mevcut Form Kartın (Login Card / Register Card) -->

  <div class="register-card">

    <h2>Kayıt Ol</h2>
    <p>Bitkilerinizi takip etmeye başlamak için bir hesap oluşturun.</p>

    <?php if ($error): ?>
        <div class="message error"><?php echo $error; ?></div>
    <?php endif; ?>

    <form action="register.php" method="POST">
        <label for="username">Kullanıcı Adı:</label>
        <input type="text" id="username" name="username" required>

        <label for="email">E-posta:</label>
        <input type="email" id="email" name="email" required>

        <label for="password">Şifre:</label>
        <input type="password" id="password" name="password" required>

        <button type="submit">Kayıt Ol</button>
    </form>

  </div>
</div>


<?php include_once 'includes/footer.php'; ?>