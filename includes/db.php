<?php
// Oturumu başlat (her sayfada gerekecek)
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Supabase API Bilgileri
$supabase_url = 'https://qcxfgedwwjfypwtsdzme.supabase.co'; // Proje URL'niz
$supabase_key = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InFjeGZnZWR3d2pmeXB3dHNkem1lIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NjQ1NzkzNzAsImV4cCI6MjA4MDE1NTM3MH0.RmxwNeq2_m7fOgrmycaUSfPl38qNf7mfvcZageULwU4'; // Anon (public) Key'iniz

// =================== YENİ EKLENEN ANAHTAR ===================
// BU ANAHTARI Supabase Proje Ayarları > API > Project API keys altındaki "service_role" anahtarından al.
// BU ANAHTARI ASLA DIŞARIYA GÖSTERME! SADECE PHP KODUNDA GÜVENLİ BİR ŞEKİLDE KULLANILACAK.
$supabase_service_key = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InFjeGZnZWR3d2pmeXB3dHNkem1lIiwicm9sZSI6InNlcnZpY2Vfcm9sZSIsImlhdCI6MTc2NDU3OTM3MCwiZXhwIjoyMDgwMTU1MzcwfQ.1lhCxW1_lHCMQTPoLuHcHDxrsLR6caklTEOVSyG31cE'; 
// ==========================================================


function supabase_api_request($method, $path, $data = [], $token = null) {
    global $supabase_url, $supabase_key;

    $url = $supabase_url . '/rest/v1/' . $path;
    $headers = [
        'Content-Type: application/json',
        'Prefer: return=representation',
        'apikey: ' . $supabase_key,
        'Authorization: Bearer ' . ($token ?? $supabase_key)
    ];

    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    switch ($method) {
        case 'POST':
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_URL, $url);
            break;
        
         case 'PATCH':
            curl_setopt($ch, CURLOPT_URL, $url); 
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            break;

        case 'GET':
            if (!empty($data)) {
                $url .= '?' . http_build_query($data);
            }
            curl_setopt($ch, CURLOPT_URL, $url);
            break;

        case 'DELETE':
             curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
             if (!empty($data)) {
                $url .= '?' . http_build_query($data);
            }
            curl_setopt($ch, CURLOPT_URL, $url);
            break;
    }

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code >= 400) {
        return null;
    }

    return json_decode($response, true);
}
// =================== YENİ EKLENEN ADMIN FONKSİYONU ===================
// Bu fonksiyon, service_role anahtarını kullanarak RLS'yi atlar.
// Sadece admin yetkisi gerektiren silme, güncelleme gibi işlemler için kullanılmalıdır.
// =====================================================================
function supabase_admin_api_request($method, $path, $data = []) {
    global $supabase_url, $supabase_service_key; // DİKKAT: $supabase_key yerine $supabase_service_key kullanılıyor

    $url = $supabase_url . '/rest/v1/' . $path;
    $headers = [
        'Content-Type: application/json',
        'Prefer: return=representation',
        'apikey: ' . $supabase_service_key, // DİKKAT: Service key burada kullanılıyor
        'Authorization: Bearer ' . $supabase_service_key
    ];

    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    switch ($method) {
        case 'POST':
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_URL, $url);
            break;
        
         case 'PATCH':
            curl_setopt($ch, CURLOPT_URL, $url); 
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            break;

        case 'GET':
            if (!empty($data)) {
                $url .= '?' . http_build_query($data);
            }
            curl_setopt($ch, CURLOPT_URL, $url);
            break;

        case 'DELETE':
             curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
             if (!empty($data)) {
                $url .= '?' . http_build_query($data);
            }
            curl_setopt($ch, CURLOPT_URL, $url);
            break;
    }

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    // HTTP kodu 204 (No Content) genellikle başarılı bir DELETE işlemi için döner
    if ($http_code >= 400) {
        // Hata ayıklama için: error_log("Supabase Admin Error: " . $response);
        return null;
    }

    return json_decode($response, true);
}


// ===================================================================
//        YENİ: SUPABASE STORAGE'A DOSYA YÜKLEME FONKSİYONU
// ===================================================================
/**
 * Bir dosyayı Supabase Storage'a yükler.
 *
 * @param string $tmp_path Yüklenecek dosyanın geçici yolu (örn: $_FILES['image']['tmp_name']).
 * @param string $storage_path Dosyanın Supabase Storage'daki hedef yolu (örn: 'forum_uploads/resim.jpg').
 * @param string $mime_type Dosyanın MIME türü (örn: 'image/jpeg').
 * @return string|null Başarılı olursa dosyanın public URL'sini, başarısız olursa null döndürür.
 */
function upload_to_supabase_storage($tmp_path, $storage_path, $mime_type) {
    global $supabase_url, $supabase_key, $supabase_service_key;

    // Supabase Storage API endpoint'ini oluştur. 'plant_images' senin bucket adın.
    $url = $supabase_url . '/storage/v1/object/plant_images/' . $storage_path;

    // Gerekli header'ları ayarla. Yükleme için Service Role Key kullanılır.
    $headers = [
        'Content-Type: ' . $mime_type,
        'apikey: ' . $supabase_key,
        'Authorization: Bearer ' . $supabase_service_key
    ];

    // Dosyanın içeriğini oku
    $file_content = file_get_contents($tmp_path);
    if ($file_content === false) {
        return null; // Dosya okunamadı hatası
    }

    // cURL ile POST isteği gönder
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $file_content);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    
    if ($http_code === 200) {
        $public_url = $supabase_url . '/storage/v1/object/public/plant_images/' . $storage_path;
        return $public_url;
    }

    
    return null;
}



include_once 'functions.php'; 
?>