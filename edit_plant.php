<?php
include_once 'includes/header.php';
$error = '';
$success = '';

// 1. GÜVENLİK: Kullanıcı giriş yapmış mı?
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

// 2. ID'yi al ve doğrula
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: dashboard.php');
    exit();
}
$plant_id = $_GET['id'];
$user_id = $_SESSION['user_id']; // <-- HATA BURADAYDI, BU SATIR EKSİKTİ VEYA YANLIŞ YERDEYDİ.

// 3. AKSİYONLARI YÖNET (Form gönderildiğinde)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Önce bitkinin bu kullanıcıya ait olduğunu tekrar doğrula
    $plant_check = supabase_api_request('GET', 'plants', ['id' => 'eq.' . $plant_id, 'user_id' => 'eq.' . $user_id]);
    if ($plant_check && count($plant_check) > 0) {

        if (isset($_POST['action'])) {
            $action = $_POST['action'];

            // =================== DÜZELTME BAŞLANGICI ===================

            // DEĞİŞİKLİK 1: Güncellenecek bitkinin yolunu (path) belirliyoruz.
            $path = 'plants?id=eq.' . $plant_id;

            if ($action === 'water') {
                // DEĞİŞİKLİK 2: Veri (payload) sadece güncellenecek kolonu içermeli.
                $updateData = ['last_watered_date' => date('c')];
                supabase_api_request('PATCH', $path, $updateData);
                $success = "Bitki sulandı olarak işaretlendi!";
            }

            if ($action === 'fertilize') {
                // DEĞİŞİKLİK 2: Veri (payload) sadece güncellenecek kolonu içermeli.
                $updateData = ['last_fertilized_date' => date('c')];
                supabase_api_request('PATCH', $path, $updateData);
                $success = "Bitki gübrelendi olarak işaretlendi!";
            }
            
            // =================== DÜZELTME SONU ===================

            if ($action === 'delete') {
                supabase_api_request('DELETE', 'plants', ['id' => 'eq.' . $plant_id]);
                // Başarı mesajı ile ana sayfaya yönlendir
                $_SESSION['notification'] = [
                    'type' => 'success',
                    'message' => 'Bitki başarıyla silindi.'
                ];
                header('Location: dashboard.php');
                exit();
            }
        }
    } else {
        $error = "Bu işlem için yetkiniz yok.";
    }
}


// 4. GÜNCEL BİTKİ BİLGİLERİNİ ÇEK
$plant_data = supabase_api_request('GET', 'plants', ['id' => 'eq.' . $plant_id, 'user_id' => 'eq.' . $user_id]);

// Eğer bitki bulunamazsa veya başkasına aitse, dashboard'a yönlendir.
if (!$plant_data || count($plant_data) === 0) {
    echo "<div class='message error'>Bitki bulunamadı veya bu bitkiye erişim yetkiniz yok.</div>";
    include_once 'includes/footer.php';
    exit();
}
$plant = $plant_data[0];

// JSON'dan özel cooldown süresini oku
$cooldown_minutes = 0; // Varsayılan değer
$plant_db_json = file_get_contents('data/plants_db.json');
$plant_options = json_decode($plant_db_json, true);

if ($plant_options) {
    foreach($plant_options as $option) {
        if ($option['species_name'] === $plant['species']) {
            if (isset($option['presentation_cooldown_minutes'])) {
                $cooldown_minutes = $option['presentation_cooldown_minutes'];
            }
            break; 
        }
    }
}
?>

<h2>Bitki Yönetimi</h2>

<?php if ($error): ?> <div class="message error"><?php echo $error; ?></div> <?php endif; ?>
<?php if ($success): ?> <div class="message success"><?php echo $success; ?></div> <?php endif; ?>

<div class="plant-manage-card" 
     data-plant-id="<?php echo $plant_id; ?>" 
     data-cooldown-minutes="<?php echo $cooldown_minutes; ?>">

    <div class="plant-manage-header">
        <img src="<?php echo htmlspecialchars($plant['image_url'] ?? 'https://via.placeholder.com/100x100.png?text=Bitki'); ?>" alt="<?php echo htmlspecialchars($plant['plant_name']); ?>">
        <div>
            <h3><?php echo htmlspecialchars($plant['plant_name']); ?></h3>
            <p class="species"><?php echo htmlspecialchars($plant['species']); ?></p>
        </div>
    </div>

    <div class="plant-manage-info">
        <p><strong>Son Sulama:</strong> <?php echo $plant['last_watered_date'] ? date('d M Y, H:i', strtotime($plant['last_watered_date'])) : 'Yok'; ?></p>
        <p><strong>Son Gübreleme:</strong> <?php echo $plant['last_fertilized_date'] ? date('d M Y, H:i', strtotime($plant['last_fertilized_date'])) : 'Yok'; ?></p>
    </div>

    <div class="plant-manage-actions">
        <h4>Hızlı İşlemler</h4>
        <form method="POST" action="edit_plant.php?id=<?php echo $plant_id; ?>">
            <button type="submit" id="water-button" name="action" value="water" class="btn btn-action-water">💧 Sula (Bugün)</button>
            <button type="submit" id="fertilize-button" name="action" value="fertilize" class="btn btn-action-fertilize">🌱 Gübrele (Bugün)</button>
        </form>
    </div>

    <div class="plant-manage-delete">
        <h4>Tehlikeli Alan</h4>
        <form method="POST" action="edit_plant.php?id=<?php echo $plant_id; ?>" onsubmit="return confirm('Bu bitkiyi kalıcı olarak silmek istediğinizden emin misiniz? Bu işlem geri alınamaz.');">
             <button type="submit" name="action" value="delete" class="btn btn-danger">🗑️ Bitkiyi Kalıcı Olarak Sil</button>
        </form>
    </div>
</div>

<a href="dashboard.php" style="display:inline-block; margin-top: 20px;">&larr; Geri Dön</a>

<?php include_once 'includes/footer.php'; ?>