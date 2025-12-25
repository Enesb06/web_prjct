<?php
include_once 'includes/header.php';


$encyclopedia_json = file_get_contents('data/encyclopedia_data.json');
$plants = json_decode($encyclopedia_json, true);

?>

<div class="encyclopedia-header">
    <h2>Bitki Ansiklopedisi</h2>
    <p>Bitkiler hakkında detaylı bilgilere ulaşın ve bakım ipuçlarını öğrenin.</p>
    <div class="search-container">
        <input type="search" id="encyclopedia-search" placeholder="Bitki adı ara...">
    </div>
</div>

<div class="encyclopedia-grid">
    <?php if ($plants): ?>
        <?php foreach ($plants as $plant): 
            $plant_slug = slugify($plant['name']);
        ?>
            <div class="encyclopedia-card" id="<?php echo $plant_slug; ?>" data-name="<?php echo htmlspecialchars(strtolower($plant['name'])); ?>">
                <img src="assets/images/encyclopedia/<?php echo $plant_slug; ?>.jpg" 
                     alt="<?php echo htmlspecialchars($plant['name']); ?>" 
                     class="encyclopedia-card-img"
                     onerror="this.src='https://via.placeholder.com/400x300.png?text=<?php echo urlencode($plant['name']); ?>'">

                <div class="encyclopedia-card-body">
                    <span class="care-level level-<?php echo strtolower($plant['care_level']); ?>">
                        Bakım: <?php echo htmlspecialchars($plant['care_level']); ?>
                    </span>
                    <h3><?php echo htmlspecialchars($plant['name']); ?></h3>

                   <div class="plant-details-list">
    <div><strong>💧 Sulama:</strong> <span><?php echo $plant['watering_interval_days']; ?> günde ~<?php echo $plant['water_amount_ml']; ?>ml</span></div>
    
    
    <?php if (isset($plant['fertilizing_interval_days'])): ?>
        <div><strong>🌱 Gübreleme:</strong> <span><?php echo $plant['fertilizing_interval_days']; ?> günde bir</span></div>
    <?php endif; ?>

    <div><strong>☀️ Işık:</strong> <span><?php echo htmlspecialchars($plant['light']); ?></span></div>
    <div><strong>📍 Ortam:</strong> <span><?php echo htmlspecialchars($plant['environment']); ?></span></div>
    <div><strong>🐾 Evcil Hayvan:</strong> 
        <span class="<?php echo $plant['pet_friendly'] ? 'pet-safe' : 'pet-toxic'; ?>">
            <?php echo $plant['pet_friendly'] ? 'Güvenli' : 'Toksik'; ?>
        </span>
    </div>
</div>
                    <p class="plant-notes"><?php echo htmlspecialchars($plant['notes']); ?></p>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>Ansiklopedi verileri yüklenemedi.</p>
    <?php endif; ?>
</div>

<?php include_once 'includes/footer.php'; ?>