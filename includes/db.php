<?php
// Oturumu başlat (her sayfada gerekecek)
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Supabase API Bilgileri (Kendi Supabase projenizden alın)
$supabase_url = 'https://qcxfgedwwjfypwtsdzme.supabase.co'; // Proje URL'niz
$supabase_key = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InFjeGZnZWR3d2pmeXB3dHNkem1lIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NjQ1NzkzNzAsImV4cCI6MjA4MDE1NTM3MH0.RmxwNeq2_m7fOgrmycaUSfPl38qNf7mfvcZageULwU4'; // Anon (public) Key'iniz

// Genel cURL Fonksiyonu (Tüm API istekleri için)
function supabase_api_request($method, $path, $data = [], $token = null) {
    global $supabase_url, $supabase_key;

    $url = $supabase_url . '/rest/v1/' . $path;
    $headers = [
        'Content-Type: application/json',
        'apikey: ' . $supabase_key,
        'Authorization: Bearer ' . ($token ?? $supabase_key),
        'Prefer: return=representation' // <--- BU SATIRI EKLEDİK: Başarılı POST işlemlerinde veriyi geri döndürür.
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    switch ($method) {
        case 'POST':
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            break;
        // ... (diğer case yapıları aynı kalacak)
        case 'GET':
            if (!empty($data)) {
                $url .= '?' . http_build_query($data);
                curl_setopt($ch, CURLOPT_URL, $url);
            }
            break;
        case 'DELETE':
             curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
             if (!empty($data)) {
                $url .= '?' . http_build_query($data);
                curl_setopt($ch, CURLOPT_URL, $url);
            }
            break;
    }

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    // Eğer HTTP kodu 200 veya 201 ise (başarılıysa) veriyi döndür
    if ($http_code >= 200 && $http_code < 300) {
        return json_decode($response, true);
    }

    return null; // Sadece gerçek hata durumlarında (400+) null döndürür
}
?>