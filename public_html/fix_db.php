<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

// Deteksi path (Local vs Hosting dengan folder laravel_app)
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    // Struktur Standar / Local
    require __DIR__ . '/../vendor/autoload.php';
    $app = require_once __DIR__ . '/../bootstrap/app.php';
} elseif (file_exists(__DIR__ . '/../laravel_app/vendor/autoload.php')) {
    // Struktur Hosting (laravel_app terpisah)
    require __DIR__ . '/../laravel_app/vendor/autoload.php';
    $app = require_once __DIR__ . '/../laravel_app/bootstrap/app.php';
} else {
    die("❌ Error: Tidak dapat menemukan file 'vendor/autoload.php' atau 'bootstrap/app.php'. Pastikan struktur folder benar.");
}

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

echo "<h1>Diagnosa & Perbaikan Database List Pengawasan</h1>";

// --- BAGIAN 1: CEK STRUKTUR DATABASE ---
echo "<h3>1. Pengecekan Struktur Tabel</h3>";

// 1. Cek Tabel 'keterangan_options'
if (!Schema::hasTable('keterangan_options')) {
    Schema::create('keterangan_options', function (Blueprint $table) {
        $table->id();
        $table->string('name')->unique();
        $table->timestamps();
    });
    echo "<p style='color:green'>✔ Tabel 'keterangan_options' berhasil dibuat.</p>";
} else {
    echo "<p style='color:blue'>ℹ Tabel 'keterangan_options' sudah ada.</p>";
}

// 2. Cek Tabel 'pengawas_keterangan'
if (!Schema::hasTable('pengawas_keterangan')) {
    Schema::create('pengawas_keterangan', function (Blueprint $table) {
        $table->id();
        $table->foreignId('pengawas_id')->constrained('pengawas')->onDelete('cascade');
        $table->foreignId('keterangan_option_id')->constrained('keterangan_options')->onDelete('cascade');
        $table->string('bukti_path')->nullable();
        $table->string('bukti_original_name')->nullable();
        $table->string('bukti_mime')->nullable();
        $table->integer('bukti_size')->nullable();
        $table->timestamp('bukti_uploaded_at')->nullable();
        $table->timestamps();
    });
    echo "<p style='color:green'>✔ Tabel 'pengawas_keterangan' berhasil dibuat.</p>";
} else {
    echo "<p style='color:blue'>ℹ Tabel 'pengawas_keterangan' sudah ada.</p>";
    
    // Cek kelengkapan kolom
    $columns = ['bukti_path', 'bukti_original_name', 'bukti_mime', 'bukti_size', 'bukti_uploaded_at'];
    $missingColumns = [];
    foreach ($columns as $col) {
        if (!Schema::hasColumn('pengawas_keterangan', $col)) {
            $missingColumns[] = $col;
        }
    }
    
    if (!empty($missingColumns)) {
        Schema::table('pengawas_keterangan', function (Blueprint $table) use ($missingColumns) {
            foreach ($missingColumns as $col) {
                if ($col === 'bukti_size') {
                    $table->integer($col)->nullable();
                } elseif ($col === 'bukti_uploaded_at') {
                    $table->timestamp($col)->nullable();
                } else {
                    $table->string($col)->nullable();
                }
            }
        });
        echo "<p style='color:green'>✔ Menambahkan kolom hilang di 'pengawas_keterangan': " . implode(', ', $missingColumns) . "</p>";
    } else {
        echo "<p style='color:blue'>ℹ Semua kolom bukti di 'pengawas_keterangan' sudah lengkap.</p>";
    }
}

// 3. Cek Tabel 'pengawas_users'
if (!Schema::hasTable('pengawas_users')) {
    Schema::create('pengawas_users', function (Blueprint $table) {
        $table->id();
        $table->foreignId('pengawas_id')->constrained('pengawas')->onDelete('cascade');
        $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
        $table->timestamps();
    });
    echo "<p style='color:green'>✔ Tabel 'pengawas_users' berhasil dibuat.</p>";
} else {
    echo "<p style='color:blue'>ℹ Tabel 'pengawas_users' sudah ada.</p>";
}

// 4. Cek Tabel 'pengawas' (Kolom bukti di tabel utama)
if (Schema::hasTable('pengawas')) {
    $columns = ['bukti_path', 'bukti_original_name', 'bukti_mime', 'bukti_size', 'bukti_uploaded_at'];
    $missingColumns = [];
    foreach ($columns as $col) {
        if (!Schema::hasColumn('pengawas', $col)) {
            $missingColumns[] = $col;
        }
    }
    
    if (!empty($missingColumns)) {
        Schema::table('pengawas', function (Blueprint $table) use ($missingColumns) {
            foreach ($missingColumns as $col) {
                if ($col === 'bukti_size') {
                    $table->integer($col)->nullable();
                } elseif ($col === 'bukti_uploaded_at') {
                    $table->timestamp($col)->nullable();
                } else {
                    $table->string($col)->nullable();
                }
            }
        });
        echo "<p style='color:green'>✔ Menambahkan kolom yang hilang di 'pengawas': " . implode(', ', $missingColumns) . "</p>";
    } else {
        echo "<p style='color:blue'>ℹ Semua kolom bukti di 'pengawas' sudah lengkap.</p>";
    }
}

