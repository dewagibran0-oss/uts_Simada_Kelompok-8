# 🚀 SIMADA - Sistem Informasi e-Purchasing

## 📖 Ringkasan Proyek
**SIMADA** adalah solusi sistem informasi berbasis web yang dikembangkan untuk mengotomatisasi siklus **Procurement-to-Pay** di lingkungan organisasi. Sistem ini menghilangkan hambatan administratif dalam proses pengadaan melalui alur kerja digital yang terintegrasi antara Staf Purchasing, Manajer, dan Admin.

---


### 3. Spesifikasi Teknis
## 🛠️ Spesifikasi Teknis (System Requirements)
* **Environment:** PHP 8.2+ (Local: XAMPP/Laragon, Production: Niagahoster/cPanel)
* **Database Management:** MariaDB/MySQL 10.4+
* **Security Layer:**
    * **PDO Driver:** Proteksi total terhadap SQL Injection.
    * **Password Hashing:** Menggunakan bcrypt (`password_hash`) sesuai standar enkripsi modern.
    * **Session Security:** Implementasi `session_regenerate_id()` untuk mencegah *Session Hijacking*.
* **Compatibility:** Responsive UI (Bootstrap 5.x) & Cross-Browser Friendly.

---
## 🏗️ Arsitektur & Struktur Folder
Proyek menggunakan pendekatan **Modular Architecture**. Setiap modul memiliki tanggung jawab logis yang terpisah (*Separation of Concerns*).

```text
simada/
├── admin/                  # [Module] Dashboard Manajemen Admin
├── assets/                 # [Statics] CSS/JS framework, fonts, & media
├── auth/                   # [Auth] Login, Logout, & Session Handling
├── config/                 # [Kernel] Konfigurasi Database
├── layouts/                # [Template] Header, Footer, Navbar, Sidebar
├── manager/                # [Module] Approval & Verifikasi
├── purchasing/             # [Module] Interface pengajuan barang
├── index.php               # [Entry] Landing page utama
└── README.md               # [Documentation] Proyek ini

## 📦 Cara Instalasi & Setup Lengkap

### 1. Repository Deployment
```bash
git clone [https://github.com/dewagibran0-oss/uts_Simada_Kelompok-8.git](https://github.com/dewagibran0-oss/uts_Simada_Kelompok-8.git)
cd uts_Simada_Kelompok-8

2. Database Provisioning
Buka phpMyAdmin > Create New Database db_simada_epurchasing.

Jalankan script db_simada_epurchasing.sql melalui tab Import.

3. Configuration
Edit file config/database.php. Ubah parameter di blok else untuk Live Server (Niagahoster).

4. Permissions
Jika di Linux/cPanel, atur CHMOD folder assets menjadi 755 dan config menjadi 644.

### 5. Logika Bisnis & Coding Standards
```markdown
## 🧩 Penjelasan Fungsi & Logic Bisnis
* **Multi-Role Authentication:** Sistem mendeteksi role pada sesi user setelah login. Jika user mencoba mengakses modul yang tidak berhak, sistem akan me-redirect secara otomatis ke halaman `index.php`.
* **Dynamic DB Connector:** Kami menyematkan deteksi `$_SERVER['HTTP_HOST']`. Sistem cerdas dalam memilih database: jika di localhost, dia menggunakan `root/empty pass`, jika di web, dia menggunakan kredensial Niagahoster tanpa perlu modifikasi manual saat push ke GitHub.
* **Approval Lifecycle:**
    `Pending (Staff mengajukan)` → `Approved/Rejected (Manajer memproses)` → `Success (Transaksi tercatat)`.

## 💻 Coding Standards
* **PEP-Style for PHP:** Menggunakan `snake_case` untuk penamaan database dan file, serta `camelCase` untuk fungsi.
* **Pathing Rules:** Dilarang keras menggunakan *hardcoded path*. Selalu gunakan `__DIR__` untuk memastikan aplikasi bersifat portable.
* **Commenting:** Setiap fungsi yang kompleks wajib memiliki docblock penjelasan.

---

## 🔄 Version Control & Commit Protocol
Kami menggunakan *Conventional Commits* untuk menjaga riwayat perubahan tetap bersih:

| Tipe Aksi | Penjelasan |
| :--- | :--- |
| `feat` | Menambah fitur baru |
| `fix` | Memperbaiki bug |
| `docs` | Menambah dokumentasi |
| `refactor` | Optimasi kode |
| `perf` | Peningkatan kecepatan |
| `chore` | Update dependensi |

**Format:** `tipe(modul): penjelasan singkat`

## 👨‍💻 Kontributor
* **Dewa Ahmad Gibran** (Lead Developer)
* **Kelompok 8** - UTS

## ⚖️ License
Proyek ini dikembangkan sebagai bagian dari tugas sistem informasi. Hak cipta dilindungi oleh peraturan akademik yang berlaku.

© 2026 - SIMADA - e-Purchasing System