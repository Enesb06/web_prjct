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
        
        if (!cooldownMinutes || cooldownMinutes <= 0) {
            // Cooldown süresi olmayan normal bitkiler için bu blok boş kalır ve kod çalışmaz.
        } else {
            // Cooldown süresi olan özel sunum bitkileri için bu blok çalışır.
            const waterButton = document.getElementById('water-button');
            const fertilizeButton = document.getElementById('fertilize-button');
            const cooldownMs = cooldownMinutes * 60 * 1000;

            const checkAndApplyCooldown = (button, actionType) => {
                if (!button) return; // Buton yoksa devam etme
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

            const form = waterButton.closest('form');
            form.addEventListener('submit', () => {
                const action = document.activeElement.value;
                if (action === 'water') {
                    localStorage.setItem(`plant_${plantId}_water_lockout`, new Date().getTime());
                } else if (action === 'fertilize') {
                    localStorage.setItem(`plant_${plantId}_fertilize_lockout`, new Date().getTime());
                }
            });
        }
    }

}); // --- TEK BİR DOMContentLoaded OLAY DİNLEYİCİSİNİN KAPANIŞI ---