<?php
/**
 * Script untuk membersihkan cache Laravel di Shared Hosting
 * Upload file ini ke folder public_html Anda.
 * Akses via browser: https://sanksi.my.id/cleaner.php
 */

// Konfigurasi Path
// Asumsi: folder 'laravel_app' berada satu level di atas 'public_html'
$laravelAppPath = __DIR__ . '/../laravel_app';

// Cek apakah path benar
if (!file_exists($laravelAppPath . '/bootstrap/app.php')) {
    echo "<h1>Error Path</h1>";
    echo "Tidak dapat menemukan folder laravel_app di: " . realpath(__DIR__ . '/../') . "/laravel_app<br>";
    echo "Mohon edit file ini dan sesuaikan variabel <code>\$laravelAppPath</code> di baris 9.";
    exit;
}

// Bootstrap Laravel
require $laravelAppPath . '/vendor/autoload.php';
$app = require_once $laravelAppPath . '/bootstrap/app.php';

// Inisialisasi Kernel Console
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

echo "<html><body style='font-family: monospace; background: #f0f0f0; padding: 20px;'>";
echo "<div style='background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);'>";
echo "<h2 style='color: #2563eb; margin-top: 0;'>Laravel Cache Cleaner</h2>";
echo "<pre style='background: #1e293b; color: #e2e8f0; padding: 15px; border-radius: 6px; overflow-x: auto;'>";

$commands = [
    'optimize:clear', // Membersihkan semua cache (config, route, view, event)
];

foreach ($commands as $cmd) {
    echo "<span style='color: #fbbf24;'>$ php artisan $cmd</span>\n";
    try {
        $status = $kernel->call($cmd);
        echo $kernel->output();
    } catch (\Exception $e) {
        echo "<span style='color: #ef4444;'>Error: " . $e->getMessage() . "</span>\n";
    }
    echo "\n";
}

echo "</pre>";
echo "<p style='color: #059669; font-weight: bold;'>✅ Proses selesai. Cache berhasil dibersihkan.</p>";
echo "<p>Silakan coba akses halaman yang sebelumnya error.</p>";
echo "<p style='font-size: 0.9em; color: #64748b;'>Note: Demi keamanan, sebaiknya hapus file <code>cleaner.php</code> ini dari hosting setelah selesai digunakan.</p>";
echo "</div></body></html>";
