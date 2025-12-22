<?php
include_once 'includes/header.php';

$error = '';
$success = '';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$plant_data_json = file_get_contents('data/plants_db.json');
$plant_options = json_decode($plant_data_json, true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $plant_name = trim($_POST['plant_name']);
    $selected_plant_key = $_POST['plant_species_key'];
    $last_watered_date = $_POST['last_watered_date'];
    $last_fertilized_date = $_POST['last_fertilized_date']; 
    
    $image_path_for_db = null;

    // =============== DEĞİŞTİRİLEN DOSYA YÜKLEME BLOĞU BAŞLANGICI ===============
    if (isset($_FILES['plant_image']) && $_FILES['plant_image']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['plant_image'];
        
        if ($file['size'] > 4194304) { // Max 4MB
            $error = 'Hata: Dosya boyutu 4MB\'den büyük olamaz.';
        } else {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime_type = finfo_file($finfo, $file['tmp_name']);
            $allowed_mime_types = ['image/jpeg', 'image/png', 'image/gif'];
            finfo_close($finfo);

            if (in_array($mime_type, $allowed_mime_types)) {
                $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                // Benzersiz dosya adı: user_123/plant_asdasd123.jpg
                $unique_filename = 'plant_' . uniqid('', true) . '.' . $file_extension;
                $storage_path = 'user_uploads/' . $_SESSION['user_id'] . '/' . $unique_filename;

                // Yeni fonksiyonu kullanarak Supabase'e yükle
                $image_path_for_db = upload_to_supabase_storage($file['tmp_name'], $storage_path, $mime_type);
                
                if ($image_path_for_db === null) {
                    $error = 'Resim Supabase\'e yüklenirken bir hata oluştu.';
                }
            } else {
                $error = 'Hata: Sadece JPG, PNG ve GIF formatında resimler yükleyebilirsiniz.';
            }
        }
    }
    // =============== DEĞİŞTİRİLEN DOSYA YÜKLEME BLOĞU SONU ===============

    if (empty($plant_name) || !isset($plant_options[$selected_plant_key])) {
        $error = "Bitki adı ve türü seçimi zorunludur.";
    } 
    
    if (empty($error)) {
        $selected_plant_info = $plant_options[$selected_plant_key];

        if ($image_path_for_db === null && isset($selected_plant_info['species_name'])) {
            $species_name_clean = preg_replace('/\s*\(.*\)/', '', $selected_plant_info['species_name']);
            $plant_slug = slugify(trim($species_name_clean));
            $image_path_for_db = 'assets/images/encyclopedia/' . $plant_slug . '.jpg';
        }

        $newPlant = [
            'user_id' => $_SESSION['user_id'],
            'plant_name' => $plant_name,
            'species' => $selected_plant_info['species_name'],
            'watering_interval' => $selected_plant_info['watering_interval'],
            'care_tip' => $selected_plant_info['care_tip'],
            'fertilizing_interval' => $selected_plant_info['fertilizing_interval'],
            'last_watered_date' => !empty($last_watered_date) ? $last_watered_date : null,
            'last_fertilized_date' => !empty($last_fertilized_date) ? $last_fertilized_date : null,
            'image_url' => $image_path_for_db
        ];

        $result = supabase_api_request('POST', 'plants', $newPlant);
        
        if ($result !== null) {
            $_SESSION['notification'] = [
                'type' => 'success',
                'message' => 'Yeni bitkin eklendi: <strong>' . htmlspecialchars($plant_name) . '</strong>'
            ];
            header('Location: dashboard.php');
            exit();
        } else {
            $error = "Bitki eklenirken bir veritabanı hatası oluştu. Lütfen tekrar deneyin.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <!-- Head içeriğiniz header.php dosyasından gelecek -->
</head>
<body>
    <!-- Header'ınız ve navigasyonunuz header.php dosyasından gelecek -->

    <!-- =================================================================== -->
    <!--                     YENİ TASARIM BURADA BAŞLIYOR                      -->
    <!-- =================================================================== -->
    <div class="add-plant-page-container">
        <div class="add-plant-card">
            <h2>Yeni Bitki Ekle</h2>

            <?php if ($error): ?>
                <div class="message error" style="background-color: #f8d7da; color: #721c24;"><?php echo $error; ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="message success" style="background-color: #d4edda; color: #155724;"><?php echo $success; ?></div>
            <?php endif; ?>

            <form action="add_plant.php" method="POST" enctype="multipart/form-data">
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
                
                <p class="form-note">
                    Not: Sulama ve gübreleme sıklığı otomatik olarak atanacaktır.
                </p>

                <label for="last_watered_date">Son Sulama Tarihi:</label>
                <input type="datetime-local" id="last_watered_date" name="last_watered_date">
                
                <label for="last_fertilized_date">Son Gübreleme Tarihi:</label>
                <input type="datetime-local" id="last_fertilized_date" name="last_fertilized_date">

                <!-- YENİ DOSYA YÜKLEME ALANI -->
                <label for="plant_image">Bitkinin Fotoğrafını Yükle (Opsiyonel):</label>
                <input type="file" id="plant_image" name="plant_image" accept="image/png, image/jpeg, image/gif">
                
                <label for="plant_image" class="custom-file-upload" id="drop-area">
                    <div class="upload-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="currentColor" class="bi bi-cloud-arrow-up-fill" viewBox="0 0 16 16">
                          <path d="M8 2a5.53 5.53 0 0 0-3.594 1.342c-.766.66-1.321 1.52-1.464 2.383C1.266 6.095 0 7.555 0 9.318 0 11.366 1.708 13 3.781 13h8.906C14.502 13 16 11.57 16 9.773c0-1.636-1.242-2.969-2.834-3.194C12.923 3.999 10.69 2 8 2zm2.354 5.146a.5.5 0 0 1-.708.708L8.5 6.707V10.5a.5.5 0 0 1-1 0V6.707L6.354 7.854a.5.5 0 1 1-.708-.708l2-2a.5.5 0 0 1 .708 0l2 2z"/>
                        </svg>
                    </div>
                    <div class="upload-text">
                        Resmi buraya sürükleyin veya <strong>seçmek için tıklayın</strong>
                    </div>
                </label>
                <div id="image-preview"></div>
                <!-- YENİ DOSYA YÜKLEME ALANI SONU -->

                <button type="submit">Bitkiyi Ekle</button>
            </form>
        </div>
    </div>
    <!-- =================================================================== -->
    <!--                       YENİ TASARIM BURADA BİTİYOR                     -->
    <!-- =================================================================== -->

    
    <!-- =================================================================== -->
    <!--          YENİ: BİTKİ EKLEME YÜKLENİYOR ANİMASYON KATMANI            -->
    <!-- =================================================================== -->
    <div class="loading-overlay" id="loading-overlay">
        <div class="loading-content">
            <lottie-player
                id="lottie-add-plant-player"
                src="assets/animations/plant_add_animations.json"
                background="transparent"
                speed="1"
                style="width: 250px; height: 250px;"
                loop>
            </lottie-player>
            <p class="loading-text">Yeni bitkiniz ekleniyor...</p>
        </div>
    </div>
    <!-- =================================================================== -->

    
    <?php include_once 'includes/footer.php'; ?>
    
    <!-- Script dosyaları footer.php dosyasından gelecek -->
</body>
</html>