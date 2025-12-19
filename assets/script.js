document.addEventListener('DOMContentLoaded', () => {

    console.log("Script dosyası başarıyla yüklendi.");

    // ===============================================
    //          MODAL (GİRİŞ/KAYIT) KODLARI
    // ===============================================
    const loginModal = document.getElementById('loginModal');
    const registerModal = document.getElementById('registerModal');
    const showLogin = document.getElementById('showLogin');
    const showRegister = document.getElementById('showRegister');
    const switchToRegister = document.getElementById('switchToRegister');
    const switchToLogin = document.getElementById('switchToLogin');
    const closeBtns = document.querySelectorAll('.close-btn');

    // GİRİŞ MODALINI AÇ
    if (showLogin && loginModal) {
        showLogin.addEventListener('click', (e) => {
            e.preventDefault();
            if(registerModal) registerModal.classList.remove('active');
            loginModal.classList.add('active');
        });
    }

    // KAYIT MODALINI AÇ
    if (showRegister && registerModal) {
        showRegister.addEventListener('click', (e) => {
            e.preventDefault();
            if(loginModal) loginModal.classList.remove('active');
            registerModal.classList.add('active');
        });
    }

    // KARTLAR ARASI GEÇİŞ
    if (switchToRegister && loginModal && registerModal) {
        switchToRegister.addEventListener('click', (e) => {
            e.preventDefault();
            loginModal.classList.remove('active');
            registerModal.classList.add('active');
        });
    }

    if (switchToLogin && loginModal && registerModal) {
        switchToLogin.addEventListener('click', (e) => {
            e.preventDefault();
            registerModal.classList.remove('active');
            loginModal.classList.add('active');
        });
    }

    // KAPATMA BUTONLARI
    closeBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            if(loginModal) loginModal.classList.remove('active');
            if(registerModal) registerModal.classList.remove('active');
        });
    });

    // DIŞARI TIKLAYINCA KAPAT
    window.addEventListener('click', (e) => {
        if (loginModal && e.target == loginModal) loginModal.classList.remove('active');
        if (registerModal && e.target == registerModal) registerModal.classList.remove('active');
    });


    // ===============================================
    //          ANSİKLOPEDİ ARAMA VE ODAKLANMA
    // ===============================================
    const searchInput = document.getElementById('encyclopedia-search');
    const plantCards = document.querySelectorAll('.encyclopedia-card');

    // Bu kodların sadece ansiklopedi sayfasında çalışmasını sağlar
    if (searchInput && plantCards.length > 0) {
        
        // Arama Çubuğu Fonksiyonu
        searchInput.addEventListener('input', (e) => {
            const searchTerm = e.target.value.toLowerCase().trim();
            plantCards.forEach(card => {
                const plantName = card.dataset.name;
                if (plantName.includes(searchTerm)) {
                    card.style.display = 'flex';
                } else {
                    card.style.display = 'none';
                }
            });
        });

        // Direkt Link ile Odaklanma Fonksiyonu
        const params = new URLSearchParams(window.location.search);
        const plantToFocus = params.get('plant');

        if (plantToFocus) {
            const targetCard = document.getElementById(plantToFocus);
            if (targetCard) {
                targetCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
                // Vurgu efekti ekle
                targetCard.classList.add('highlight');
                setTimeout(() => {
                    targetCard.classList.remove('highlight');
                }, 2500); // 2.5 saniye sonra vurguyu kaldır
            }
        }
    }
    
}); // DOMContentLoaded olay dinleyicisinin kapanışı


document.addEventListener('DOMContentLoaded', () => {
    const loginModal = document.getElementById('loginModal');
    const registerModal = document.getElementById('registerModal');
    const showLogin = document.getElementById('showLogin');
    const showRegister = document.getElementById('showRegister');
    const switchToRegister = document.getElementById('switchToRegister');
    const switchToLogin = document.getElementById('switchToLogin');
    const closeBtns = document.querySelectorAll('.close-btn');

    // GİRİŞ MODALINI AÇ (Üst menüdeki buton)
    if (showLogin && loginModal) {
        showLogin.addEventListener('click', (e) => {
            e.preventDefault();
            if(registerModal) registerModal.classList.remove('active');
            loginModal.classList.add('active');
        });
    }

    // KAYIT MODALINI AÇ (Üst menüdeki buton)
    if (showRegister && registerModal) {
        showRegister.addEventListener('click', (e) => {
            e.preventDefault();
            if(loginModal) loginModal.classList.remove('active');
            registerModal.classList.add('active');
        });
    }

    // MODALLAR ARASI GEÇİŞ (Giriş'ten Kayıt'a)
    if (switchToRegister && loginModal && registerModal) {
        switchToRegister.addEventListener('click', (e) => {
            e.preventDefault();
            loginModal.classList.remove('active');
            registerModal.classList.add('active');
        });
    }

    // MODALLAR ARASI GEÇİŞ (Kayıt'tan Giriş'e)
    if (switchToLogin && loginModal && registerModal) {
        switchToLogin.addEventListener('click', (e) => {
            e.preventDefault();
            registerModal.classList.remove('active');
            loginModal.classList.add('active');
        });
    }

    // KAPATMA BUTONLARI (Tüm modallar için)
    closeBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            if(loginModal) loginModal.classList.remove('active');
            if(registerModal) registerModal.classList.remove('active');
        });
    });

    // DIŞARI TIKLAYINCA KAPAT (Tüm modallar için)
    window.addEventListener('click', (e) => {
        if (loginModal && e.target == loginModal) loginModal.classList.remove('active');
        if (registerModal && e.target == registerModal) registerModal.classList.remove('active');
    });
});