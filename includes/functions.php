<?php

// URL ve dosya isimleri için güvenli bir string oluşturan yardımcı fonksiyon
function slugify($text) {
    // Türkçe karakterleri dönüştür
    $turkish = array('ı', 'İ', 'ğ', 'Ğ', 'ü', 'Ü', 'ş', 'Ş', 'ö', 'Ö', 'ç', 'Ç');
    $english = array('i', 'i', 'g', 'g', 'u', 'u', 's', 's', 'o', 'o', 'c', 'c');
    $text = str_replace($turkish, $english, $text);

    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);
    if (empty($text)) {
        return 'n-a';
    }
    return $text;
}

?>