// 5. Cek Tabel 'notifications'
if (!Schema::hasTable('notifications')) {
    Schema::create('notifications', function (Blueprint $table) {
        $table->uuid('id')->primary();
        $table->string('type');
        $table->morphs('notifiable');
        $table->text('data');
        $table->timestamp('read_at')->nullable();
        $table->timestamps();
    });
    echo "<p style='color:green'>✔ Tabel 'notifications' berhasil dibuat.</p>";
} else {
    echo "<p style='color:blue'>ℹ Tabel 'notifications' sudah ada.</p>";
}


// --- BAGIAN 2: DIAGNOSA LOGIC ---
echo "<h3>2. Simulasi Fungsional (Mencari Error)</h3>";

try {
    // A. Cek User
    $user = \App\Models\User::first();
    if (!$user) {
        throw new Exception("Tidak ada user di database. Harap buat user dulu.");
    }
    \Illuminate\Support\Facades\Auth::login($user);
    echo "<p style='color:green'>✔ Login simulasi berhasil sebagai: <strong>{$user->name}</strong></p>";

    // B. Cek Module Access
    if (class_exists(\App\Models\Module::class)) {
        $module = \App\Models\Module::where('slug', 'list-pengawasan')->first();
        if ($module) {
             echo "<p style='color:green'>✔ Data Module 'list-pengawasan' ditemukan.</p>";
        } else {
             echo "<p style='color:orange'>⚠ Data Module 'list-pengawasan' TIDAK ditemukan. Permission mungkin bermasalah.</p>";
        }
    } else {
        echo "<p style='color:red'>❌ Class App\Models\Module tidak ditemukan.</p>";
    }

    // C. Cek Data Pengawas
    $pengawas = \Illuminate\Support\Facades\DB::table('pengawas')->first();
    if (!$pengawas) {
        $pid = \Illuminate\Support\Facades\DB::table('pengawas')->insertGetId([
            'name' => 'Proyek Diagnosa Otomatis',
            'status' => 'On Progress',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $pengawas = \Illuminate\Support\Facades\DB::table('pengawas')->where('id', $pid)->first();
        echo "<p style='color:green'>✔ Membuat data dummy pengawas ID: {$pengawas->id}</p>";
    } else {
        echo "<p style='color:green'>✔ Menggunakan sampel pengawas ID: {$pengawas->id}</p>";
    }

    // D. Simulasi Insert Keterangan
    $label = "Test Diag " . time();
    $optId = \Illuminate\Support\Facades\DB::table('keterangan_options')->insertGetId([
        'name' => $label,
        'created_at' => now(),
        'updated_at' => now()
    ]);
    
    \Illuminate\Support\Facades\DB::table('pengawas_keterangan')->insert([
        'pengawas_id' => $pengawas->id,
        'keterangan_option_id' => $optId,
        'created_at' => now(),
        'updated_at' => now()
    ]);
    echo "<p style='color:green'>✔ Simulasi Insert Database: BERHASIL.</p>";

    // E. Simulasi Notifikasi
    if (class_exists(\App\Notifications\SystemNotification::class)) {
        $recipients = collect([$user]);
        \Illuminate\Support\Facades\Notification::send($recipients, new \App\Notifications\SystemNotification(
            'update', 'List Pengawasan', 'Tes Diagnosa Notifikasi', $user->name
        ));
        echo "<p style='color:green'>✔ Simulasi Kirim Notifikasi: BERHASIL.</p>";
    } else {
        throw new Exception("Class App\Notifications\SystemNotification tidak ditemukan.");
    }

    echo "<h3 style='color:green'>✅ KESIMPULAN: Sistem Backend Berjalan Normal.</h3>";
    echo "<p>Jika Anda masih mengalami error, kemungkinan masalah ada di Browser Cache atau JavaScript.</p>";

} catch (\Throwable $e) {
    echo "<div style='background:#ffebee; border:2px solid #ef5350; padding:15px; border-radius:5px; margin-top:20px;'>";
    echo "<h3 style='color:#c62828; margin-top:0;'>❌ TERDETEKSI ERROR:</h3>";
    echo "<p><strong>Pesan Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>Lokasi:</strong> " . $e->getFile() . " (Baris " . $e->getLine() . ")</p>";
    echo "<hr>";
    echo "<h4>Stack Trace (Untuk Developer):</h4>";
    echo "<pre style='background:#fff; padding:10px; overflow:auto; max-height:300px;'>" . $e->getTraceAsString() . "</pre>";
    echo "</div>";
}
