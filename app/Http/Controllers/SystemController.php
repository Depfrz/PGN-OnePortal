<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use App\Models\BukuSakuDocument;
use App\Models\AuditLog;

class SystemController extends Controller
{
    public function welcome()
    {
        return view('welcome');
    }

    public function fixHosting()
    {
        try {
            Artisan::call('optimize:clear');
            return "<h1>BERHASIL!</h1> <p>Cache hosting sudah dibersihkan (Clear All).</p> <p>Gunakan ini jika ada error atau perubahan tidak muncul.</p> <p>Agar website LEBIH CEPAT, silakan akses: <a href='/optimize-hosting'>/optimize-hosting</a></p>";
        } catch (\Exception $e) {
            return "GAGAL: " . $e->getMessage();
        }
    }

    public function optimizeHosting()
    {
        try {
            // Manual optimization for Shared Hosting
            
            // 1. Clear Bootstrap Cache
            $bootstrapFiles = glob(base_path('bootstrap/cache/*.php'));
            foreach ($bootstrapFiles as $file) {
                @unlink($file);
            }

            // 2. Clear Views
            $viewFiles = glob(storage_path('framework/views/*'));
            foreach ($viewFiles as $file) {
                if (is_file($file) && basename($file) !== '.gitignore') @unlink($file);
            }

            // 3. Clear Cache (Recursive)
            $deleteDir = function($dirPath) use (&$deleteDir) {
                if (!is_dir($dirPath)) return;
                $files = glob($dirPath . '/*', GLOB_MARK);
                foreach ($files as $file) {
                    if (is_dir($file)) {
                        $deleteDir($file);
                    } else {
                        @unlink($file);
                    }
                }
                @rmdir($dirPath);
            };

            $cacheDataPath = storage_path('framework/cache/data');
            if (is_dir($cacheDataPath)) {
                $items = glob($cacheDataPath . '/*', GLOB_MARK);
                foreach ($items as $item) {
                     if (is_dir($item)) {
                         $deleteDir($item);
                     } else {
                         @unlink($item);
                     }
                }
            }

            return "<h1>OPTIMASI BERHASIL!</h1> <p>Cache (Bootstrap, Views, Storage) telah dibersihkan secara manual.</p>";
        } catch (\Exception $e) {
            return "<h1>Optimasi Gagal</h1> <p>Error: " . $e->getMessage() . "</p>";
        }
    }

    public function fixHistoryBukuSaku()
    {
        $documents = BukuSakuDocument::all();
        $count = 0;
        foreach ($documents as $doc) {
            // Backfill Create Log
            $exists = AuditLog::where('module', 'Buku Saku')
                ->where('description', 'Menambahkan dokumen: ' . $doc->title)
                ->exists();
            
            if (!$exists) {
                AuditLog::create([
                    'user_id' => $doc->user_id,
                    'action' => 'create',
                    'module' => 'Buku Saku',
                    'description' => 'Menambahkan dokumen: ' . $doc->title,
                    'created_at' => $doc->created_at,
                    'updated_at' => $doc->created_at,
                ]);
                $count++;
            }
            
            // Backfill Update Log
            if ($doc->updated_at != $doc->created_at) {
                 $existsUpdate = AuditLog::where('module', 'Buku Saku')
                    ->where('description', 'like', 'Mengupdate dokumen: ' . $doc->title . '%')
                    ->exists();
                    
                 if (!$existsUpdate) {
                    AuditLog::create([
                        'user_id' => $doc->user_id, // Default to creator for old data
                        'action' => 'update',
                        'module' => 'Buku Saku',
                        'description' => 'Mengupdate dokumen: ' . $doc->title . ' (Dipulihkan Sistem)',
                        'created_at' => $doc->updated_at,
                        'updated_at' => $doc->updated_at,
                    ]);
                    $count++;
                 }
            }
        }
        return redirect()->route('buku-saku.history')->with('success', "Berhasil memulihkan $count riwayat aktivitas.");
    }
}
