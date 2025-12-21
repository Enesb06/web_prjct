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

// ÖZET HESAPLAMALARI
$total_plants = 0;
$plants_to_water_today = 0;
$plants_overdue = 0;

// =================================================================== //
//          GÜNLÜK BİLDİRİM HESAPLAMA KODU                             //
// =================================================================== //
$daily_notification_data = null; 

$today_date = date('Y-m-d');

if (!isset($_SESSION['last_login_greeting']) || $_SESSION['last_login_greeting'] !== $today_date) {
    
    $soonest_plant = null;
    $min_days_diff = PHP_INT_MAX; 

    if ($plants && count($plants) > 0) {
        $today = new DateTime();
        foreach ($plants as $plant) {
            if (!empty($plant['last_watered_date'])) {
                try {
                    $last_watered = new DateTime($plant['last_watered_date']);
                    $next_watering = (clone $last_watered)->modify('+' . $plant['watering_interval'] . ' days');
                    $interval = $today->diff($next_watering);
                    $days_diff = (int)$interval->format('%r%a');

                    if ($days_diff >= 0 && $days_diff < $min_days_diff) {
                        $min_days_diff = $days_diff;
                        $soonest_plant = $plant;
                    }
                } catch (Exception $e) {
                    // Geçersiz tarih formatını yoksay
                }
            }
        }
    }
    
    if ($soonest_plant) {
        $message = '';
        if ($min_days_diff == 0) {
            $message = "<strong>" . htmlspecialchars($soonest_plant['plant_name']) . "</strong> için bugün sulama günü!";
        } else {
            $message = "En yakın sulama: <strong>" . htmlspecialchars($soonest_plant['plant_name']) . "</strong> bitkisine " . $min_days_diff . " gün kaldı.";
        }
        
        $daily_notification_data = [
            'type' => 'info',
            'message' => $message
        ];
    }
    
    $_SESSION['last_login_greeting'] = $today_date;
}


if ($plants && count($plants) > 0) {
    $total_plants = count($plants);
    $today = new DateTime();
    foreach ($plants as $plant) {
        if (!empty($plant['last_watered_date'])) {
            try {
                $last_watered = new DateTime($plant['last_watered_date']);
                $next_watering = (clone $last_watered)->modify('+' . $plant['watering_interval'] . ' days');
                $interval = $today->diff($next_watering);
                $days_diff = (int)$interval->format('%r%a');
                if ($days_diff < 0) {
                    $plants_overdue++;
                } elseif ($days_diff == 0) {
                    $plants_to_water_today++;
                }
            } catch (Exception $e) {
                 // Geçersiz tarih formatını yoksay
            }
        }
    }
}
?>

<div class="dashboard-header">
    <h2>Bitkilerim</h2>
    <a href="add_plant.php" class="btn-add-plant">Yeni Bitki Ekle</a>
</div>

<?php
// --- TAKVİM VERİLERİNİ HAZIRLAMA ---
$current_month = date('m');
$current_year = date('Y');
$current_day = date('d');
$first_day_of_week = date('N', strtotime("{$current_year}-{$current_month}-01"));
$days_in_month = cal_days_in_month(CAL_GREGORIAN, $current_month, $current_year);
$month_names = ["", "Ocak", "Şubat", "Mart", "Nisan", "Mayıs", "Haziran", "Temmuz", "Ağustos", "Eylül", "Ekim", "Kasım", "Aralık"];
$current_month_name = $month_names[(int)$current_month];
$care_events = [];

