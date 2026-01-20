Saya akan mengimplementasikan halaman **Management User** beserta fitur-fitur interaktifnya (Modal Tambah, Edit, Hapus, dan Reset Password) agar sesuai dengan desain Figma yang dilampirkan.

Berikut adalah rencana implementasinya:

### 1. Persiapan Routing & Controller
*   Membuat route baru `/management-user` di `routes/web.php`.
*   Menambahkan method controller (jika diperlukan) atau menggunakan closure untuk mengirimkan data dummy agar tampilan tabel terisi sesuai desain.

### 2. Implementasi Tampilan Utama (Main View)
*   Membuat file view baru `resources/views/management-user.blade.php`.
*   Menggunakan layout `dashboard-layout` yang sudah dibuat sebelumnya.
*   **Bagian Header**: Breadcrumb "Beranda -> Management User".
*   **Bagian Pencarian**: Input field besar "Cari Nama ......".
*   **Bagian Tabel**:
    *   Header kolom: Nama/Email, Password, Instansi, Jabatan, Role, Status, Hak Akses, Aksi.
    *   Baris data: Menampilkan data dummy yang mirip dengan desain (contoh: User "Falih").
    *   Kolom Aksi: Ikon Kunci (Reset Password) dan Ikon Sampah (Hapus).
    *   Tombol "Tambah User baru" di pojok kanan atas tabel.

### 3. Implementasi Modal (Alpine.js)
Saya akan menggunakan **Alpine.js** untuk membuat modal yang interaktif tanpa perlu reload halaman, sesuai dengan screenshot popup:
*   **Modal Tambah User**: Form input lengkap.
*   **Modal Reset Password**: Popup dengan input "Password baru" dan tombol "Setuju/Batal" (Referensi: `screenshot_17_1244.png`).
*   **Modal Hapus User**: Popup konfirmasi hapus dengan tombol "Setuju/Batal" (Referensi: `screenshot_17_1650.png`).

### 4. Styling & Aset
*   Menggunakan Tailwind CSS untuk meniru styling presisi dari Figma (Warna, Shadow, Radius, Typography).
*   Menggunakan ikon SVG yang konsisten untuk tombol aksi.

### 5. Verifikasi
*   Memastikan layout responsif dan sesuai dengan screenshot `screenshot_9_84.png` (Desktop View).
*   Memastikan interaksi modal berjalan lancar.

Apakah Anda setuju dengan rencana ini? Jika ya, saya akan mulai menulis kodenya.