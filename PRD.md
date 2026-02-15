# Product Requirement Document (PRD) - PGN One Portal

**Nama Proyek:** PGN One Portal (DataLens Integration)
**Versi:** 1.2 (Refined & Consolidated)
**Tanggal:** 2026-01-20
**Status:** Planning Phase
**Penulis:** Senior Fullstack Software Architect
**Target:** Tim Pengembang & Stakeholders PT Pertamina Gas Negara Tbk.

---

## 1. Pendahuluan

### 1.1. Latar Belakang
PT Pertamina Gas Negara Tbk. (PGN) membutuhkan platform terpusat untuk mengintegrasikan berbagai sistem operasional dan analitik yang saat ini terpisah (silo). Integrasi ini diperlukan untuk mempercepat pengambilan keputusan berbasis data.

### 1.2. Tujuan Proyek
Membangun **PGN One Portal**, sebuah *Single Pane of Glass* yang menggabungkan:
*   **Manajemen Operasional:** Input dan pengelolaan data via Web Interface.
*   **Analisis Data Cerdas:** Visualisasi data hasil pengolahan kompleks (statistik/prediksi) menggunakan Python.

---

## 2. Spesifikasi Teknologi (Tech Stack)

### 2.1. Application Layer (Core)
*   **Framework:** Laravel 12 (PHP 8.2+)
*   **Frontend:** Blade Templates + Tailwind CSS
*   **Frontend Logic:** Alpine.js (Lightweight reactivity)
*   **Authentication:** Laravel Breeze (Customized)
*   **Authorization:** Spatie Laravel Permission (RBAC)

### 2.2. Intelligence Layer (Data Engine)
*   **Language:** Python 3.x
*   **Libraries:** Pandas (Data Processing), SQLAlchemy (ORM), MySQL Connector
*   **Fungsi:** ETL (Extract, Transform, Load), analisis statistik, prediksi.

### 2.3. Infrastruktur
*   **Database:** MySQL (Shared Source of Truth)
*   **OS:** Windows (Dev), Linux (Prod)

---

## 3. Arsitektur Sistem

### 3.1. Model Hybrid
Menggunakan pola **Shared Database Integration**:
1.  **Laravel:** Menangani User Interface, HTTP Request, dan Manajemen User.
2.  **MySQL:** Penyimpanan data terpusat.
3.  **Python Worker:** Proses latar belakang untuk komputasi berat, dipicu oleh Laravel.

### 3.2. Alur Data
1.  User input/request -> Laravel
2.  Laravel -> Database (Raw Data)
3.  Laravel (Scheduler/Trigger) -> Python Script
4.  Python Script -> Read DB -> Process -> Write DB (Summary Tables)
5.  Laravel Dashboard <- Read DB (Summary Tables)

---

## 4. Kebutuhan Fungsional

### 4.1. Matriks Akses & Peran (RBAC)
Sistem menerapkan **Strict RBAC** dengan 4 peran utama:

| Peran | Alias | Deskripsi | CRUD Data | Kelola Sistem | Kelola User |
| :--- | :--- | :--- | :--- | :---: | :---: |
| **User** | Auditor | Read-Only. | Read (Scoped) | ❌ | ❌ |
| **SuperUser** | Staff | Input Terbatas. | Write (Scoped*) | ❌ | ❌ |
| **Supervisor**| Manager | Full Control Modul. | Write (All) | ✅ | ❌ |
| **Admin** | IT | System Admin. | Write (All) | ✅ | ✅ |

> (*) **Scoped Write:** SuperUser hanya dapat mengubah data pada modul yang secara eksplisit diberikan hak aksesnya di tabel `module_access`.

### 4.2. Dashboard & Visualisasi
*   Widget interaktif (Chart.js/ApexCharts).
*   Data real-time dari hasil olahan Python.
*   Export laporan (PDF/Excel).

### 4.3. Manajemen Modul
*   Penambahan modul analisis baru tanpa merombak core system.
*   Arsitektur script Python yang modular.

---

## 5. Desain Basis Data

### 5.1. Tabel Inti
*   `users`: ID, Name, Email, Password.
*   `roles` & `permissions`: Dikelola oleh Spatie.

### 5.2. Manajemen Modul
*   `modules`:
    *   `id`, `name`, `slug`, `url`, `icon`, `status`.
*   `module_access` (Pivot Penting):
    *   `user_id`, `module_id`
    *   `can_read` (bool), `can_write` (bool), `can_delete` (bool)
    *   *Digunakan untuk validasi akses SuperUser.*

---

## 6. Frontend Specification & UI/UX

### 6.1. Layout Strategy
*   **Sidebar Navigation:**
    *   Responsive (Collapsible on mobile).
    *   Role-aware visibility (Menu items hidden if unauthorized).
    *   Style: Corporate Blue (PGN Identity).
*   **Dashboard:**
    *   Grid System (App Launcher style).
    *   Dynamic Module Cards.
*   **Forms:**
    *   Live Preview using Alpine.js.
    *   Instant validation feedback.

### 6.2. Component Library
*   `x-sidebar`: Navigasi utama dengan role check.
*   `x-module-card`: Kartu akses modul di dashboard.
*   `x-app-layout`: Wrapper utama dengan logic responsive.

---

## 7. Strategi Implementasi

### 6.1. Logika Otorisasi (Middleware/Gates)
Validasi bertingkat (Cascading Check):
1.  **Check Role:** Admin/Supervisor -> **ALLOW**.
2.  **Check Permission:** SuperUser punya izin dasar? -> **NEXT**.
3.  **Check Scope:** Cek tabel `module_access` untuk modul terkait -> **DECIDE**.

### 6.2. Integrasi Python
*   **Metode:** On-Demand Execution via Artisan Command.
*   **Command:** `php artisan analytics:run --module={slug}`
*   **Eksekusi:** `Process::run("python3 python_engine/main.py ...")`

---

## 8. Kebutuhan Non-Fungsional
*   **Performa:** Load time dashboard < 2 detik.
*   **Keamanan:** Standar OWASP, proteksi CSRF/XSS, Session Timeout.
*   **Skalabilitas:** Pemisahan jelas antara Logic Bisnis dan Framework.

---

## 9. Peta Jalan (Roadmap)

### Fase 1: Fondasi (Current)
*   [x] Inisialisasi Laravel 12.
*   [x] Implementasi RBAC & Skema Database.
*   [x] Setup Python Bridge.

### Fase 2: Frontend & Core UI (Next)
*   [ ] Implementasi Layout Utama (Sidebar Dinamis).
*   [ ] Dashboard Grid System.
*   [ ] Modul Management UI (Alpine.js Preview).
*   [ ] Integrasi Blade Directives untuk RBAC.

### Fase 3: Integrasi Data & Logic
*   [ ] Modul Manajemen User.
*   [ ] Integrasi Data Engine Python.
*   [ ] Reporting & Export.