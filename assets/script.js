document.addEventListener('DOMContentLoaded', () => {
    const loginModal = document.getElementById('loginModal');
    const registerModal = document.getElementById('registerModal');
    const showLogin = document.getElementById('showLogin');
    const showRegister = document.getElementById('showRegister');
    const switchToRegister = document.getElementById('switchToRegister');
    const switchToLogin = document.getElementById('switchToLogin');
    const closeBtns = document.querySelectorAll('.close-btn');

    console.log("Script yüklendi...");

    // GİRİŞ MODALINI AÇ
    if (showLogin && loginModal) {
        showLogin.addEventListener('click', (e) => {
            e.preventDefault();
            console.log("Giriş açılıyor");
            if(registerModal) registerModal.classList.remove('active');
            loginModal.classList.add('active');
        });
    }

    // KAYIT MODALINI AÇ
    if (showRegister && registerModal) {
        showRegister.addEventListener('click', (e) => {
            e.preventDefault();
            console.log("Kayıt açılıyor");
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
});