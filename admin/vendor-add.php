<?php
// admin/vendor-add.php
require_once __DIR__ . '/../config/database.php';
include_once __DIR__ . '/../layouts/header.php';
include_once __DIR__ . '/../layouts/sidebar.php';
include_once __DIR__ . '/../layouts/navbar.php';

// Proteksi Hak Akses
if ($_SESSION['role'] !== 'Admin') {
    echo "<div style='padding:30px;'><h3>Akses Ditolak!</h3></div>";
    include_once __DIR__ . '/../layouts/footer.php';
    exit();
}

// ==========================================
// LOGIKA PROSES TAMBAH DATA (CREATE)
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data form dan sanitasi input
    $nama_vendor = trim(filter_input(INPUT_POST, 'nama_vendor', FILTER_SANITIZE_SPECIAL_CHARS));
    $nama_kontak = trim(filter_input(INPUT_POST, 'nama_kontak', FILTER_SANITIZE_SPECIAL_CHARS));
    $telepon     = trim(filter_input(INPUT_POST, 'telepon', FILTER_SANITIZE_SPECIAL_CHARS));
    $email       = trim(filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL));
    $alamat      = trim(filter_input(INPUT_POST, 'alamat', FILTER_SANITIZE_SPECIAL_CHARS));

    if ($nama_vendor && $nama_kontak && $telepon && $email && $alamat) {
        try {
            $sql = "INSERT INTO vendors (nama_vendor, nama_kontak, telepon, email, alamat, status) 
                    VALUES (:nama, :kontak, :telp, :email, :alamat, 'Aktif')";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'nama' => $nama_vendor,
                'kontak' => $nama_kontak,
                'telp' => $telepon,
                'email' => $email,
                'alamat' => $alamat
            ]);

            // Redirect ke halaman utama vendor jika sukses
            echo "<script>window.location.href='vendors.php?msg=success';</script>";
            exit();
        } catch (PDOException $e) {
            $error_msg = "Gagal menyimpan data ke database: " . $e->getMessage();
        }
    } else {
        $error_msg = "Mohon isi semua data form dengan benar dan valid!";
    }
}
?>

<style>
    .form-container { background: #fff; border-radius: 12px; padding: 30px; box-shadow: var(--shadow-soft); max-width: 700px; border: 1px solid var(--border-color); }
    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px; }
    .form-group-full { margin-bottom: 20px; }
    .form-label { display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 8px; }
    .form-input { width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; color: #334155; outline: none; transition: all 0.3s; }
    .form-input:focus { border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15); }
    .btn-group { display: flex; gap: 12px; margin-top: 10px; }
    .btn-submit { background: #2563eb; color: #fff; border: none; padding: 12px 24px; border-radius: 8px; cursor: pointer; font-size: 14px; font-weight: 600; }
    .btn-submit:hover { background: #1d4ed8; }
    .btn-cancel { background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; padding: 12px 24px; border-radius: 8px; text-decoration: none; font-size: 14px; font-weight: 500; text-align: center; }
    .btn-cancel:hover { background: #e2e8f0; }
    .alert-danger { background: #fee2e2; border: 1px solid #fca5a5; color: #991b1b; padding: 15px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; }
</style>

<div style="margin-bottom: 25px;">
    <h1 style="font-size: 22px; font-weight: 700; color: #0f172a;">Tambah Data Vendor Baru</h1>
    <p style="color: #64748b; font-size: 13px; margin-top: 2px;">Masukkan informasi legalitas dan kontak perusahaan vendor pengadaan barang.</p>
</div>

<?php if (isset($error_msg)): ?>
    <div class="alert-danger"><i class="fa-solid fa-circle-exclamation"></i> <?= $error_msg; ?></div>
<?php endif; ?>

<div class="form-container">
    <form action="vendor-add.php" method="POST" autocomplete="off">
        <div class="form-row">
            <div>
                <label class="form-label" for="nama_vendor">Nama Vendor / Perusahaan</label>
                <input type="text" id="nama_vendor" name="nama_vendor" class="form-input" placeholder="Contoh: PT. Adhi Jaya Perkasa" required>
            </div>
            <div>
                <label class="form-label" for="nama_kontak">Nama Contact Person (PIC)</label>
                <input type="text" id="nama_kontak" name="nama_kontak" class="form-input" placeholder="Contoh: Ahmad Subarjo" required>
            </div>
        </div>

        <div class="form-row">
            <div>
                <label class="form-label" for="email">Alamat Email Perusahaan</label>
                <input type="email" id="email" name="email" class="form-input" placeholder="Contoh: corporate@vendor.com" required>
            </div>
            <div>
                <label class="form-label" for="telepon">No. Telepon / WhatsApp</label>
                <input type="text" id="telepon" name="telepon" class="form-input" placeholder="Contoh: 08123456789" required>
            </div>
        </div>

        <div class="form-group-full">
            <label class="form-label" for="alamat">Alamat Kantor Pusat</label>
            <textarea id="alamat" name="alamat" class="form-input" rows="4" placeholder="Masukkan alamat lengkap kantor vendor..." required style="resize: none;"></textarea>
        </div>

        <div class="btn-group">
            <button type="submit" class="btn-submit"><i class="fa-solid fa-floppy-disk"></i> Simpan Data</button>
            <a href="vendors.php" class="btn-cancel">Batal</a>
        </div>
    </form>
</div>

<?php include_once __DIR__ . '/../layouts/footer.php'; ?>