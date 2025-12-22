<?php
$is_landing_page = true; 

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

<!-- ========================================================= -->
<!--        YENİ EKLENEN SWIPER CSS LİNKİ (HEAD İÇİNE)         -->
<!-- ========================================================= -->
<link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css" />


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

<main>
    <!-- ÖZELLİKLER BÖLÜMÜ -->
    <section class="features-section">
        <div class="container">
            <h2>Neden PlantCare?</h2>
            <div class="features-grid">
                <!-- 1. KART -->
                <div class="feature-card">
                    <i class="fas fa-calendar-alt"></i>
                    <h3>Akıllı Takvim</h3>
                    <p>Bitkilerinin sulama ve gübreleme zamanlarını senin için takip eder, asla unutmamanı sağlar.</p>
                </div>
                
                <!-- 2. KART -->
                <div class="feature-card">
                    <i class="fas fa-tachometer-alt"></i>
                    <h3>Kişisel Pano</h3>
                    <p>Ana sayfan, bitkilerinin anlık durumunu gösteren komuta merkezin olsun. Bugün ne yapman gerektiğini anında gör.</p>
                </div>

                <!-- 3. KART -->
                <div class="feature-card">
                    <i class="fas fa-book-open"></i>
                    <h3>Geniş Ansiklopedi</h3>
                    <p>Yüzlerce bitki hakkında detaylı bakım bilgilerine, ipuçlarına ve daha fazlasına anında ulaşın.</p>
                </div>
                
                <!-- 4. KART -->
                <div class="feature-card">
                    <i class="fas fa-hand-pointer"></i>
                    <h3>Tek Tıkla Bakım</h3>
                    <p>Bitkilerinin bakımını yaptın mı? Sulama ve gübreleme işlemlerini tek bir dokunuşla kaydet, biz takibini yapalım.</p>
                </div>

                <!-- 5. KART -->
                <div class="feature-card">
                    <i class="fas fa-users"></i>
                    <h3>Aktif Topluluk</h3>
                    <p>Diğer bitki severlerle forumda buluşun, tecrübelerini paylaşın ve sorularınıza yanıt bulun.</p>
                </div>

                <!-- 6. KART -->
                <div class="feature-card">
                    <i class="fas fa-user-cog"></i>
                    <h3>Özelleştirilebilir Profil</h3>
                    <p>Kendi avatarını seç, profilini kişiselleştir ve toplulukta kendini en iyi şekilde yansıt.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- =============================================== -->
    <!--            YENİ NASIL ÇALIŞIR? BÖLÜMÜ           -->
    <!-- =============================================== -->
    <section class="how-it-works-section">
        <div class="container">
            <h2>Sadece 3 Adımda Başla</h2>
            <div class="steps-container">
                <!-- Adım 1 -->
                <div class="step-card">
                    <div class="step-number">1</div>
                    <i class="fas fa-user-plus"></i>
                    <h3>Hesabını Oluştur</h3>
                    <p>Ücretsiz bir hesap oluşturarak bitki bakım dünyasına ilk adımını at.</p>
                </div>
                <!-- Adım 2 -->
                <div class="step-card">
                    <div class="step-number">2</div>
                    <i class="fas fa-leaf"></i>
                    <h3>Bitkilerini Ekle</h3>
                    <p>Ansiklopedimizden seçerek veya manuel olarak bitkilerini profiline ekle.</p>
                </div>
                <!-- Adım 3 -->
                <div class="step-card">
                    <div class="step-number">3</div>
                    <i class="fas fa-seedling"></i>
                    <h3>Keyfini Çıkar!</h3>
                    <p>Biz sana bakım zamanlarını hatırlatalım, sen bitkilerinin keyifle büyümesini izle.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- KULLANICI YORUMLARI BÖLÜMÜ -->
    <section class="testimonials-section">
        <div class="container">
            <h2>Mutlu Kullanıcılarımızdan</h2>
            <div class="swiper-container testimonial-slider">
                <div class="swiper-wrapper">
                    <!-- Yorum 1 -->
                    <div class="swiper-slide">
                        <div class="testimonial-card">
                            <p class="quote">"PlantCare sayesinde artık bitkilerimi unutmuyorum! Özellikle sulama takvimi hayat kurtarıcı. Orkide'm hiç bu kadar sağlıklı olmamıştı."</p>
                            <div class="user-info">
                                <img src="assets/images/avatars/avatar2.png" alt="Kullanıcı Avatarı">
                                <div>
                                    <strong>Ayşe Y.</strong>
                                    <span>Orkide Sahibi</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Yorum 2 -->
                    <div class="swiper-slide">
                        <div class="testimonial-card">
                            <p class="quote">"Forumu harika! Deve Tabanı'mda sarı lekeler oluşmuştu, ne yapacağımı bilemiyordum. Topluluktaki tecrübeli üyeler sayesinde bitkimi kurtardım."</p>
                            <div class="user-info">
                                <img src="assets/images/avatars/avatar4.png" alt="Kullanıcı Avatarı">
                                <div>
                                    <strong>Mehmet K.</strong>
                                    <span>Deve Tabanı Meraklısı</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Yorum 3 -->
                    <div class="swiper-slide">
                        <div class="testimonial-card">
                            <p class="quote">"Uygulamanın sadeliğine bayıldım. Karmaşık menüler yok, her şey elimin altında. Yeni başlayanlar için kesinlikle tavsiye ederim."</p>
                            <div class="user-info">
                                <img src="assets/images/avatars/avatar3.png" alt="Kullanıcı Avatarı">
                                <div>
                                    <strong>Elif S.</strong>
                                    <span>Yeni Başlayan</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Yorum 4 -->
                    <div class="swiper-slide">
                        <div class="testimonial-card">
                            <p class="quote">"Ansiklopedi özelliği çok faydalı. Hediye gelen bitkinin ne olduğunu ve nasıl bakılacağını anında öğrendim. Teşekkürler PlantCare!"</p>
                            <div class="user-info">
                                <img src="assets/images/avatars/avatar5.png" alt="Kullanıcı Avatarı">
                                <div>
                                    <strong>Zeynep A.</strong>
                                    <span>Kalanşo Bakıcısı</span>
                                </div>
                            </div>
                        </div>
                    </div>
                           <div class="swiper-slide">
                        <div class="testimonial-card">
                            <p class="quote">“Bitkim neden soluyor diye günlerce araştırıyordum. PlantCare’de birebir aynı sorunu yaşayanları görünce çözümü hemen buldum.”</p>
                            <div class="user-info">
                                <img src="assets/images/avatars/avatar5.png" alt="Kullanıcı Avatarı">
                                <div>
                                    <strong>Can B.</strong>
                                    <span>Ev Bitkileri Meraklısı 🌿</span>
                                </div>
                            </div>
                        </div>
                    </div>
                     <div class="swiper-slide">
                        <div class="testimonial-card">
                            <p class="quote">“Yeni taşındım ve tüm bitkilerim strese girmişti. Sulama ve ışık önerileri gerçekten nokta atışı.”</p>
                            <div class="user-info">
                                <img src="assets/images/avatars/avatar3.png" alt="Kullanıcı Avatarı">
                                <div>
                                    <strong>Ahmet D.</strong>
                                    <span>Bitki Koleksiyoncusu 🌵</span>
                                </div>
                            </div>
                        </div>
                    </div>
                     <div class="swiper-slide">
                        <div class="testimonial-card">
                            <p class="quote">“Hediye gelen bitkinin adını bile bilmiyordum. Şimdi türünü, bakımını ve hatta çoğaltmayı öğrendim.”</p>
                            <div class="user-info">
                                <img src="assets/images/avatars/avatar3.png" alt="Kullanıcı Avatarı">
                                <div>
                                    <strong>Burcu Y.</strong>
                                    <span>Yeni Başlayan</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="swiper-slide">
    <div class="testimonial-card">
        <p class="quote">“Bitkimdeki sararmanın nedenini forumda öğrendim. Küçük bir dokunuşla tamamen toparlandı.”</p>
        <div class="user-info">
            <img src="assets/images/avatars/avatar1.png" alt="Kullanıcı Avatarı">
            <div>
                <strong>Mehmet K.</strong>
                <span>Bitki Meraklısı</span>
            </div>
        </div>
    </div>
