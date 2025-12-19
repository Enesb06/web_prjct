document.addEventListener('DOMContentLoaded', () => {

    console.log("Script dosyası başarıyla yüklendi ve tüm modüller hazır.");

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

    if (showLogin && loginModal) {
        showLogin.addEventListener('click', (e) => {
            e.preventDefault();
            if(registerModal) registerModal.classList.remove('active');
            loginModal.classList.add('active');
        });
    }

    if (showRegister && registerModal) {
        showRegister.addEventListener('click', (e) => {
            e.preventDefault();
            if(loginModal) loginModal.classList.remove('active');
            registerModal.classList.add('active');
        });
    }

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

    closeBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            if(loginModal) loginModal.classList.remove('active');
            if(registerModal) registerModal.classList.remove('active');
        });
    });

    window.addEventListener('click', (e) => {
        if (loginModal && e.target == loginModal) loginModal.classList.remove('active');
        if (registerModal && e.target == registerModal) registerModal.classList.remove('active');
    });


    // ===============================================
    //          ANSİKLOPEDİ ARAMA VE ODAKLANMA
    // ===============================================
    const searchInput = document.getElementById('encyclopedia-search');
    const plantCards = document.querySelectorAll('.encyclopedia-card');

    if (searchInput && plantCards.length > 0) {
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

        const params = new URLSearchParams(window.location.search);
        const plantToFocus = params.get('plant');

        if (plantToFocus) {
            const targetCard = document.getElementById(plantToFocus);
            if (targetCard) {
                targetCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
                targetCard.classList.add('highlight');
                setTimeout(() => {
                    targetCard.classList.remove('highlight');
                }, 2500);
            }
        }
    }


    // =========================================================
    //          SUNUM İÇİN GERİ SAYIM VE BUTON KİLİTLEME (VERİ TABANLI)
    // =========================================================
    const manageCard = document.querySelector('.plant-manage-card');

    if (manageCard) {
        const plantId = manageCard.dataset.plantId;
        const cooldownMinutes = parseFloat(manageCard.dataset.cooldownMinutes);
        
        if (cooldownMinutes && cooldownMinutes > 0) {
            const waterButton = document.getElementById('water-button');
            const fertilizeButton = document.getElementById('fertilize-button');
            const cooldownMs = cooldownMinutes * 60 * 1000;

            const checkAndApplyCooldown = (button, actionType) => {
                if (!button) return;
                const originalText = button.innerHTML;
                const storageKey = `plant_${plantId}_${actionType}_lockout`;
                const lockoutTime = localStorage.getItem(storageKey);

                if (lockoutTime) {
                    const timePassed = new Date().getTime() - lockoutTime;
                    if (timePassed < cooldownMs) {
                        button.disabled = true;
                        const interval = setInterval(() => {
                            const secondsLeft = Math.ceil((cooldownMs - (new Date().getTime() - lockoutTime)) / 1000);
                            if (secondsLeft > 0) {
                                button.innerHTML = `Bekleyin (${secondsLeft}s)`;
                            } else {
                                clearInterval(interval);
                                button.disabled = false;
                                button.innerHTML = originalText;
                                localStorage.removeItem(storageKey);
                            }
                        }, 1000);
                    }
                }
            };

            checkAndApplyCooldown(waterButton, 'water');
            checkAndApplyCooldown(fertilizeButton, 'fertilize');
        }
    }

    // =========================================================
    //          BAKIM İŞLEMİ SONRASI LOTTIE ANİMASYONU
    // =========================================================
    const manageForm = document.querySelector('.plant-manage-actions form');
    const animationOverlay = document.getElementById('animation-overlay');
    const waterPlayer = document.getElementById('lottie-water-player');
    const fertilizePlayer = document.getElementById('lottie-fertilize-player');
    const allAnimations = document.querySelectorAll('.lottie-animation');

    if (manageForm && animationOverlay && waterPlayer && fertilizePlayer) {
        manageForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const action = document.activeElement.value;
            let activePlayer = null;

            allAnimations.forEach(anim => anim.style.display = 'none');

            if (action === 'water') {
                activePlayer = waterPlayer;
            } else if (action === 'fertilize') {
                activePlayer = fertilizePlayer;
            }

            if (activePlayer) {
                activePlayer.style.display = 'block';
                animationOverlay.classList.add('active');
                activePlayer.seek(0);
                activePlayer.play();

                const animationDuration = 2000; // Animasyon süresi (ms)

                // Geri sayım mantığını Lottie animasyonuna entegre et
                const plantId = manageCard.dataset.plantId;
                const cooldownMinutes = parseFloat(manageCard.dataset.cooldownMinutes);
                if (cooldownMinutes && cooldownMinutes > 0) {
                    if (action === 'water') {
                        localStorage.setItem(`plant_${plantId}_water_lockout`, new Date().getTime());
                    } else if (action === 'fertilize') {
                        localStorage.setItem(`plant_${plantId}_fertilize_lockout`, new Date().getTime());
                    }
                }

                setTimeout(() => {
                    e.target.submit();
                }, animationDuration);
            } else {
                e.target.submit();
            }
        });
    }

}); // --- TEK BİR DOMContentLoaded OLAY DİNLEYİCİSİNİN KAPANIŞI ---