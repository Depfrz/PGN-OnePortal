<?php

// Konfigurasi Path
$targetFolder = __DIR__ . '/../laravel_app/storage/app/public';
$linkFolder = __DIR__ . '/storage';

echo "<h1>FIX STORAGE SYMLINK (FORCE MODE)</h1>";

// 1. Cek Folder Storage "Pengganggu"
if (file_exists($linkFolder)) {
    if (is_dir($linkFolder) && !is_link($linkFolder)) {
        echo "<p style='color:orange'>[FOUND] Folder 'storage' biasa ditemukan. Mencoba menghapus...</p>";
        
        // Coba hapus folder secara rekursif
        // (Hati-hati: ini akan menghapus isi folder storage di public_html, 
        // tapi TIDAK MENGHAPUS data asli di laravel_app)
        
        // Rename dulu biar aman (backup)
        $backupName = __DIR__ . '/storage_moved_' . time();
        if (rename($linkFolder, $backupName)) {
             echo "<p style='color:green'>[SUCCESS] Folder 'storage' berhasil dipindahkan ke: $backupName</p>";
        } else {
             echo "<p style='color:red'>[FAIL] Gagal memindahkan folder. Coba hapus manual lewat File Manager!</p>";
             die("Proses dihentikan.");
        }
    } elseif (is_link($linkFolder)) {
        echo "<p>[INFO] Symlink sudah ada, menghapus symlink lama...</p>";
        unlink($linkFolder);
    }
}

// 2. Buat Symlink Baru
echo "<p>Membuat symlink baru...</p>";
if (symlink($targetFolder, $linkFolder)) {
    echo "<h2 style='color:green'>[BERHASIL] Symlink Sukses Dibuat!</h2>";
    echo "<p>Sekarang file gambar seharusnya sudah muncul.</p>";
    echo "<p><a href='/debug_storage.php'>Klik di sini untuk Cek Ulang (Debug)</a></p>";
} else {
    echo "<h2 style='color:red'>[GAGAL] Tidak bisa membuat symlink.</h2>";
    echo "<p>Penyebab: Hosting mungkin memblokir fungsi `symlink()` PHP.</p>";
}
