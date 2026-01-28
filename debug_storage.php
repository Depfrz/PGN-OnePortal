<?php

// Konfigurasi Path
$appPath = __DIR__ . '/../laravel_app';
$storagePublicPath = $appPath . '/storage/app/public';
$publicStoragePath = __DIR__ . '/storage';

// File spesifik yang error (dari laporan Anda)
$testFileRelative = 'kegiatan-bukti-keterangan/5/KZaI1FHEntv47zeEhpirxcVKck0OTozlCrshY9o9.png';
$testFilePathSource = $storagePublicPath . '/' . $testFileRelative;
$testFilePathPublic = $publicStoragePath . '/' . $testFileRelative;

echo "<h1>Debug Storage & Symlink</h1>";

// 1. Cek Folder Source (Asli)
echo "<h2>1. Cek Folder Sumber (laravel_app)</h2>";
if (file_exists($storagePublicPath)) {
    echo "<p style='color:green'>[OK] Folder <code>storage/app/public</code> ditemukan.</p>";
} else {
    echo "<p style='color:red'>[ERROR] Folder <code>storage/app/public</code> TIDAK ditemukan di <code>$storagePublicPath</code>.</p>";
}

// 2. Cek Keberadaan File Fisik
echo "<h2>2. Cek File Fisik</h2>";
echo "Mencari file: <code>$testFileRelative</code><br>";
if (file_exists($testFilePathSource)) {
    echo "<p style='color:green'>[OK] File ADA di folder sumber.</p>";
    echo "Ukuran: " . filesize($testFilePathSource) . " bytes.";
} else {
    echo "<p style='color:red'>[FATAL ERROR] File TIDAK ADA di folder sumber!</p>";
    echo "<p>Artinya: Symlink mungkin benar, tapi filenya memang tidak ada (belum terupload/terhapus).</p>";
    echo "<p>Path yang dicek: <code>$testFilePathSource</code></p>";
}

// 3. Cek Symlink
echo "<h2>3. Cek Shortcut (Symlink) di public_html</h2>";
if (is_link($publicStoragePath)) {
    echo "<p style='color:green'>[OK] 'storage' adalah Symlink.</p>";
    echo "Mengarah ke: <code>" . readlink($publicStoragePath) . "</code>";
} else {
    if (is_dir($publicStoragePath)) {
        echo "<p style='color:orange'>[WARNING] 'storage' adalah FOLDER BIASA (Bukan Symlink).</p>";
        echo "<p>Ini penyebabnya! Server tidak bisa membaca file dari folder aplikasi karena ini bukan shortcut.</p>";
    } else {
        echo "<p style='color:red'>[ERROR] 'storage' tidak ditemukan di public_html.</p>";
    }
}

// 4. Tes Baca File via Public
echo "<h2>4. Tes Akses via Public</h2>";
if (file_exists($testFilePathPublic)) {
    echo "<p style='color:green'>[OK] File terdeteksi via public_html (Siap diakses).</p>";
    echo "<a href='/storage/$testFileRelative' target='_blank'>Klik untuk buka file</a>";
} else {
    echo "<p style='color:red'>[FAIL] File tidak bisa diakses via public_html.</p>";
}
