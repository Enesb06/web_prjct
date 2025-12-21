<?php
// db.php'yi en üste dahil ediyoruz. 
// Bu dosya hem veritabanı fonksiyonlarını getirir hem de session_start() komutunu çalıştırır.

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
    // Formdan metin verilerini al
    $plant_name = trim($_POST['plant_name']);
    $selected_plant_key = $_POST['plant_species_key'];
    $last_watered_date = $_POST['last_watered_date'];
    $last_fertilized_date = $_POST['last_fertilized_date']; 
    
    // Varsayılan resim yolu
    $image_path_for_db = null;

    // Dosya yükleme mantığı
    // Bir dosya seçilmiş mi ve yüklemede hata var mı kontrol et
    if (isset($_FILES['plant_image']) && $_FILES['plant_image']['error'] === UPLOAD_ERR_OK) {
        
        $upload_dir = 'assets/images/user_uploads/';
        $file = $_FILES['plant_image'];
        
        // Güvenlik: Dosya boyutunu kontrol et (örn: max 2MB)
        if ($file['size'] > 2097152) {
            $error = 'Hata: Dosya boyutu 2MB\'den büyük olamaz.';
        } else {
            // Güvenlik: Dosya tipinin gerçekten bir resim olduğunu doğrula
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime_type = finfo_file($finfo, $file['tmp_name']);
            $allowed_mime_types = ['image/jpeg', 'image/png', 'image/gif'];
            finfo_close($finfo);

            if (!in_array($mime_type, $allowed_mime_types)) {
                $error = 'Hata: Sadece JPG, PNG ve GIF formatında resimler yükleyebilirsiniz.';
            } else {
                // Güvenlik: Benzersiz bir dosya adı oluşturarak üzerine yazmaları engelle
                $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                $unique_filename = uniqid('plant_', true) . '.' . $file_extension;
                $destination = $upload_dir . $unique_filename;

                // Dosyayı geçici konumundan hedef klasöre taşı
                if (move_uploaded_file($file['tmp_name'], $destination)) {
                    $image_path_for_db = $destination; // Veritabanına kaydedilecek yol
                } else {
                    $error = 'Dosya yüklenirken bir hata oluştu.';
                }
            }
        }
    }

    if (empty($plant_name) || !isset($plant_options[$selected_plant_key])) {
        $error = "Bitki adı ve türü seçimi zorunludur.";
    } 
    
    // Eğer bir dosya yükleme hatası oluşmadıysa devam et
    if (empty($error)) {
        $selected_plant_info = $plant_options[$selected_plant_key];

        // =================================================================== //
        //                  YENİ EKLENEN KISIM BAŞLANGICI                        //
        // =================================================================== //

        // Eğer kullanıcı özel bir resim yüklemediyse ($image_path_for_db hala null ise)
        // ve bir bitki türü seçilmişse, varsayılan ansiklopedi resmini ata.
        if ($image_path_for_db === null && isset($selected_plant_info['species_name'])) {
            
            // 1. Türün adını al ve parantez içindeki bilimsel ismi temizle.
            // Örnek: "Deve Tabanı (Monstera Deliciosa)" -> "Deve Tabanı"
            $species_name_clean = preg_replace('/\s*\(.*\)/', '', $selected_plant_info['species_name']);
            
            // 2. Temizlenmiş adı dosya ismine uygun hale getir (slugify).
            // Örnek: "Deve Tabanı" -> "deve-tabani"
            $plant_slug = slugify(trim($species_name_clean));
            
            // 3. Varsayılan resim yolunu oluştur.
            // Örnek: "assets/images/encyclopedia/deve-tabani.jpg"
            $image_path_for_db = 'assets/images/encyclopedia/' . $plant_slug . '.jpg';
        }

        // =================================================================== //
        //                    YENİ EKLENEN KISIM SONU                          //
        // =================================================================== //


        // Veritabanına kaydedilecek yeni bitki dizisini oluştur
        $newPlant = [
            'user_id' => $_SESSION['user_id'],
            'plant_name' => $plant_name,
            'species' => $selected_plant_info['species_name'],
            'watering_interval' => $selected_plant_info['watering_interval'],
            'care_tip' => $selected_plant_info['care_tip'],
            'fertilizing_interval' => $selected_plant_info['fertilizing_interval'],
            'last_watered_date' => !empty($last_watered_date) ? $last_watered_date : null,
            'last_fertilized_date' => !empty($last_fertilized_date) ? $last_fertilized_date : null,
            'image_url' => $image_path_for_db // Bu değişken artık ya kullanıcının yüklediği resmi ya da varsayılan resmi tutuyor.
        ];

        $result = supabase_api_request('POST', 'plants', $newPlant);
        
        // =================================================================== //
        //             *** DEĞİŞTİRİLEN BLOK BAŞLANGICI ***                      //
        // =================================================================== //
        if ($result !== null) {
            // Session'a bildirim mesajını ve türünü kaydet
            $_SESSION['notification'] = [
                'type' => 'success',
                'message' => 'Yeni bitkin eklendi: <strong>' . htmlspecialchars($plant_name) . '</strong>'
            ];
            // Kullanıcıyı ana panele yönlendir. Oradaki footer bu bildirimi gösterecek.
            header('Location: dashboard.php');
            exit();
        } else {
            $error = "Bitki eklenirken bir veritabanı hatası oluştu. Lütfen tekrar deneyin.";
        }
        // =================================================================== //
        //              *** DEĞİŞTİRİLEN BLOK SONU ***                         //
        // =================================================================== //
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <!-- Head içeriğiniz burada -->
</head>
<body>
    <!-- Header'ınız ve navigasyonunuz burada -->

    <div class="container">
        <h2>Yeni Bitki Ekle</h2>

        <?php if ($error): ?>
            <div class="message error"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="message success"><?php echo $success; ?></div>
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
            
            <p style="font-size:0.9em; color:#777; margin-top: -10px;">
                Not: Sulama ve gübreleme sıklığı otomatik olarak atanacaktır.
            </p>

            <label for="last_watered_date">Son Sulama Tarihi:</label>
            <input type="datetime-local" id="last_watered_date" name="last_watered_date">
            
            <label for="last_fertilized_date">Son Gübreleme Tarihi (Opsiyonel):</label>
            <input type="datetime-local" id="last_fertilized_date" name="last_fertilized_date">

            <label for="plant_image">Bitkinin Fotoğrafını Yükle (Opsiyonel):</label>
            <input type="file" id="plant_image" name="plant_image" accept="image/png, image/jpeg, image/gif">

            <button type="submit">Bitkiyi Ekle</button>
        </form>
    </div>

    <?php include_once 'includes/footer.php'; ?>
</body>
</html>