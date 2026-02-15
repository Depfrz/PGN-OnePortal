## Tujuan
- Merapikan halaman [index.blade.php](file:///c:/Users/Ahmad%20Falih%20Agus/Documents/PGN-DataLens-Python-Laravel-/resources/views/list-pengawasan/index.blade.php) agar kolom rapi, bukti tidak “kecil”, dan fitur baru sesuai request.

## Perubahan UI Utama (List)
- **Keterangan (preview max 10):** ubah tampilan kolom Keterangan dari teks join menjadi daftar (grid 2 kolom) berisi maksimal 10 item.
- **Indikator lebih dari 10:** kalau jumlah keterangan > 10, tampilkan badge `({jumlah-10}+)`/`{jumlah-10}+` sesuai format “n+” yang Anda minta.
- **Bukti lebih besar:** perlebar kolom Bukti (layout grid baru) + perbesar thumbnail/icon dan area nama file agar terbaca.
- **Layout kolom rapi:** ubah header+row dari `grid-cols-12` menjadi grid dengan kolom lebih fleksibel (mis. 16 kolom via class Tailwind “arbitrary”) supaya semua kolom (Nama, Tanggal, Deadline, Status, Keterangan, Bukti) proporsional.

## Empty State (tidak ada proyek)
- Jika `items.length === 0`, tampilkan blok empty-state di area list:
  - ilustrasi ringan (SVG inline, tanpa file baru)
  - teks: “Belum ada proyek. Klik Tambah Proyek untuk memulai.”

## Filter & Sort
- **Filter Status:** tambah dropdown filter: `All / Pending / On Progress / Done`.
  - Mapping: `Pending` di UI tetap memakai status backend `OFF`.
- **Sort:** tambah dropdown sort sederhana (mis. Terbaru/Terlama/Deadline terdekat/terjauh).
  - Agar sorting akurat, controller akan mengirim field mentah (ISO) untuk `created_at` dan `deadline` (bukan cuma string format).

## Deadline (kolom baru + editable)
- **Database:** tambah kolom `deadline` pada tabel `pengawas` (tipe `date`, nullable) via migration baru.
- **Backend:**
  - Update `index()` untuk select & mengirim `deadline` (raw untuk input + display format).
  - Tambah endpoint baru `PATCH /list-pengawasan/{id}/deadline` untuk update deadline (validasi `nullable|date`).
  - Update `store()` agar bisa menerima deadline opsional (kalau Anda setuju).
- **Frontend:**
  - Tambah kolom “Deadline” pada list.
  - Inline edit deadline (date input + tombol simpan kecil) atau klik untuk edit—mengikuti aturan role.

## Aturan Role (ikut pola yang sudah ada)
- Di controller, hitung `canWrite` menggunakan `canWriteForModule(Auth::user())` lalu kirim ke view.
- Di UI:
  - Jika `canWrite=false`, sembunyikan/nonaktifkan tombol yang memodifikasi data: Tambah Proyek, edit nama, delete, ubah status, edit keterangan, upload/hapus bukti, dan edit deadline (deadline tetap tampil read-only).

## Edit Keterangan: tambah Rename & Hapus option
- Di modal Edit Keterangan:
  - Tambah aksi **Rename** option (memanggil route yang sudah ada: `PATCH /list-pengawasan/keterangan/rename`).
  - Tambah aksi **Hapus** option (memanggil route yang sudah ada: `DELETE /list-pengawasan/keterangan`).
  - Setelah rename/hapus, sinkronkan:
    - `options` (list checkbox)
    - `selectedKeterangan`
    - semua `items[*].keterangan` agar preview di list ikut berubah.

## Verifikasi
- Jalankan migration.
- Smoke test:
  - List kosong menampilkan empty-state.
  - Filter status & sort berfungsi.
  - Keterangan preview max 10 + `n+` muncul benar.
  - Rename/hapus option keterangan mempengaruhi semua item.
  - Deadline bisa diubah (write role) dan read-only untuk non-write.
  - Bukti tampil lebih lega dan tombolnya rapi.

Jika Anda setuju, saya lanjut eksekusi perubahan di file-file: controller, routes, migration, dan blade view.