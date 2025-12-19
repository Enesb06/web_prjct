<?php
// db.php'yi dahil et, bu dosya oturumu zaten başlatıyor
include_once 'db.php';
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Bitki Bakım Takipçisi</title>
    <link rel="stylesheet" href="assets/style.css">
    
    <!-- ========================================================= -->
    <!-- YENİ EKLENEN LOTTIE PLAYER KÜTÜPHANESİ (ANİMASYON İÇİN GEREKLİ) -->
    <script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>
    <!-- ========================================================= -->

</head>
<body>
    <header>
        <h1>Bitki Bakım Takipçisi</h1>
    </header>
    <!-- Sarmaşık Efektleri -->
    <div class="ivy-overlay ivy-left"></div>
    <div class="ivy-overlay ivy-right"></div>
    <nav>
        <ul>
            <?php if (isset($_SESSION['user_id'])): ?>
                <li><a href="dashboard.php">Ana Sayfa</a></li>
                <li><a href="add_plant.php">Bitki Ekle</a></li>
                <li><a href="encyclopedia.php">Bitki Ansiklopedisi</a></li> 
                <li><a href="forum.php">Forum</a></li>
                <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                    <li><a href="admin.php">Admin Paneli</a></li>
                <?php endif; ?>
                <li><a href="logout.php" style="font-weight:bold;">Çıkış Yap</a></li>
            <?php else: ?>
                <!-- Not: Giriş/Kayıt butonlarının ID'lerini JS ile uyumlu hale getirdim -->
                <li><a href="#" id="showLogin">Giriş Yap</a></li>
                <li><a href="#" id="showRegister">Kayıt Ol</a></li>
            <?php endif; ?>
        </ul>
    </nav>
    <div class="container">