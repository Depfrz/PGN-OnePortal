## Ringkasan Perubahan
- Halaman **List Pengawasan**: tanggal otomatis realtime saat tambah data (tanpa input manual), status punya 3 opsi (OFF/On Progress/Done) dengan tombol warna, dan pop-up rename keterangan dibuat lebih modern.
- **Dashboard**: tambah search modul di kanan teks “Selamat Datang, User.”
- **Dark mode logo**: pastikan logo PGN/“Pertamina Gas Negara” tidak berubah jadi putih (tetap warna asli).
- **Integrasi Sistem**: gambar modul otomatis ambil dari URL (logo/favicon/preview) sebagai fallback.

## 1) List Pengawasan
### 1.1 Tanggal otomatis realtime
- Ubah sumber tanggal tampil dari kolom `pengawas.tanggal` menjadi **timestamp saat data dibuat** (`created_at`).
- Implementasi:
  - Update query & mapping di [ListPengawasanController.php](file:///c:/Users/Ahmad%20Falih%20Agus/Documents/PGN-DataLens-Python-Laravel-/app/Http/Controllers/ListPengawasanController.php) agar select `created_at` dan format tampil `d-m-Y H:i`.
  - Di [index.blade.php](file:///c:/Users/Ahmad%20Falih%20Agus/Documents/PGN-DataLens-Python-Laravel-/resources/views/list-pengawasan/index.blade.php): hapus field input tanggal pada modal tambah dan jangan kirim `tanggal` saat POST.
  - Saat sukses create, UI ambil `tanggal` dari response server (bukan dari input).

### 1.2 Status OFF / On Progress / Done (tombol warna)
- Standarisasi nilai status yang disimpan: `OFF`, `On Progress`, `Done`.
- Tambah endpoint update status:
  - Tambah route PATCH baru di [web.php](file:///c:/Users/Ahmad%20Falih%20Agus/Documents/PGN-DataLens-Python-Laravel-/routes/web.php): `/list-pengawasan/{id}/status`.
  - Tambah method `updateStatus()` di [ListPengawasanController.php](file:///c:/Users/Ahmad%20Falih%20Agus/Documents/PGN-DataLens-Python-Laravel-/app/Http/Controllers/ListPengawasanController.php) dengan validasi status dan cek permission `canWriteForModule()`.
  - Update `store()` agar menerima status (opsional) dengan default `On Progress`.
- UI:
  - Di kolom status pada [index.blade.php](file:///c:/Users/Ahmad%20Falih%20Agus/Documents/PGN-DataLens-Python-Laravel-/resources/views/list-pengawasan/index.blade.php), ganti badge statis jadi **segmented buttons**:
    - OFF = merah
    - On Progress = kuning gelap (pakai palet amber)
    - Done = hijau
  - Klik tombol status akan memanggil PATCH status dan update state list.
  - Untuk data lama yang `status = 'Active'`, di UI diperlakukan sebagai `On Progress` (opsional: ditambah migrasi kecil untuk konversi data lama).

### 1.3 Pop-up rename keterangan lebih modern
- Hapus `prompt()` pada tombol rename di modal edit keterangan.
- Buat modal rename di halaman yang sama (tanpa file baru):
  - State Alpine: `renameModal`, `renameOld`, `renameNew`.
  - UI modal: input old (readonly), input new, tombol batal/simpan.
  - Saat simpan, pakai fungsi `renameOption(old, new)` yang sudah ada.
- Sekaligus merapikan warna/spacing modal edit agar lebih “clean” (konsisten dengan palet page + dark mode).

## 2) Dashboard: Search Modul di kanan “Selamat Datang, User.”
- Tambahkan search bar kecil di kanan header dashboard pada [dashboard.blade.php](file:///c:/Users/Ahmad%20Falih%20Agus/Documents/PGN-DataLens-Python-Laravel-/resources/views/dashboard.blade.php) dengan layout flex.
- Implementasi search server-side (lebih sederhana & stabil):
  - Ubah [DashboardController.php](file:///c:/Users/Ahmad%20Falih%20Agus/Documents/PGN-DataLens-Python-Laravel-/app/Http/Controllers/DashboardController.php) untuk menerima `Request $request` dan filter `$modules` dengan `where('name','like',...)` (opsional tambah `description`).
  - Search form method GET ke route `dashboard`, mempertahankan query `search`.

## 3) Darkmode Logo: warna tetap
- Titik yang berpotensi bikin logo jadi “putih” adalah penggunaan `x-application-logo` (SVG fill-current) di halaman guest.
- Rencana:
  - Ganti logo di [guest.blade.php](file:///c:/Users/Ahmad%20Falih%20Agus/Documents/PGN-DataLens-Python-Laravel-/resources/views/layouts/guest.blade.php) dari `<x-application-logo>` menjadi `<img src="...pgn-logo.png">` supaya warna logo selalu sama pada light/dark.
  - Pastikan logo di [dashboard.blade.php (layout)](file:///c:/Users/Ahmad%20Falih%20Agus/Documents/PGN-DataLens-Python-Laravel-/resources/views/layouts/dashboard.blade.php) tetap tanpa filter/invert.

## 4) Integrasi Sistem: gambar modul dari URL
- Karena form integrasi-sistem tidak mengatur upload icon, kartu modul sebaiknya punya fallback otomatis dari URL.
- Implementasi di [integrasi-sistem/index.blade.php](file:///c:/Users/Ahmad%20Falih%20Agus/Documents/PGN-DataLens-Python-Laravel-/resources/views/integrasi-sistem/index.blade.php):
  - Jika `$module->icon` ada dan terlihat seperti path file, pakai itu.
  - Kalau tidak, dan `$module->url` ada, tampilkan **favicon** berdasarkan URL (logo sesuai URL) sebagai preview ringan.
  - Jika URL kosong, pakai placeholder seperti sekarang.

## Verifikasi
- Cek manual di browser:
  - Tambah pengawas: tanggal otomatis tampil (dengan jam) dan status default benar.
  - Ubah status via tombol: tersimpan dan warna sesuai.
  - Rename keterangan: modal baru muncul, rename sukses tanpa `prompt()`.
  - Dashboard: search modul memfilter hasil.
  - Guest pages (login/verify/reset jika memakai guest layout): logo tetap berwarna.
  - Integrasi sistem: kartu modul menampilkan favicon/preview dari URL.

Jika OK, saya lanjut implementasi perubahan di file-file yang disebut di atas.