if ($plants && count($plants) > 0) {
    foreach ($plants as $plant) {
        // --- Sulama günlerini hesapla ---
        if (!empty($plant['last_watered_date']) && !empty($plant['watering_interval'])) {
            try {
                $next_watering = new DateTime($plant['last_watered_date']);
                $next_watering->modify('+' . $plant['watering_interval'] . ' days');
                while ($next_watering->format('Y-m') <= $current_year . '-' . $current_month) {
                    if ($next_watering->format('Y-m') == $current_year . '-' . $current_month) {
                        $day = (int)$next_watering->format('d');
                        if (!isset($care_events[$day])) $care_events[$day] = [];
                        if (!in_array('water', $care_events[$day])) {
                           $care_events[$day][] = 'water';
                        }
                    }
                    $next_watering->modify('+' . $plant['watering_interval'] . ' days');
                }
            } catch (Exception $e) {
                // Geçersiz tarih formatını yoksay
            }
        }

        // --- Gübreleme günlerini hesapla ---
        if (!empty($plant['last_fertilized_date']) && !empty($plant['fertilizing_interval'])) {
            // =================================================================== //
            //             DEĞİŞİKLİK BAŞLANGICI (BURASI HATAYI ÖNLER)               //
            // =================================================================== //
            try {
                $next_fertilizing = new DateTime($plant['last_fertilized_date']);
                $next_fertilizing->modify('+' . $plant['fertilizing_interval'] . ' days');

                while ($next_fertilizing->format('Y-m') <= $current_year . '-' . $current_month) {
                     if ($next_fertilizing->format('Y-m') == $current_year . '-' . $current_month) {
                        $day = (int)$next_fertilizing->format('d');
                        if (!isset($care_events[$day])) $care_events[$day] = [];
                        if (!in_array('fertilize', $care_events[$day])) {
                            $care_events[$day][] = 'fertilize';
                        }
                    }
                    $next_fertilizing->modify('+' . $plant['fertilizing_interval'] . ' days');
                }
            } catch (Exception $e) {
                // Tarih formatı bozuksa bu bitkiyi takvim hesaplamasında atla.
                // İsteğe bağlı: error_log("Invalid date format for plant ID " . $plant['id']);
            }
            // =================================================================== //
            //                          DEĞİŞİKLİK SONU                            //
            // =================================================================== //
        }
    }
}
?>

<div class="welcome-calendar-card">
    <div class="welcome-text">
        <h2>Selam, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
        <p class="welcome-quote">"Bir bitkiyi sevmek, büyümeye inanmaktır."</p>
    </div>

    <div class="calendar-container">
        <table class="calendar-table">
            <thead>
                <tr>
                    <th colspan="7" class="calendar-month-header"><?php echo strtoupper($current_month_name); ?></th>
                </tr>
                <tr>
                    <th>P</th><th>S</th><th>Ç</th><th>P</th><th>C</th><th>C</th><th>P</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                <?php
                    // Ayın ilk gününden önceki boş hücreleri doldur
                    for ($i = 1; $i < $first_day_of_week; $i++) {
                        echo "<td></td>";
                    }

                    // Ayın günlerini döngüye al
                    for ($day = 1; $day <= $days_in_month; $day++) {
                        // Eğer haftanın başıysa yeni bir satır (tr) başlat
                        if (($day + $first_day_of_week - 2) % 7 == 0 && $day != 1) {
                            echo "</tr><tr>";
                        }
                        
                        $today_class = ($day == $current_day) ? ' today' : '';
                        
                        echo "<td class='day-cell{$today_class}'>";
                        echo "<div class='day-number'>{$day}</div>";
                        
                        if (isset($care_events[$day])) {
                            echo "<div class='care-icons-container'>";
                            if (in_array('water', $care_events[$day])) {
                                echo '<i class="fas fa-tint care-icon water" title="Sulama Günü"></i>';
                            }
                            if (in_array('fertilize', $care_events[$day])) {
                                echo '<i class="fas fa-leaf care-icon fertilize" title="Gübreleme Günü"></i>';
                            }
                            echo "</div>";
                        }
                        echo "</td>";
                    }
                    $remaining_days = 7 - (($days_in_month + $first_day_of_week - 1) % 7);
                    if ($remaining_days < 7) {
                        for ($i = 0; $i < $remaining_days; $i++) {
                            echo "<td></td>";
                        }
                    }
                ?>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<p>Merhaba, <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong>! İşte bitkilerinin güncel durumu.</p>

<div class="summary-panel">
    <div class="summary-card">
        <h3><?php echo $total_plants; ?></h3>
        <p>Toplam Bitki</p>
    </div>
    <div class="summary-card status-today">
        <h3><?php echo $plants_to_water_today; ?></h3>
        <p>Bugün Sulanacak</p>
    </div>
    <div class="summary-card status-overdue">
        <h3><?php echo $plants_overdue; ?></h3>
        <p>Sulama Gecikmiş</p>
    </div>
</div>

