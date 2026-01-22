## Ringkasan Perubahan
- Mengubah konsep UI dari “Pengawas” menjadi “Proyek” (tanpa menghapus struktur yang sudah ada), sehingga kolom utama menjadi: Nama Proyek, Tanggal/Waktu (real-time dari created_at), Status (OFF/On Progress/Done), Keterangan (checkbox), Bukti (upload pdf/jpg/png).

## Data & Database
- Tetap memakai tabel yang sudah ada agar aman dan cepat (tabel `pengawas` diperlakukan sebagai tabel proyek).
- Menambahkan kolom baru di tabel `pengawas` untuk bukti upload:
  - `bukti_path` (nullable)
  - `bukti_original_name` (nullable)
  - `bukti_mime` (nullable)
  - `bukti_size` (nullable)
  - `bukti_uploaded_at` (nullable)
- Tidak menampilkan lagi kolom `divisi` di UI (kolom di DB boleh tetap ada untuk kompatibilitas).

## Route/API
- Menambah endpoint upload & hapus bukti per proyek:
  - `POST /list-pengawasan/{id}/bukti` (multipart/form-data)
  - `DELETE /list-pengawasan/{id}/bukti`
- Tetap mempertahankan endpoint existing:
  - add proyek (sebelumnya store pengawas)
  - edit nama proyek (sebelumnya updatePengawas)
  - edit keterangan (updateKeterangan)
  - update status
  - hapus proyek
  - rename/hapus master keterangan

## Controller
- Update [ListPengawasanController.php](file:///c:/Users/Ahmad%20Falih%20Agus/Documents/PGN-DataLens-Python-Laravel-/app/Http/Controllers/ListPengawasanController.php) untuk:
  - `index()`: ikut mengirim informasi bukti (`bukti_path` dll) ke `items`.
  - `store()`: ganti validasi & label UI menjadi nama proyek (field `nama` tetap dipakai di request), `divisi` tidak wajib/diabaikan.
  - Tambah method `uploadBukti()`:
    - Validasi: `file` wajib, tipe `pdf/jpg/jpeg/png`, size limit (mis. 5MB).
    - Simpan ke `storage/app/public/pengawasan-bukti/{id}/...`.
    - Update kolom bukti di tabel `pengawas`.
  - Tambah method `deleteBukti()`:
    - Hapus file fisik (jika ada) dan null-kan kolom bukti.

## UI/Blade (Sederhana & Rapi)
- Update [list-pengawasan/index.blade.php](file:///c:/Users/Ahmad%20Falih%20Agus/Documents/PGN-DataLens-Python-Laravel-/resources/views/list-pengawasan/index.blade.php):
  - Judul & tombol: “List Proyek” dan “Tambah Proyek”.
  - Tabel kolom:
    - Nama Proyek (inline edit + tombol pensil)
    - Tanggal & Waktu (dari `created_at` formatted yang sudah ada)
    - Status (dropdown existing tetap)
    - Keterangan (tampilan ringkas + tombol edit)
    - Bukti (tampilkan: belum ada → tombol Upload; sudah ada → link/preview + tombol Ganti + tombol Hapus)
    - Aksi hapus proyek (ikon trash) tetap.
  - Modal Tambah Proyek:
    - Input Nama Proyek.
    - Section Keterangan:
      - Checkbox keterangan yang sudah ada.
      - Input + tombol “Tambah keterangan” untuk membuat label baru.
  - Modal Edit Keterangan:
    - Checklist master keterangan.
    - Tombol “Tambah keterangan” untuk menambah label baru.
    - Tambah tombol “Hapus keterangan proyek” (mengosongkan semua keterangan) dengan popup konfirmasi model trash seperti hapus proyek.
  - Toast sukses (ceklist) tetap dipakai untuk: tambah proyek, edit nama, edit keterangan, upload/hapus bukti.

## Penyimpanan File
- Pastikan pakai disk `public` dan URL via `asset('storage/...')`.
- Jika `storage:link` belum ada, langkah ini tetap diperlukan di environment Windows.

## Verifikasi
- Jalankan migrasi baru.
- Uji alur:
  - Tambah proyek + pilih keterangan.
  - Edit nama proyek.
  - Edit/hapus keterangan proyek.
  - Upload bukti pdf/jpg/png lalu buka link/preview.
  - Hapus bukti.
  - Hapus proyek.

Jika kamu setuju, saya lanjut implementasi perubahan di migration + controller + blade dan memastikan semua endpoint berjalan.