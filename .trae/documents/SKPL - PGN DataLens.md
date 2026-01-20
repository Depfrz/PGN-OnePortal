# SKPL (Spesifikasi Kebutuhan Perangkat Lunak)

## 1. Informasi Dokumen
- Nama Sistem: **PGN DataLens**
- Platform: Web Application
- Versi Dokumen: 0.1
- Tanggal: 2026-01-20

## 2. Ringkasan Sistem
PGN DataLens adalah portal web internal untuk mengelola akses pengguna ke kumpulan modul/sistem, menjalankan proses analitik terintegrasi (termasuk engine Python), serta menyediakan dashboard dan riwayat aktivitas.

## 3. Ruang Lingkup
### 3.1 In-Scope
- Autentikasi pengguna (login/logout, reset password).
- Manajemen role dan hak akses.
- Manajemen modul/sistem.
- Akses modul berbasis izin (scoped access) untuk role tertentu.
- Eksekusi proses analitik/engine per modul (integrasi Python).

### 3.2 Out-of-Scope (fase berikutnya bila belum tersedia)
- Integrasi SSO/LDAP.
- Audit trail lengkap level field.
- Persistensi hasil analitik ke data mart/warehouse (bila belum dirancang).

## 4. Definisi, Akronim, dan Istilah
- **Modul/Sistem**: Unit aplikasi/fitur yang dapat diakses dari portal.
- **RBAC**: Role-Based Access Control.
- **Scoped access**: Akses dibatasi pada modul tertentu yang di-assign admin.
- **CRUD**: Create, Read, Update, Delete.

## 5. Profil Pengguna dan Role
Sistem memiliki 4 role utama:

### 5.1 User
- Dapat login.
- Hanya dapat **melihat** modul/sistem yang diberikan izin oleh Admin.
- Tidak dapat input/edit/delete data.

### 5.2 SuperUser
- Sama seperti User dalam cakupan modul (hanya modul yang diizinkan Admin).
- Dapat **input data** pada modul yang diberikan izin oleh Admin.

### 5.3 Supervisor
- Dapat mengakses **seluruh modul/sistem**.
- Dapat menambah modul/sistem.
- Dapat input dan delete data pada semua modul.

### 5.4 Admin
- Sama seperti Supervisor.
- Dapat mengelola user (CRUD) dan memberikan role.

## 6. Kebutuhan Fungsional

### FR-01 Autentikasi
- Sistem menyediakan halaman login.
- Sistem memvalidasi kredensial.
- Sistem membuat sesi pengguna setelah login berhasil.
- Sistem menyediakan logout.

### FR-02 Manajemen Role
- Admin dapat menetapkan role ke user.
- Sistem menegakkan aturan akses berbasis role.

### FR-03 Manajemen Modul
- Supervisor dan Admin dapat menambah modul/sistem.
- Supervisor dan Admin dapat mengubah status modul (aktif/nonaktif).

### FR-04 Akses Modul Berbasis Izin
- User hanya melihat modul yang diizinkan Admin.
- SuperUser hanya melihat modul yang diizinkan Admin.
- Supervisor dan Admin melihat semua modul.

### FR-05 Input Data
- SuperUser dapat input data pada modul yang diizinkan Admin.
- Supervisor dan Admin dapat input data pada semua modul.
- User tidak dapat input.

### FR-06 Hapus Data
- Supervisor dan Admin dapat menghapus data pada semua modul.
- User dan SuperUser tidak dapat menghapus data.

### FR-07 Manajemen User
- Admin dapat melihat daftar user.
- Admin dapat membuat user baru.
- Admin dapat mengubah user dan role.
- Admin dapat menonaktifkan/menghapus user.

### FR-08 Riwayat Aktivitas
- Sistem menampilkan halaman riwayat aktivitas utama (minimal login/logout dan aksi manajemen).

### FR-09 Integrasi Engine Analitik
- Sistem dapat mengeksekusi proses analitik berbasis modul.
- Proses analitik menerima parameter identitas modul.
- Sistem menampilkan status sukses/gagal.

## 7. Kebutuhan Data
### 7.1 Entitas Utama
- User
- Role & Permission
- Module
- ModuleAccess (izin per user per modul: read/write/delete)

## 8. Matriks Hak Akses (Ringkas)
| Fitur | User | SuperUser | Supervisor | Admin |
|---|---:|---:|---:|---:|
| Login | Ya | Ya | Ya | Ya |
| Lihat Dashboard | Ya | Ya | Ya | Ya |
| Lihat Modul (scoped) | Ya (scoped) | Ya (scoped) | Ya (all) | Ya (all) |
| Input Data | Tidak | Ya (scoped) | Ya (all) | Ya (all) |
| Delete Data | Tidak | Tidak | Ya (all) | Ya (all) |
| Tambah Modul | Tidak | Tidak | Ya | Ya |
| Manajemen User | Tidak | Tidak | Tidak | Ya |

## 9. Kebutuhan Non-Fungsional
### 9.1 Keamanan
- Proteksi CSRF pada form.
- Session-based auth.
- Pembatasan brute-force login.
- Prinsip least privilege.

### 9.2 Kinerja
- Halaman dashboard dan daftar modul dimuat < 3 detik pada jaringan internal.

### 9.3 Ketersediaan
- Target uptime minimal 99% pada jam kerja.

### 9.4 Kualitas UI
- UI dirapikan agar konsisten walaupun referensi Figma tidak rapi.
- Standar: spacing konsisten, tipografi konsisten, komponen reusable.

## 10. Asumsi dan Ketergantungan
- Database menggunakan MySQL (atau Postgres) di environment production.
- Engine analitik Python tersedia pada server (python runtime dan dependensi).
- Modul yang dapat diakses oleh user/scoped dikelola melalui data `module_access`.

