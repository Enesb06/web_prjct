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
    <link rel="stylesheet" href="style.css">

</head>
<body>
    <header>
        <h1>Bitki Bakım Takipçisi</h1>
    </header>
    <nav>
        <ul>
            <?php if (isset($_SESSION['user_id'])): ?>
                <li><a href="dashboard.php">Ana Sayfa</a></li>
                <li><a href="add_plant.php">Bitki Ekle</a></li>
                <li><a href="forum.php">Forum</a></li>
                <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                    <li><a href="admin.php">Admin Paneli</a></li>
                <?php endif; ?>
                <li><a href="logout.php">Çıkış Yap</a></li>
            <?php else: ?>
                <li><a href="index.php">Giriş Yap</a></li>
                <li><a href="register.php">Kayıt Ol</a></li>
            <?php endif; ?>
        </ul>
    </nav>
    <div class="container">