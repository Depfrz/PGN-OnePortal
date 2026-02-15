## Tujuan
Menerapkan 3 permintaan:
1) List Pengawasan: tambah kolom Divisi + rapikan layout + inline edit nama/divisi via ikon pen.
2) Dashboard: tampilkan gambar modul dari URL (favicon) jika icon file tidak ada (seperti Integrasi Sistem).
3) Logout: UI pop-up logout dibuat lebih modern.

## 1) List Pengawasan
### 1.1 Tambah field divisi di database
- Tambah kolom `divisi` pada tabel `pengawas`.
- Implementasi dengan **migration baru** (lebih aman daripada mengubah migration lama):
  - Buat migration `add_divisi_to_pengawas_table` menambah `divisi` (string) nullable.

### 1.2 Update backend (controller)
- Update query di [ListPengawasanController.php](file:///c:/Users/Ahmad%20Falih%20Agus/Documents/PGN-DataLens-Python-Laravel-/app/Http/Controllers/ListPengawasanController.php):
  - `index()`: select `divisi` dan masukkan ke `items` agar tampil di UI.
  - `store()`: validasi `divisi` (string max 255) dan simpan ke DB.
- Tambah endpoint update nama/divisi:
  - Tambah route PATCH baru di [web.php](file:///c:/Users/Ahmad%20Falih%20Agus/Documents/PGN-DataLens-Python-Laravel-/routes/web.php) misal: `/list-pengawasan/{id}`.
  - Tambah method `updatePengawas()` untuk update `name` dan `divisi` (cek permission `canWriteForModule`).

### 1.3 Update UI List Pengawasan
- Di [list-pengawasan/index.blade.php](file:///c:/Users/Ahmad%20Falih%20Agus/Documents/PGN-DataLens-Python-Laravel-/resources/views/list-pengawasan/index.blade.php):
  - Modal “Tambah Pengawas Baru”: tambah input **Divisi** di bawah Nama, simpan ke `newPengawas.divisi` dan kirim saat POST.
  - Rapikan spacing kolom agar tidak tabrakan:
    - Ubah header grid dan row grid untuk memasukkan kolom Divisi.
    - Usulan pembagian grid 12 kolom: Nama(3) + Divisi(3) + Tanggal(2) + Status(2) + Keterangan(2). Ini menjaga semua field tetap ada (tanggal tetap tampil realtime) dan lebih rapat/rapi.
    - Sesuaikan area “Keterangan” agar `min-w-0` dan teks bisa wrap/truncate supaya tidak menabrak tombol status.
  - Inline edit nama/divisi:
    - Tambah ikon pen di sebelah nama.
    - Saat diklik, baris masuk mode edit:
      - Nama berubah jadi input.
      - Divisi berubah jadi input.
      - Tampil tombol simpan/batal kecil (tanpa modal/popup).
    - Simpan memanggil PATCH `/list-pengawasan/{id}` lalu update state `items`.

## 2) Dashboard: favicon modul dari URL
- Di [dashboard.blade.php](file:///c:/Users/Ahmad%20Falih%20Agus/Documents/PGN-DataLens-Python-Laravel-/resources/views/dashboard.blade.php):
  - Pada badge modul, tampilkan:
    - Jika `icon` adalah path file (storage) → tampilkan gambar.
    - Jika tidak ada icon file dan `url` ada → tampilkan favicon via layanan yang sama seperti Integrasi Sistem (Google s2 favicon).
    - Jika tidak ada keduanya → fallback ke SVG icon sekarang.
  - Logika dibuat mirip [integrasi-sistem/index.blade.php](file:///c:/Users/Ahmad%20Falih%20Agus/Documents/PGN-DataLens-Python-Laravel-/resources/views/integrasi-sistem/index.blade.php#L65-L77).

## 3) Logout: modal lebih modern
- Modernisasi popup logout di 2 tempat agar konsisten:
  - [layouts/dashboard.blade.php](file:///c:/Users/Ahmad%20Falih%20Agus/Documents/PGN-DataLens-Python-Laravel-/resources/views/layouts/dashboard.blade.php) (modal logoutConfirmOpen pada layout dashboard).
  - [layouts/navigation.blade.php](file:///c:/Users/Ahmad%20Falih%20Agus/Documents/PGN-DataLens-Python-Laravel-/resources/views/layouts/navigation.blade.php#L109-L154) (modal logoutConfirmOpen bawaan navigation).
- Perubahan UI:
  - Panel lebih clean (rounded-2xl, shadow, border halus), ada ikon peringatan, judul + deskripsi singkat.
  - Tombol: “Logout” (merah) dan “Batal” (abu) dengan ukuran lebih proporsional.
  - Pastikan dark mode enak dipandang.

## Verifikasi
- Jalankan migration untuk kolom `divisi` dan pastikan tidak error.
- Cek manual:
  - Tambah Pengawas: nama+divisi tersimpan, tanggal realtime tampil.
  - Inline edit: klik ikon pen → edit → simpan/batal tanpa modal.
  - Spacing: status/keterangan tidak overlap (sesuai screenshot).
  - Dashboard: favicon muncul untuk modul URL.
  - Logout modal: tampil modern dan berfungsi.
- Jalankan test suite (`php artisan test`).