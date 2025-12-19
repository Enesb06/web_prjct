<?php
include_once 'includes/header.php';
$error = '';
$success = '';

// Giriş yapmamış kullanıcıyı engelle
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

// JSON dosyasından bitki verilerini oku
$plant_data_json = file_get_contents('data/plants_db.json');
$plant_options = json_decode($plant_data_json, true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Formdan gelen verileri al
    $plant_name = trim($_POST['plant_name']); // Kullanıcının verdiği özel isim
    $selected_plant_key = $_POST['plant_species_key']; // Seçilen bitkinin JSON'daki anahtarı (index'i)
    $last_watered_date = $_POST['last_watered_date'];
    $image_url = trim($_POST['image_url']);
    
    // Gerekli alanların kontrolü
    if (empty($plant_name) || !isset($plant_options[$selected_plant_key])) {
        $error = "Bitki adı ve türü seçimi zorunludur.";
    } else {
        // Seçilen bitkinin bilgilerini JSON verisinden al
        $selected_plant_info = $plant_options[$selected_plant_key];

        // Veritabanına kaydedilecek yeni bitki dizisini oluştur
        $newPlant = [
            'user_id' => $_SESSION['user_id'],
            'plant_name' => $plant_name,
            'species' => $selected_plant_info['species_name'], // Tür adı
            'watering_interval' => $selected_plant_info['watering_interval'], // Sulama sıklığı
            'care_tip' => $selected_plant_info['care_tip'], // Bakım ipucu
            'last_watered_date' => !empty($last_watered_date) ? $last_watered_date : null,
            'image_url' => $image_url
        ];

        $result = supabase_api_request('POST', 'plants', $newPlant);
        
        if ($result !== null) {
            $success = "Bitkin başarıyla eklendi! <a href='dashboard.php'>Bitkilerimi Gör</a>";
        } else {
            $error = "Bitki eklenirken bir hata oluştu. Lütfen tekrar deneyin.";
        }
    }
}
?>

<h2>Yeni Bitki Ekle</h2>

<?php if ($error): ?>
    <div class="message error"><?php echo $error; ?></div>
<?php endif; ?>
<?php if ($success): ?>
    <div class="message success"><?php echo $success; // HTML linkine izin ver ?></div>
<?php endif; ?>

<form action="add_plant.php" method="POST">
    <label for="plant_name">Bitkine bir isim ver (Örn: Boncuk, Paşa):</label>
    <input type="text" id="plant_name" name="plant_name" required>

    <label for="plant_species_key">Bitkinin Türünü Seç:</label>
    <select id="plant_species_key" name="plant_species_key" required>
        <option value="" disabled selected>-- Lütfen bir bitki türü seçin --</option>
        <?php foreach ($plant_options as $key => $plant): ?>
            <option value="<?php echo $key; ?>">
                <?php echo htmlspecialchars($plant['species_name']); ?>
            </option>
        <?php endforeach; ?>
    </select>
    
    <p style="font-size:0.9em; color:#777; margin-top: -10px;">
        Not: Bitki türünü seçtiğinizde sulama sıklığı ve bakım ipucu otomatik olarak atanacaktır.
    </p>

    <label for="last_watered_date">Son Sulama Tarihi:</label>
    <input type="date" id="last_watered_date" name="last_watered_date">
    
    <label for="image_url">Fotoğraf URL'si (Opsiyonel):</label>
    <input type="text" id="image_url" name="image_url" placeholder="https://ornek.com/resim.jpg">

    <button type="submit">Bitkiyi Ekle</button>
</form>

<?php include_once 'includes/footer.php'; ?>