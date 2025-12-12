<?php
include_once 'includes/header.php';
$error = '';
$success = '';

// Giriş yapmamış kullanıcıyı engelle
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $plant_name = trim($_POST['plant_name']);
    $species = trim($_POST['species']);
    $watering_interval = filter_input(INPUT_POST, 'watering_interval', FILTER_VALIDATE_INT);
    $last_watered_date = $_POST['last_watered_date'];
    $image_url = trim($_POST['image_url']);
    
    if (empty($plant_name) || !$watering_interval) {
        $error = "Bitki adı ve sulama aralığı zorunludur.";
    } else {
        $newPlant = [
            'user_id' => $_SESSION['user_id'],
            'plant_name' => $plant_name,
            'species' => $species,
            'watering_interval' => $watering_interval,
            'last_watered_date' => !empty($last_watered_date) ? $last_watered_date : null,
            'image_url' => $image_url
        ];
$result = supabase_api_request('POST', 'plants', $newPlant);
        
        if ($result !== null) {
            $success = "Bitkin başarıyla eklendi!";
        } else {
            $error = "Bitki eklenirken bir hata oluştu.";
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
    <label for="plant_name">Bitki Adı:</label>
    <input type="text" id="plant_name" name="plant_name" required>

    <label for="species">Türü (Örn: Sukulent, Orkide):</label>
    <input type="text" id="species" name="species">
 <label for="watering_interval">Sulama Sıklığı (Günde bir):</label>
    <input type="number" id="watering_interval" name="watering_interval" required>

    <label for="last_watered_date">Son Sulama Tarihi:</label>
    <input type="date" id="last_watered_date" name="last_watered_date">
    
    <label for="image_url">Fotoğraf URL'si (Opsiyonel):</label>
    <input type="text" id="image_url" name="image_url" placeholder="https://ornek.com/resim.jpg">

    <button type="submit">Bitkiyi Ekle</button>
</form>

<?php include_once 'includes/footer.php'; ?>