</div>

<div class="swiper-slide">
    <div class="testimonial-card">
        <p class="quote">“Uygulama çok sade ve anlaşılır. Bitki bakımına yeni başlayanlar için birebir.”</p>
        <div class="user-info">
            <img src="assets/images/avatars/avatar2.png" alt="Kullanıcı Avatarı">
            <div>
                <strong>Elif S.</strong>
                <span>Yeni Başlayan</span>
            </div>
        </div>
    </div>
</div>

<div class="swiper-slide">
    <div class="testimonial-card">
        <p class="quote">“Sulama hatası yaptığımı fark etmemi sağladı. Takvim özelliği gerçekten hayat kurtarıyor.”</p>
        <div class="user-info">
            <img src="assets/images/avatars/avatar3.png" alt="Kullanıcı Avatarı">
            <div>
                <strong>Can B.</strong>
                <span>Kaktüs Bakıcısı</span>
            </div>
        </div>
    </div>
</div>

<div class="swiper-slide">
    <div class="testimonial-card">
        <p class="quote">“Fotoğraf yükleyip sorumu sordum, kısa sürede çok faydalı cevaplar aldım.”</p>
        <div class="user-info">
            <img src="assets/images/avatars/avatar2.png" alt="Kullanıcı Avatarı">
            <div>
                <strong>Ayşe Y.</strong>
                <span>Ev Bitkileri Sahibi</span>
            </div>
        </div>
    </div>
