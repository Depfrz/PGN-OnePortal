# DPPL (Deskripsi Perancangan Perangkat Lunak)

## 1. Informasi Dokumen
- Nama Sistem: **PGN DataLens**
- Versi Dokumen: 0.1
- Tanggal: 2026-01-20

## 2. Arsitektur Tingkat Tinggi
### 2.1 Komponen
- **Web App (Laravel)**: UI, autentikasi, RBAC, manajemen modul, manajemen user.
- **Database (MySQL)**: penyimpanan user, role/permission, modul, akses modul.
- **Python Engine**: proses analitik berbasis modul (dipanggil dari Laravel).

### 2.2 Alur Data Ringkas
1. User login ke Laravel.
2. Laravel menentukan menu/modul yang tampil berdasarkan role dan/atau `module_access`.
3. Jika modul tertentu dijalankan (on-demand/terjadwal), Laravel memanggil engine Python dengan parameter `module`.
4. Engine Python mengembalikan JSON ke STDOUT.
5. Laravel menampilkan status dan (target) menyimpan ringkasan hasil ke DB.

## 3. Desain Otorisasi (Role & Permission)
### 3.1 Model RBAC
Menggunakan Spatie Permission.

Role:
- User
- SuperUser
- Supervisor
- Admin

Aturan bisnis:
- **User/SuperUser**: akses modul bersifat scoped (hanya modul yang diberikan Admin).
- **Supervisor/Admin**: akses seluruh modul.
- **Admin**: satu-satunya role yang boleh manajemen user dan assignment role.

Catatan implementasi:
- Data role/permission diisi melalui seeder dan dapat diegakkan melalui middleware/policy.
- Untuk akses scoped per modul, gunakan tabel `module_access` sebagai sumber keputusan.

## 4. Desain Data

### 4.1 Tabel Inti
#### 4.1.1 `users`
- `id` (PK)
- `name`
- `email` (unique)
- `email_verified_at` (nullable)
- `password`
- `remember_token`
- `created_at`, `updated_at`

#### 4.1.2 `roles`, `permissions`, pivot Spatie
- `roles`, `permissions`
- `model_has_roles`
- `model_has_permissions`
- `role_has_permissions`

#### 4.1.3 `modules`
- `id` (PK)
- `name`
- `slug` (unique)
- `url` (nullable)
- `icon` (nullable)
- `status` (boolean, default true)
- `created_at`, `updated_at`

#### 4.1.4 `module_access`
- `id` (PK)
- `user_id` (FK → users.id)
- `module_id` (FK → modules.id)
- `can_read` (bool)
- `can_write` (bool)
- `can_delete` (bool)
- Unique: (`user_id`, `module_id`)

### 4.2 Relasi Eloquent
- `User` ↔ `ModuleAccess` (one-to-many)
- `Module` ↔ `ModuleAccess` (one-to-many)

## 5. Desain Routing & Halaman
### 5.1 Halaman Publik
- `/` (landing)
- `/login`, `/register`, `/forgot-password`, `/reset-password`

### 5.2 Halaman Terproteksi
- `/dashboard`
- `/history`
- `/integrasi-sistem` (list modul)
- `/integrasi-sistem/tambah` (tambah modul)
- `/management-user` (manajemen user)

### 5.3 Kontrol Akses (target)
- Middleware `auth` untuk semua halaman terproteksi.
- Tambahan gate/permission:
  - `manage users` untuk halaman manajemen user.
  - `manage modules` untuk tambah modul.
  - `view all modules` untuk melihat semua modul (Supervisor/Admin).
  - User/SuperUser menggunakan query `module_access` untuk menentukan modul yang tampil.

## 6. Desain UI/UX
### 6.1 Prinsip Perapihan UI
- Konsistensi spacing (mis. 8px grid), ukuran heading, warna, dan radius.
- Komponen reusable: card, button, input, modal, table.
- Responsif minimal untuk layar laptop (dashboard internal).

### 6.2 Struktur Layout
- Layout dashboard dengan sidebar (menu utama) + area konten.
- Navbar menampilkan identitas user dan menu profil/logout.

## 7. Desain Integrasi Python
### 7.1 Kontrak Input/Output
- Input: `module` (slug modul)
- Output: JSON ke STDOUT
  - sukses: `{ "status": "success", "module": "...", ... }`
  - gagal: `{ "status": "error", "message": "..." }`

### 7.2 Mekanisme Eksekusi
- Laravel menjalankan proses Python sebagai child process.
- Timeout dan error handling wajib ada (log dan return status).

### 7.3 Penyimpanan Hasil (target)
- Minimum: simpan status run, timestamp, dan ringkasan hasil.
- Opsional: simpan output penuh (JSON) untuk audit.

## 8. Desain Deploy (Ringkas)
- Web server: Nginx/Apache + PHP-FPM.
- Queue worker (bila dipakai): `php artisan queue:work`.
- Scheduler (bila dipakai): `php artisan schedule:run`.
- Node build untuk asset: `npm run build`.
- Runtime Python tersedia untuk engine.

