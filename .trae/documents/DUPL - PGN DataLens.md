# DUPL (Dokumen Uji Perangkat Lunak)

## 1. Informasi Dokumen
- Nama Sistem: **PGN DataLens**
- Versi Dokumen: 0.1
- Tanggal: 2026-01-20

## 2. Tujuan Pengujian
- Memastikan login dan sesi berjalan stabil.
- Memastikan kontrol akses 4 role sesuai kebutuhan bisnis.
- Memastikan manajemen modul dan manajemen user hanya tersedia untuk role yang berhak.
- Memastikan integrasi engine Python dapat dieksekusi dan menghasilkan output terstruktur.
- Memastikan tampilan UI rapi dan konsisten pada halaman utama.

## 3. Ruang Lingkup Pengujian
### 3.1 In-Scope
- Autentikasi: login/logout, reset password.
- Otorisasi: akses halaman, menu, dan aksi berdasarkan role.
- Modul: tampilnya daftar modul sesuai hak akses.
- Eksekusi analytics Python (via CLI).

### 3.2 Out-of-Scope
- Load test skala besar dan hardening produksi tingkat lanjut (dikerjakan terpisah).

## 4. Lingkungan Uji
- OS: Windows (dev), Linux (target prod)
- PHP: >= 8.2
- Framework: Laravel
- DB: MySQL (prod) / SQLite (dev) bila diperlukan
- Node: untuk build asset
- Python: runtime untuk engine

## 5. Data Uji
Gunakan akun uji berikut (default password: `password`):
- Admin: `admin@pgn.co.id`
- Supervisor: `supervisor@pgn.co.id`
- SuperUser: `superuser@pgn.co.id`
- User: `user@pgn.co.id`

## 6. Matriks Akses yang Divalidasi
| Aksi | User | SuperUser | Supervisor | Admin |
|---|---:|---:|---:|---:|
| Login | Ya | Ya | Ya | Ya |
| Lihat dashboard | Ya | Ya | Ya | Ya |
| Lihat modul | Scoped | Scoped | Semua | Semua |
| Input data | Tidak | Scoped | Semua | Semua |
| Delete data | Tidak | Tidak | Semua | Semua |
| Tambah modul | Tidak | Tidak | Ya | Ya |
| Manajemen user | Tidak | Tidak | Tidak | Ya |

## 7. Test Case

### TC-AUTH-01 Login berhasil
- Prasyarat: user terdaftar.
- Langkah:
  1. Buka halaman login.
  2. Masukkan email & password valid.
  3. Submit.
- Hasil yang diharapkan:
  - Redirect ke dashboard.
  - Sesi aktif.

### TC-AUTH-02 Login gagal (password salah)
- Langkah:
  1. Masukkan email valid + password salah.
  2. Submit.
- Hasil yang diharapkan:
  - Muncul pesan error.
  - Tidak membuat sesi.

### TC-RBAC-01 User tidak melihat modul yang tidak di-assign
- Prasyarat: pada data `module_access`, User hanya diberi 1 modul.
- Langkah:
  1. Login sebagai User.
  2. Buka halaman daftar modul.
- Hasil yang diharapkan:
  - Hanya modul yang di-assign yang muncul.

### TC-RBAC-02 SuperUser dapat input pada modul yang di-assign
- Prasyarat: SuperUser diberi `can_write=true` pada salah satu modul.
- Langkah:
  1. Login sebagai SuperUser.
  2. Buka modul yang di-assign.
  3. Lakukan input data.
- Hasil yang diharapkan:
  - Input berhasil.
  - Aksi input ditolak pada modul yang tidak di-assign.

### TC-RBAC-03 Supervisor punya akses penuh ke semua modul
- Langkah:
  1. Login sebagai Supervisor.
  2. Buka daftar modul.
- Hasil yang diharapkan:
  - Semua modul tampil.
  - Bisa tambah modul.
  - Bisa input dan delete data.

### TC-RBAC-04 Admin dapat manajemen user
- Langkah:
  1. Login sebagai Admin.
  2. Buka halaman manajemen user.
  3. Buat user baru dan assign role.
- Hasil yang diharapkan:
  - CRUD user berhasil.
  - Role tersimpan.

### TC-MOD-01 Tambah modul (Supervisor/Admin)
- Langkah:
  1. Login sebagai Supervisor atau Admin.
  2. Tambah modul dengan `name`, `slug` unik.
- Hasil yang diharapkan:
  - Modul baru tersimpan.
  - `slug` duplikat ditolak.

### TC-MOD-02 Tambah modul ditolak (User/SuperUser)
- Langkah:
  1. Login sebagai User atau SuperUser.
  2. Akses endpoint/halaman tambah modul.
- Hasil yang diharapkan:
  - Ditolak (403) atau redirect dengan pesan.

### TC-PY-01 Eksekusi analytics engine berhasil
- Langkah:
  1. Jalankan perintah: `php artisan analytics:run <slug_modul>`.
- Hasil yang diharapkan:
  - Output JSON status `success`.
  - Tidak ada error pada log.

### TC-UI-01 Konsistensi tampilan
- Langkah:
  1. Buka dashboard, manajemen user, integrasi sistem.
  2. Verifikasi spacing, alignment, dan komponen.
- Hasil yang diharapkan:
  - Tidak ada overflow layout.
  - Komponen konsisten antar halaman.

## 8. Kriteria Lulus (Exit Criteria)
- Semua test case kritikal (AUTH dan RBAC) lulus.
- Tidak ada error console pada halaman utama.
- Tidak ada error 500 pada halaman terproteksi.
- Integrasi Python dapat berjalan minimal untuk 1 modul.

## 9. Catatan Risiko
- Jika enforcement RBAC (middleware/policy) belum diterapkan pada route dan UI, beberapa test case RBAC bisa gagal walaupun data role/permission sudah ada.
- Jika runtime Python atau dependensi engine tidak tersedia di server deploy, test case integrasi engine akan gagal.