</div>

<div class="swiper-slide">
    <div class="testimonial-card">
        <p class="quote">“Hediye gelen bitkinin adını bile bilmiyordum. Şimdi bakımını gönül rahatlığıyla yapıyorum.”</p>
        <div class="user-info">
            <img src="assets/images/avatars/avatar1.png" alt="Kullanıcı Avatarı">
            <div>
                <strong>Burcu Y.</strong>
                <span>Yeni Başlayan</span>
            </div>
        </div>
    </div>
</div>

<div class="swiper-slide">
    <div class="testimonial-card">
        <p class="quote">“Topluluk kısmı çok aktif. Her soruma mutlaka biri yardımcı oldu.”</p>
        <div class="user-info">
            <img src="assets/images/avatars/avatar4.png" alt="Kullanıcı Avatarı">
            <div>
                <strong>Emre C.</strong>
                <span>Bitki Koleksiyoncusu</span>
            </div>
        </div>
    </div>
</div>

<div class="swiper-slide">
    <div class="testimonial-card">
        <p class="quote">“Orkidelerim hiç bu kadar sağlıklı görünmemişti. Öneriler gerçekten işe yarıyor.”</p>
        <div class="user-info">
            <img src="assets/images/avatars/avatar1.png" alt="Kullanıcı Avatarı">
            <div>
                <strong>Selma T.</strong>
                <span>Orkide Sever</span>
            </div>
        </div>
    </div>
</div>

<div class="swiper-slide">
    <div class="testimonial-card">
        <p class="quote">“Bitki türlerini öğrenmek ve doğru bakım yapmak artık çok daha kolay.”</p>
        <div class="user-info">
            <img src="assets/images/avatars/avatar3.png" alt="Kullanıcı Avatarı">
            <div>
                <strong>Okan A.</strong>
                <span>Ev Bitkileri Kullanıcısı</span>
            </div>
        </div>
    </div>
</div>

<div class="swiper-slide">
    <div class="testimonial-card">
        <p class="quote">“Basit arayüzü sayesinde annem bile rahatça kullanabiliyor.”</p>
        <div class="user-info">
            <img src="assets/images/avatars/avatar2.png" alt="Kullanıcı Avatarı">
            <div>
                <strong>Gizem D.</strong>
                <span>Kullanıcı</span>
            </div>
        </div>
    </div>
</div>

<div class="swiper-slide">
    <div class="testimonial-card">
        <p class="quote">“Bitkilerim artık ne zaman su ister biliyorum. Unutma derdi tamamen bitti.”</p>
        <div class="user-info">
            <img src="assets/images/avatars/avatar1.png" alt="Kullanıcı Avatarı">
            <div>
                <strong>Murat E.</strong>
                <span>Yoğun Çalışan</span>
            </div>
        </div>
    </div>
</div>


                    

                </div>
                <div class="swiper-pagination"></div>
            </div>
        </div>
    </section>

<!-- =============================================== -->
    <!--            YENİ SON ÇAĞRI BÖLÜMÜ                -->
    <!-- =============================================== -->
    <section class="final-cta-section">
        <div class="container">
            <h2>Yeşil Macerana Bugün Başla!</h2>
            <p>Binlerce mutlu bitki sahibi arasına katıl. Ücretsiz hesabını şimdi oluştur.</p>
            <a href="#" class="auth-btn-register" id="showRegisterFooter">Hemen Kayıt Ol</a>
        </div>
    </section>

    <!-- =============================================== -->
    <!--                 YENİ FOOTER                     -->
    <!-- =============================================== -->
    <footer class="site-footer">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> PlantCare.com. Tüm Hakları Saklıdır.</p>
        </div>
    </footer>
</main>


<!-- POP-UP (MODAL) PENCERELERİ -->
<!-- GİRİŞ MODALI -->
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


<!-- ========================================================= -->
<!--      DEĞİŞİKLİK: SCRIPTLER FOOTER'DAN ÖNCEYE TAŞINDI      -->
<!-- ========================================================= -->
<script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const testimonialSwiper = new Swiper('.testimonial-slider', {
        loop: true,
        autoplay: {
            delay: 4000,
            disableOnInteraction: false,
        },
        slidesPerView: 1,
        spaceBetween: 30,
        breakpoints: {
            768: {
                slidesPerView: 2,
            },
            1024: {
                slidesPerView: 3,
            }
        },
        pagination: {
            el: '.swiper-pagination',
            clickable: true,
        },
    });
});
</script>

<?php include_once 'includes/footer.php'; ?>