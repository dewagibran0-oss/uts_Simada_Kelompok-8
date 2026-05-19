# 🚀 SIMADA - Sistem Informasi e-Purchasing

[![Status](https://img.shields.io/badge/status-active-success.svg)]() 
[![PHP](https://img.shields.io/badge/PHP-8.2%2B-blue.svg)]() 
[![Database](https://img.shields.io/badge/Database-MySQL-orange.svg)]()

**SIMADA** adalah solusi sistem informasi berbasis web yang dirancang untuk mengotomatisasi siklus **Procurement-to-Pay** di lingkungan organisasi. Sistem ini mentransformasi alur pengadaan manual menjadi alur kerja digital yang terintegrasi, transparan, dan efisien.

---

## 🏗️ Struktur Proyek
Proyek ini menggunakan **Modular Architecture** untuk memastikan *Separation of Concerns* yang rapi.

```text
SIMADA/
├── admin/          # Dashboard & Manajemen User/Vendor
├── auth/           # Sistem Login, Logout, & Session Security
├── config/         # Konfigurasi Database & Koneksi
├── layouts/        # Komponen UI (Header, Footer, Nav, Sidebar)
├── manager/        # Modul Approval & Verifikasi Manajer
├── purchasing/     # Modul Pengajuan & Transaksi Staf
├── assets/         # Static files (CSS, JS, Images)
├── index.php       # Entry Point Utama
└── README.md       # Dokumentasi Proyek
```

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

## 📦 Cara Instalasi & Setup

### 1. Repository Deployment
```bash
git clone [https://github.com/dewagibran0-oss/uts_Simada_Kelompok-8.git](https://github.com/dewagibran0-oss/uts_Simada_Kelompok-8.git)
cd uts_Simada_Kelompok-8

```

### 2. Database Provisioning
1. Buka **phpMyAdmin** pada lokal server Anda (XAMPP/Laragon).
2. Buat database baru dengan nama `db_simada_epurchasing`.
3. Pilih database tersebut, lalu buka tab **Import**.
4. Upload dan jalankan file `db_simada_epurchasing.sql`.

### 3. Configuration
Edit file `config/database.php` untuk mengatur koneksi:
* Sesuaikan kredensial database pada blok `else` jika Anda melakukan deployment ke **Live Server (Niagahoster/cPanel)**.

### 4. Permissions (Khusus Linux/cPanel)
Jika aplikasi berjalan di lingkungan Linux, pastikan izin akses folder diatur sebagai berikut:
* `assets/` : `755`
* `config/` : `644`

---

---

## 🧩 Logika Bisnis & Fitur Utama

* **Multi-Role Authentication**: Sistem mendeteksi *role* pengguna secara otomatis setelah login. Akses yang tidak sah ke modul tertentu akan secara otomatis diredirect ke `index.php`.
* **Dynamic DB Connector**: Menggunakan deteksi `$_SERVER['HTTP_HOST']` untuk transisi otomatis antara konfigurasi *localhost* dan *production*. Tidak perlu modifikasi kode saat melakukan *push* ke repositori.
* **Approval Lifecycle**: Alur kerja sistem yang terstruktur:
    > `Pending (Staff)` ➔ `Approved/Rejected (Manajer)` ➔ `Success (Transaksi tercatat)`

## 💻 Standar Pengembangan

* **Naming Convention**: Menggunakan `snake_case` untuk penamaan database/file dan `camelCase` untuk penamaan fungsi.
* **Portability Rules**: Penggunaan *hardcoded path* dilarang keras. Kami menerapkan `__DIR__` untuk memastikan aplikasi bersifat *portable* di lingkungan server mana pun.
* **Documentation**: Setiap fungsi kompleks wajib menyertakan *docblock* untuk menjelaskan alur logika secara teknis.

---

## 🔄 Version Control & Protocol

Kami menggunakan **Conventional Commits** untuk menjaga riwayat repositori tetap bersih dan mudah dipahami:

| Tipe | Penjelasan |
| :---: | :--- |
| `feat` | Menambahkan fitur baru |
| `fix` | Memperbaiki bug atau error |
| `docs` | Menambah atau mengubah dokumentasi |
| `refactor` | Optimasi kode tanpa perubahan fungsionalitas |
| `perf` | Peningkatan performa aplikasi |
| `chore` | Update dependensi atau konfigurasi server |

> **Format Penulisan**: `tipe(modul): deskripsi singkat`
> *Contoh:* `feat(purchasing): menambah validasi input supplier`

---

## 👨‍💻 Kontributor

* **Dewa Ahmad Gibran** — *Lead Developer*
* **Kelompok 8** — *UTS Development Team*

## ⚖️ License & Copyright

Proyek ini dikembangkan sebagai bagian dari tugas sistem informasi. Hak cipta dilindungi oleh peraturan akademik yang berlaku.

**© 2026 - SIMADA - e-Purchasing System**