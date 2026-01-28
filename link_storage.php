<?php

// Konfigurasi Path (DIUPDATE)
// Asumsi struktur folder hosting:
// /home/user/public_html  <-- File ini ditaruh di sini
// /home/user/laravel_app  <-- Folder aplikasi Anda

// Target yang benar (lokasi fisik file):
// Naik satu level dari public_html (..), lalu masuk ke laravel_app/storage/app/public
$targetFolder = __DIR__ . '/../laravel_app/storage/app/public';

// Link yang akan dibuat (shortcut):
// Di dalam folder public_html/storage
$linkFolder = __DIR__ . '/storage';

echo "<h1>Perbaikan Symlink Storage (Mode: ../laravel_app)</h1>";
echo "<p>Target Folder (Sumber File): <code>" . realpath(__DIR__ . '/../laravel_app') . "/storage/app/public</code></p>";
echo "<p>Link Folder (Shortcut): <code>" . $linkFolder . "</code></p>";

// 1. Cek folder target
if (!file_exists($targetFolder)) {
    echo "<h3 style='color:red'>ERROR: Folder target tidak ditemukan!</h3>";
    echo "<p>Sistem mencoba mengakses: <code>$targetFolder</code></p>";
    echo "<p>Pastikan nama folder aplikasi Anda di luar public_html benar-benar tertulis <strong>laravel_app</strong> (huruf kecil semua, tanpa spasi).</p>";
    die();
}

// 2. Bersihkan shortcut lama jika ada
if (file_exists($linkFolder)) {
    if (is_link($linkFolder)) {
        unlink($linkFolder); // Hapus symlink lama
        echo "<p>Symlink lama dihapus.</p>";
    } elseif (is_dir($linkFolder)) {
        // Coba rename folder storage asli (bukan symlink) jadi storage_backup biar aman
        rename($linkFolder, __DIR__ . '/storage_backup_' . time());
        echo "<p>Folder 'storage' yang bukan symlink dipindahkan ke backup.</p>";
    }
}

// 3. Buat Symlink Baru
if (symlink($targetFolder, $linkFolder)) {
    echo "<h3 style='color:green'>SUKSES: Symlink berhasil dibuat!</h3>";
    echo "<p>Sekarang file di <code>laravel_app</code> sudah terhubung ke <code>public_html</code>.</p>";
    echo "<p>Silakan refresh halaman website Anda dan coba klik 'Lihat' lagi.</p>";
} else {
    echo "<h3 style='color:red'>GAGAL: Tidak bisa membuat symlink.</h3>";
    echo "<p>Kemungkinan izin akses dibatasi oleh hosting.</p>";
}
