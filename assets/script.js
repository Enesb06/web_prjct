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


    // ===============================================
//          PROFİL MODAL KODLARI
// ===============================================
const profileModal = document.getElementById('profileModal');
const showProfileModalBtn = document.getElementById('showProfileModal');
const closeProfileModalBtn = document.getElementById('closeProfileModal');

// Avatar seçimi için değişkenler
let selectedAvatar = '';
const avatarOptions = document.querySelectorAll('.avatar-option');

// Formlar
const profileUpdateForm = document.getElementById('profileUpdateForm');
const passwordUpdateForm = document.getElementById('passwordUpdateForm');

// Mesaj konteynerleri
const profileMessageContainer = document.getElementById('profile-message-container');
const passwordMessageContainer = document.getElementById('password-message-container');

// Profili Göster Butonu
if (showProfileModalBtn) {
    showProfileModalBtn.addEventListener('click', (e) => {
        e.preventDefault();
        
        fetch('api_handler.php?action=get_user_data')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Mevcut verileri inputlara doldur
                    document.getElementById('profile_username').value = data.data.username;
                    document.getElementById('profile_email').value = data.data.email;
                    
                    // YENİ: En üstteki avatar resmini güncelle
                    const avatarDisplay = document.getElementById('profile_avatar_display');
                    avatarDisplay.src = `assets/images/avatars/${data.data.avatar_url}`;

                    // Mevcut avatarı seçili yap
                    selectedAvatar = data.data.avatar_url;
                    avatarOptions.forEach(img => {
                        if (img.dataset.avatar === selectedAvatar) {
                            img.classList.add('selected');
                        } else {
                            img.classList.remove('selected');
                        }
                    });

                    profileModal.classList.add('active');
                } else {
                    alert('Kullanıcı bilgileri yüklenemedi: ' + data.error);
                }
            });
    });
}

// Profil Modalını Kapatma
if (closeProfileModalBtn) {
    closeProfileModalBtn.addEventListener('click', () => {
        profileModal.classList.remove('active');
    });
}
// Dışarı tıklayınca kapatma
window.addEventListener('click', (e) => {
    if (e.target == profileModal) {
        profileModal.classList.remove('active');
    }
});

// Avatar Seçim İşlevi
avatarOptions.forEach(img => {
    img.addEventListener('click', () => {
        avatarOptions.forEach(otherImg => otherImg.classList.remove('selected'));
        img.classList.add('selected');
        selectedAvatar = img.dataset.avatar;

        document.getElementById('profile_avatar_display').src = img.src;
    });
});

// Profil Güncelleme Formu Gönderimi (AJAX)
if (profileUpdateForm) {
    profileUpdateForm.addEventListener('submit', (e) => {
        e.preventDefault();
        const formData = new FormData(profileUpdateForm);
        formData.append('action', 'update_profile');
        formData.append('avatar_url', selectedAvatar); // Seçili avatarı ekle

        fetch('api_handler.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                profileMessageContainer.innerHTML = `<div class="message success">${data.message}</div>`;
                // Navigasyondaki kullanıcı adını da güncelle (opsiyonel ama şık)
                const usernameDisplay = document.querySelector('.dashboard-header strong'); // Bu seçiciyi kendi yapınıza göre ayarlayın
                if(usernameDisplay) usernameDisplay.textContent = formData.get('username');
            } else {
                profileMessageContainer.innerHTML = `<div class="message error">${data.error}</div>`;
            }
        });
    });
}

// Şifre Değiştirme Formu Gönderimi (AJAX)
if (passwordUpdateForm) {
    passwordUpdateForm.addEventListener('submit', (e) => {
        e.preventDefault();
        const formData = new FormData(passwordUpdateForm);
        formData.append('action', 'change_password');

        fetch('api_handler.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                passwordMessageContainer.innerHTML = `<div class="message success">${data.message}</div>`;
                passwordUpdateForm.reset(); // Formu temizle
            } else {
                passwordMessageContainer.innerHTML = `<div class="message error">${data.error}</div>`;
            }
        });
    });
}

// ===============================================
//          FORUM ETKİLEŞİM KODLARI
// ===============================================
const forumContainer = document.querySelector('.forum-posts-container');

if (forumContainer) {
    // Yorumları Göster/Gizle
    const commentToggleButtons = document.querySelectorAll('.comment-toggle-btn');
    commentToggleButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            const commentsSection = btn.closest('.forum-post').querySelector('.comments-section');
            if (commentsSection) {
                commentsSection.style.display = commentsSection.style.display === 'none' ? 'block' : 'none';
            }
        });
    });

    // Gönderi Beğenme (AJAX)
    const likeButtons = document.querySelectorAll('.like-btn');
    likeButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            const postId = btn.dataset.postId;
            const formData = new FormData();
            formData.append('action', 'toggle_like');
            formData.append('post_id', postId);

            fetch('api_handler.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const likeCountSpan = btn.querySelector('.like-count');
                    likeCountSpan.textContent = data.new_count;
                    if (data.liked) {
                        btn.classList.add('liked');
                    } else {
                        btn.classList.remove('liked');
                    }
                }
            });
        });
    });

    // Yorum Ekleme (AJAX)
    const commentForms = document.querySelectorAll('.comment-form');
    commentForms.forEach(form => {
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            
            const formData = new FormData(form);
            const existingCommentsDiv = form.closest('.comments-section').querySelector('.existing-comments');
            const commentInput = form.querySelector('input[name="comment_message"]');

            fetch('api_handler.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Yeni yorum için HTML oluştur
                    const newComment = document.createElement('div');
                    newComment.classList.add('comment');
                    newComment.innerHTML = `<strong>${data.comment.username}:</strong> ${data.comment.message}`;
                    
                    // Yeni yorumu listeye ekle
                    existingCommentsDiv.appendChild(newComment);
                    
                    // Yorum yazma alanını temizle
                    commentInput.value = '';
                } else {
                    alert(data.error || 'Bir hata oluştu.');
                }
            });
        });
    });
}


}); // --- TEK BİR DOMContentLoaded OLAY DİNLEYİCİSİNİN KAPANIŞI ---