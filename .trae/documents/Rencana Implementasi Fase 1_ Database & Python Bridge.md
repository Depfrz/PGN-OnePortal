# Implementasi Fase 1: Fondasi

Sesuai dengan PRD v1.2, saya akan mengeksekusi dua poin utama yang tersisa di Fase 1: **Implementasi RBAC & Skema Database** serta **Setup Python Bridge**.

## Langkah 1: Implementasi Skema Database & Model
Kita akan membangun struktur tabel untuk manajemen modul dan akses granular (Scoped Access) yang menjadi inti dari logika **SuperUser**.

1.  **Buat Migrasi Database**:
    *   Tabel `modules`: Menyimpan daftar sistem/modul (Nama, Slug, URL, Icon, Status).
    *   Tabel `module_access`: Pivot table untuk `users` dan `modules` dengan flag `can_read`, `can_write`, `can_delete`.
2.  **Buat Model Eloquent**:
    *   `App\Models\Module`
    *   `App\Models\ModuleAccess`
3.  **Definisikan Relasi**:
    *   Update model `User` untuk memiliki relasi ke `Module` melalui `ModuleAccess`.

## Langkah 2: Refinement RBAC (Spatie Permission)
Memastikan `RoleSeeder` yang sebelumnya dibuat benar-benar selaras dengan **Matriks Akses & Peran (PRD 4.1)**.
*   Verifikasi permission untuk User, SuperUser, Supervisor, dan Admin.
*   Pastikan `DatabaseSeeder` memanggil `RoleSeeder` dan membuat akun Admin default.

## Langkah 3: Setup Python Bridge (Data Engine)
Membangun jembatan komunikasi antara Laravel dan Python sesuai strategi **On-Demand Execution**.

1.  **Struktur Direktori**:
    *   Mengubah nama folder `python` menjadi `python_engine` sesuai PRD.
    *   Membuat script entry point `python_engine/main.py` yang dapat menerima argumen `--module`.
2.  **Laravel Artisan Command**:
    *   Membuat command `php artisan analytics:run {module}`.
    *   Implementasi `Illuminate\Support\Facades\Process` untuk mengeksekusi script Python secara aman.

## Langkah 4: Verifikasi & Testing
1.  Jalankan `migrate:fresh --seed`.
2.  Test insert data dummy ke tabel `modules`.
3.  Test user **SuperUser** mengakses modul (cek relasi database).
4.  Test panggil command `php artisan analytics:run --module=test` dan pastikan output Python tertangkap di Laravel.