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
    $plant_name = trim($_POST['plant_name']);
    $selected_plant_key = $_POST['plant_species_key'];
    $last_watered_date = $_POST['last_watered_date'];
    $last_fertilized_date = $_POST['last_fertilized_date']; // YENİ
    $image_url = trim($_POST['image_url']);
    
    if (empty($plant_name) || !isset($plant_options[$selected_plant_key])) {
        $error = "Bitki adı ve türü seçimi zorunludur.";
    } else {
        $selected_plant_info = $plant_options[$selected_plant_key];

        // Veritabanına kaydedilecek yeni bitki dizisini oluştur
        $newPlant = [
            'user_id' => $_SESSION['user_id'],
            'plant_name' => $plant_name,
            'species' => $selected_plant_info['species_name'],
            'watering_interval' => $selected_plant_info['watering_interval'],
            'care_tip' => $selected_plant_info['care_tip'],
            'fertilizing_interval' => $selected_plant_info['fertilizing_interval'], // YENİ
            'last_watered_date' => !empty($last_watered_date) ? $last_watered_date : null,
            'last_fertilized_date' => !empty($last_fertilized_date) ? $last_fertilized_date : null, // YENİ
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
    <div class="message success"><?php echo $success; ?></div>
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
        Not: Sulama ve gübreleme sıklığı otomatik olarak atanacaktır.
    </p>

    <label for="last_watered_date">Son Sulama Tarihi:</label>
    <input type="date" id="last_watered_date" name="last_watered_date">
    
    <!-- YENİ GÜBRELEME TARİHİ ALANI -->
    <label for="last_fertilized_date">Son Gübreleme Tarihi (Opsiyonel):</label>
    <input type="date" id="last_fertilized_date" name="last_fertilized_date">

    <label for="image_url">Fotoğraf URL'si (Opsiyonel):</label>
    <input type="text" id="image_url" name="image_url" placeholder="https://ornek.com/resim.jpg">

    <button type="submit">Bitkiyi Ekle</button>
</form>

<?php include_once 'includes/footer.php'; ?>