<div class="plant-list">
    <?php if ($plants && count($plants) > 0): ?>
        <?php foreach ($plants as $plant): ?>
            <div class="plant-card">
                <img src="<?php echo htmlspecialchars($plant['image_url'] ?? 'https://via.placeholder.com/250x150.png?text=Bitki'); ?>" alt="<?php echo htmlspecialchars($plant['plant_name']); ?>" class="plant-card-img">
                <div class="plant-card-body">
                    <h3><?php echo htmlspecialchars($plant['plant_name']); ?></h3>
                    <p class="species"><?php echo htmlspecialchars($plant['species']); ?></p>
                    
                    <div class="plant-info">
                        <span><strong>💧 Sulama:</strong> <?php echo $plant['watering_interval']; ?> günde bir</span>
                        <span><strong>📅 Son Sulama:</strong> <?php echo $plant['last_watered_date'] ? date('d M Y, H:i', strtotime($plant['last_watered_date'])) : 'Belirtilmemiş'; ?></span>
                        
                        <?php if (!empty($plant['fertilizing_interval'])): ?>
                            <span><strong>🌱 Gübreleme:</strong> <?php echo $plant['fertilizing_interval']; ?> günde bir</span>
                            <span><strong>🗓️ Son Gübreleme:</strong> <?php echo $plant['last_fertilized_date'] ? date('d M Y, H:i', strtotime($plant['last_fertilized_date'])) : 'Belirtilmemiş'; ?></span>
                        <?php endif; ?> 

                        <?php if (!empty($plant['care_tip'])): ?>
                            <span class="care-tip"><strong>💡 İpucu:</strong> <?php echo htmlspecialchars($plant['care_tip']); ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="watering-status">
                        <?php
                        if ($plant['last_watered_date']) {
                            try {
                                $today = new DateTime();
                                $last_watered = new DateTime($plant['last_watered_date']);
                                $next_watering = (clone $last_watered)->modify('+' . $plant['watering_interval'] . ' days');
                                $interval = $today->diff($next_watering);
                                $days_diff = (int)$interval->format('%r%a');

                                if ($days_diff < 0) {
                                    echo '<p class="status-overdue">Sulama ' . abs($days_diff) . ' gün gecikti!</p>';
                                } elseif ($days_diff == 0) {
                                    echo '<p class="status-today">Bugün sulama günü!</p>';
                                } else {
                                    echo '<p class="status-ok">Sonraki sulamaya ' . $days_diff . ' gün kaldı.</p>';
                                }
                            } catch(Exception $e) {
                                echo '<p class="status-unknown">Sulama tarihi geçersiz.</p>';
                            }
                        } else {
                            echo '<p class="status-unknown">Sulama durumu için son sulama tarihini girin.</p>';
                        }
                        ?>
                    </div>

                    <div class="fertilizing-status">
                         <?php
                        if (!empty($plant['last_fertilized_date']) && !empty($plant['fertilizing_interval'])) {
                            // =================================================================== //
                            //         DEĞİŞİKLİK BAŞLANGICI (BURASI DA HATAYI ÖNLER)                //
                            // =================================================================== //
                            try {
                                $today = new DateTime();
                                $last_fertilized = new DateTime($plant['last_fertilized_date']);
                                $next_fertilizing = (clone $last_fertilized)->modify('+' . $plant['fertilizing_interval'] . ' days');
                                
                                $interval = $today->diff($next_fertilizing);
                                $days_diff = (int)$interval->format('%r%a');

                                if ($days_diff < 0) {
                                    echo '<p class="status-overdue">Gübreleme ' . abs($days_diff) . ' gün gecikti!</p>';
                                } elseif ($days_diff == 0) {
                                    echo '<p class="status-today">Bugün gübreleme günü!</p>';
                                } else {
                                    echo '<p class="status-ok">Sonraki gübrelemeye ' . $days_diff . ' gün kaldı.</p>';
                                }
                            } catch (Exception $e) {
                                echo '<p class="status-unknown">Gübreleme tarihi geçersiz.</p>';
                            }
                            // =================================================================== //
                            //                          DEĞİŞİKLİK SONU                            //
                            // =================================================================== //
                        } else if (!empty($plant['fertilizing_interval'])) {
                            echo '<p class="status-unknown">Gübreleme durumu için son gübreleme tarihini girin.</p>';
                        }
                        ?>
                    </div>

                   <div class="plant-actions">
                        <?php
                            $species_name_clean = preg_replace('/\s*\(.*\)/', '', $plant['species']);
                            $plant_encyclopedia_slug = slugify($species_name_clean);
                        ?>
                        <a href="encyclopedia.php?plant=<?php echo $plant_encyclopedia_slug; ?>" class="btn btn-info">Detaylar</a>
                        <a href="edit_plant.php?id=<?php echo $plant['id']; ?>" class="btn btn-secondary">Yönet</a>
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