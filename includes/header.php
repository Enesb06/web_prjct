<?php

include_once 'db.php';
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Bitki Bakım Takipçisi</title>
    <link rel="stylesheet" href="assets/style.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    
    
    <script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>
</head>
<body>
    
    
    <div class="ivy-overlay ivy-left"></div>
    <div class="ivy-overlay ivy-right"></div>

    <?php 
    
    if (!isset($is_landing_page)): 
    ?>
    
    <nav>
       
        <a href="dashboard.php" class="nav-logo">PlantCare.com</a>

        <ul>
            <?php if (isset($_SESSION['user_id'])): ?>
                <li><a href="dashboard.php">Ana Sayfa</a></li>
                <li><a href="add_plant.php">Bitki Ekle</a></li>
                <li><a href="encyclopedia.php">Bitki Ansiklopedisi</a></li> 
                <li><a href="forum.php">Forum</a></li>
                <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                    <li><a href="admin.php">Admin Paneli</a></li>
                <?php endif; ?>
                <li><a href="#" id="showProfileModal">Profilim</a></li> 
            <?php else: ?>
                <li><a href="index.php">Giriş Yap</a></li>
                <li><a href="register.php">Kayıt Ol</a></li>
            <?php endif; ?>
        </ul>
    </nav>
    <div class="container">

    <?php 
    endif; 
    ?>