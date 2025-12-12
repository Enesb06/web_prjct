<?php
include_once 'includes/header.php';

// Giriş yapmamış kullanıcıyı engelle
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

// Kullanıcının bitkilerini veritabanından çek (en yeniden eskiye sıralı)
$user_id = $_SESSION['user_id'];
$plants = supabase_api_request('GET', 'plants', ['user_id' => 'eq.' . $user_id, 'order' => 'created_at.desc']);

?>

<div class="dashboard-header">
    <h2>Bitkilerim</h2>
    <a href="add_plant.php" class="btn-add-plant">Yeni Bitki Ekle</a>
</div>

<p>Merhaba, <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong>! İşte bitkilerinin güncel durumu.</p>

<div class="plant-list">
    <?php if ($plants && count($plants) > 0): ?>
        <?php foreach ($plants as $plant): ?>
            <div class="plant-card">
                <img src="<?php echo htmlspecialchars($plant['image_url'] ?? 'https://via.placeholder.com/250x150.png?text=Bitki'); ?>" alt="<?php echo htmlspecialchars($plant['plant_name']); ?>" class="plant-card-img">
                <div class="plant-card-body">
                    <h3><?php echo htmlspecialchars($plant['plant_name']); ?></h3>
                    <p class="species"><?php echo htmlspecialchars($plant['species']); ?></p>
                    
                    <div class="plant-info">
                        <span><strong>Sulama:</strong> <?php echo $plant['watering_interval']; ?> günde bir</span>
                        <span><strong>Son Sulama:</strong> <?php echo $plant['last_watered_date'] ? date('d M Y', strtotime($plant['last_watered_date'])) : 'Belirtilmemiş'; ?></span>
                    </div>

                    <div class="watering-status">
                        <?php
                        if ($plant['last_watered_date']) {
                            $today = new DateTime();
                            $last_watered = new DateTime($plant['last_watered_date']);
                            $next_watering = (clone $last_watered)->modify('+' . $plant['watering_interval'] . ' days');
                            
                            $interval = $today->diff($next_watering);
                            $days_diff = (int)$interval->format('%r%a'); // %r ile negatif/pozitif alır, %a ile gün sayısını

                            if ($days_diff < 0) {
                                echo '<p class="status-overdue">Sulama ' . abs($days_diff) . ' gün gecikti!</p>';
                            } elseif ($days_diff == 0) {
                                echo '<p class="status-today">Bugün sulama günü!</p>';
                            } else {
                                echo '<p class="status-ok">Sonraki sulamaya ' . $days_diff . ' gün kaldı.</p>';
                            }
                        } else {
                            echo '<p class="status-unknown">Sulama durumu için son sulama tarihini girin.</p>';
                        }
                        ?>
                    </div>

                    <div class="plant-actions">
                        <a href="edit_plant.php?id=<?php echo $plant['id']; ?>" class="btn btn-secondary">Düzenle</a>
                        <a href="delete_plant.php?id=<?php echo $plant['id']; ?>" class="btn btn-danger" onclick="return confirm('Bu bitkiyi silmek istediğinizden emin misiniz?');">Sil</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="no-plants-message">
            <h3>Henüz hiç bitki eklemedin!</h3>
            <p>İlk bitkini ekleyerek bakımını takip etmeye başla.</p>
            <a href="add_plant.php" class="btn-add-plant-big">Hemen Ekle</a>
        </div>
    <?php endif; ?>
</div>

<?php include_once 'includes/footer.php'; ?>