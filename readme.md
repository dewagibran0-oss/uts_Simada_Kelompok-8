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

## 🗄️ Struktur Database: `db_simada_epurchasing`

Database ini dirancang untuk mendukung alur kerja *e-purchasing* yang terintegrasi, mulai dari pengelolaan produk hingga transaksi akhir.

### 1. Tabel `users`
*Menyimpan data pengguna yang memiliki akses ke sistem.*

| Field | Tipe Data | Keterangan |
| :--- | :--- | :--- |
| `id_user` | INT (PK) | Auto Increment, Primary Key |
| `username` | VARCHAR(50) | Username untuk login (Unique) |
| `password` | VARCHAR(255) | Password terenkripsi (Hashed) |
| `nama_lengkap` | VARCHAR(100) | Nama asli pengguna |
| `role` | ENUM | Admin, Staff Purchasing, Manager |
| `created_at` | TIMESTAMP | Waktu pembuatan akun |

### 2. Tabel `vendors`
*Menyimpan data rekanan/penyedia barang.*

| Field | Tipe Data | Keterangan |
| :--- | :--- | :--- |
| `id_vendor` | INT (PK) | Auto Increment, Primary Key |
| `nama_vendor` | VARCHAR(100) | Nama perusahaan vendor |
| `nama_kontak` | VARCHAR(100) | Nama PIC vendor |
| `telepon` | VARCHAR(20) | Nomor telepon vendor |
| `email` | VARCHAR(100) | Alamat email vendor |
| `alamat` | TEXT | Alamat kantor vendor |
| `status` | ENUM | Aktif / Nonaktif |

### 3. Tabel `products`
*Menyimpan katalog barang yang tersedia untuk dibeli.*

| Field | Tipe Data | Keterangan |
| :--- | :--- | :--- |
| `id_product` | INT (PK) | Auto Increment, Primary Key |
| `id_vendor` | INT (FK) | Relasi ke `vendors.id_vendor` |
| `kode_barang` | VARCHAR(30) | Kode unik barang (Unique) |
| `nama_barang` | VARCHAR(150) | Nama barang |
| `kategori` | VARCHAR(50) | Kategori barang |
| `harga` | INT | Harga satuan barang |
| `stok` | INT | Jumlah stok tersedia |
| `satuan` | VARCHAR(20) | Unit (pcs, box, dll) |

### 4. Tabel `purchase_requests`
*Menyimpan data pengajuan pembelian oleh staf.*

| Field | Tipe Data | Keterangan |
| :--- | :--- | :--- |
| `id_request` | INT (PK) | Auto Increment, Primary Key |
| `no_request` | VARCHAR(50) | Nomor PR unik (Unique) |
| `id_user` | INT (FK) | Relasi ke `users.id_user` |
| `id_product` | INT (FK) | Relasi ke `products.id_product` |
| `jumlah` | INT | Jumlah yang diajukan |
| `total_harga` | BIGINT | Jumlah * Harga |
| `tanggal_pengajuan`| DATE | Tanggal dibuat |
| `status` | ENUM | Pending, Approved, Rejected |
| `catatan_staff` | TEXT | Catatan tambahan staf |

### 5. Tabel `approvals`
*Menyimpan hasil keputusan (approve/reject) oleh manajer.*

| Field | Tipe Data | Keterangan |
| :--- | :--- | :--- |
| `id_approval` | INT (PK) | Auto Increment, Primary Key |
| `id_request` | INT (FK) | Relasi ke `purchase_requests.id_request` |
| `id_user` | INT (FK) | Relasi ke `users.id_user` (Manajer) |
| `status_approval`| ENUM | Approved, Rejected |
| `catatan_manager`| TEXT | Catatan manajer |
| `tanggal_proses` | TIMESTAMP | Waktu persetujuan |

### 6. Tabel `transactions`
*Menyimpan catatan transaksi yang sudah disetujui.*

| Field | Tipe Data | Keterangan |
| :--- | :--- | :--- |
| `id_transaction` | INT (PK) | Auto Increment, Primary Key |
| `no_transaksi` | VARCHAR(50) | Nomor transaksi unik (Unique) |
| `id_request` | INT (FK) | Relasi ke `purchase_requests.id_request` |
| `tanggal_transaksi`| DATE | Tanggal transaksi |
| `metode_pembayaran`| VARCHAR(50) | Metode pembayaran |

---
> **Catatan:**
> * **PK** = Primary Key (Kunci Utama).
> * **FK** = Foreign Key (Kunci Tamu untuk relasi antar tabel).

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