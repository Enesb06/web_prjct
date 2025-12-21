<?php
// Oturumu başlat (her sayfada gerekecek)
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Supabase API Bilgileri (Kendi Supabase projenizden alın)
$supabase_url = 'https://qcxfgedwwjfypwtsdzme.supabase.co'; // Proje URL'niz
$supabase_key = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InFjeGZnZWR3d2pmeXB3dHNkem1lIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NjQ1NzkzNzAsImV4cCI6MjA4MDE1NTM3MH0.RmxwNeq2_m7fOgrmycaUSfPl38qNf7mfvcZageULwU4'; // Anon (public) Key'iniz

function supabase_api_request($method, $path, $data = [], $token = null) {
    global $supabase_url, $supabase_key;

    $url = $supabase_url . '/rest/v1/' . $path;
    $headers = [
        'Content-Type: application/json',
        'Prefer: return=representation', // YENİ: POST ve PATCH sonrası eklenen/güncellenen veriyi geri döndürür.
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
        
        // YENİ PATCH METODU
         case 'PATCH':
            // $path değişkeni zaten 'users?id=eq.123' gibi tam yolu içeriyor.
            // Bu yüzden $url'i doğrudan kullanıyoruz.
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
        // echo "Supabase API Hatası: " . $response;
        return null;
    }

    return json_decode($response, true);
}
include_once 'functions.php'; 
?>