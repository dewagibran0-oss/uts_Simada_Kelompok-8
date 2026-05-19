<?php
// admin/products.php
require_once __DIR__ . '/../config/database.php';
include_once __DIR__ . '/../layouts/header.php';
include_once __DIR__ . '/../layouts/sidebar.php';
include_once __DIR__ . '/../layouts/navbar.php';

if ($_SESSION['role'] !== 'Admin') {
    echo "<div style='padding:30px;'><h3>Akses Ditolak!</h3></div>";
    include_once __DIR__ . '/../layouts/footer.php';
    exit();
}

// ==========================================
// 1. LOGIKA PROSES TAMBAH BARANG (CREATE)
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_barang'])) {
    $nama_barang = trim(filter_input(INPUT_POST, 'nama_barang', FILTER_SANITIZE_SPECIAL_CHARS));
    $id_vendor   = (int)$_POST['id_vendor'];
    $harga       = (max(0, (int)$_POST['harga']));
    $stok        = (max(0, (int)$_POST['stok']));
    $satuan      = trim(filter_input(INPUT_POST, 'satuan', FILTER_SANITIZE_SPECIAL_CHARS));

    if (!empty($nama_barang) && $id_vendor > 0 && $harga > 0 && !empty($satuan)) {
        try {
            // GENERATOR KODE BARANG OTOMATIS (BRG-XXXXX)
            $stmt_code = $pdo->query("SELECT kode_barang FROM products ORDER BY id_product DESC LIMIT 1");
            $last_code = $stmt_code->fetchColumn();
            
            if ($last_code) {
                // Ambil angka dari kode terakhir, misal BRG-0002 diambil 2
                $last_num = (int)substr($last_code, 4);
                $next_num = str_pad($last_num + 1, 4, '0', STR_PAD_LEFT);
            } else {
                $next_num = "0001";
            }
            $generated_kode_barang = "BRG-" . $next_num;

            // Masukkan $generated_kode_barang ke dalam query INSERT
            $sql_insert = "INSERT INTO products (kode_barang, nama_barang, id_vendor, harga, stok, satuan) 
                           VALUES (:kode, :nama, :id_v, :harga, :stok, :satuan)";
            $stmt_ins = $pdo->prepare($sql_insert);
            $stmt_ins->execute([
                'kode'   => $generated_kode_barang,
                'nama'   => $nama_barang,
                'id_v'   => $id_vendor,
                'harga'  => $harga,
                'stok'   => $stok,
                'satuan' => $satuan
            ]);
            echo "<script>window.location.href='products.php?msg=success';</script>";
            exit();
        } catch (PDOException $e) {
            $error_msg = "Gagal menambah barang: " . $e->getMessage();
        }
    } else {
        $error_msg = "Mohon isi semua field input dengan data yang valid!";
    }
}

// ==========================================
// 2. LOGIKA PROSES HAPUS BARANG (DELETE)
// ==========================================
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id_product = (int)$_GET['id'];
    try {
        $stmt_del = $pdo->prepare("DELETE FROM products WHERE id_product = :id");
        $stmt_del->execute(['id' => $id_product]);
        echo "<script>window.location.href='products.php?msg=deleted';</script>";
        exit();
    } catch (PDOException $e) {
        $error_msg = "Barang gagal dihapus! Data ini kemungkinan sedang digunakan dalam transaksi/pengajuan aktif.";
    }
}

// ==========================================
// 3. QUERY DATA PRODUK & VENDOR (READ)
// ==========================================
try {
    // Kita gunakan INNER JOIN untuk mengambil nama vendor asal muasal barang
    $sql_fetch = "SELECT p.*, v.nama_vendor 
                  FROM products p 
                  JOIN vendors v ON p.id_vendor = v.id_vendor 
                  ORDER BY p.id_product DESC";
    $products = $pdo->query($sql_fetch)->fetchAll();
    
    // Ambil data vendor untuk pilihan dropdown di form input
    $vendors = $pdo->query("SELECT id_vendor, nama_vendor FROM vendors WHERE status = 'Aktif'")->fetchAll();
} catch (PDOException $e) {
    die("Error Database: " . $e->getMessage());
}
?>

<style>
    .crud-layout { display: grid; grid-template-columns: 2fr 1fr; gap: 30px; align-items: start; }
    @media(max-width: 992px) { .crud-layout { grid-template-columns: 1fr; } }
    
    .panel-box { background: #fff; border-radius: 12px; padding: 25px; box-shadow: var(--shadow-soft); border: 1px solid var(--border-color); }
    .modern-table { width: 100%; border-collapse: collapse; font-size: 14px; text-align: left; }
    .modern-table th { background: #f8fafc; padding: 14px 16px; color: #475569; font-weight: 600; border-bottom: 1px solid var(--border-color); }
    .modern-table td { padding: 14px 16px; color: #334155; border-bottom: 1px solid var(--border-color); vertical-align: middle; }
    .modern-table tr:hover { background: #f8fafc; }
    
    .form-group { margin-bottom: 18px; }
    .form-label { display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px; }
    .form-input { width: 100%; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; outline: none; transition: border 0.2s; }
    .form-input:focus { border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15); }
    
    .btn-submit { background: #2563eb; color: #fff; border: none; width: 100%; padding: 12px; border-radius: 8px; cursor: pointer; font-size: 14px; font-weight: 600; display: flex; align-items: center; justify-content: center; gap: 8px; }
    .btn-submit:hover { background: #1d4ed8; }
    .btn-delete { background: #fee2e2; color: #b91c1c; border: none; padding: 6px 12px; border-radius: 6px; cursor: pointer; font-size: 13px; text-decoration: none; }
    .btn-delete:hover { background: #fca5a5; }
    
    .alert { padding: 12px 15px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; display: flex; align-items: center; gap: 10px; }
    .alert-success { background: #f0fdf4; border: 1px solid #bbf7d0; color: #16a34a; }
    .alert-danger { background: #fee2e2; border: 1px solid #fca5a5; color: #991b1b; }
</style>

<div style="margin-bottom: 25px;">
    <h1 style="font-size: 22px; font-weight: 700; color: #0f172a;">Kelola Katalog Barang</h1>
    <p style="color: #64748b; font-size: 13px; margin-top: 2px;">Kelola komoditas logistik aset dan inventarisasi harga satuan barang dari mitra vendor.</p>
</div>

<?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-success">
        <i class="fa-solid fa-circle-check"></i> 
        <?= $_GET['msg'] === 'success' ? "Produk komoditas baru berhasil didaftarkan!" : "Data komoditas berhasil dihapus."; ?>
    </div>
<?php endif; ?>

<?php if (isset($error_msg)): ?>
    <div class="alert alert-danger"><i class="fa-solid fa-circle-exclamation"></i> <?= $error_msg; ?></div>
<?php endif; ?>

<div class="crud-layout">
    <div class="panel-box" style="overflow-x: auto;">
        <h3 style="font-size: 16px; font-weight: 600; margin-bottom: 15px; color: #0f172a;">Katalog Komoditas Aktif</h3>
        <table class="modern-table">
            <thead>
                <tr>
                    <th>Nama Barang / Model</th>
                    <th>Mitra Vendor</th>
                    <th style="text-align: right;">Harga Satuan</th>
                    <th style="text-align: center;">Stok</th>
                    <th>Satuan</th>
                    <th style="text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($products) === 0): ?>
                    <tr>
                        <td colspan="6" style="text-align: center; color: #94a3b8; padding: 30px;">Belum ada komoditas di dalam katalog. Silakan tambah di panel kanan.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($products as $p): ?>
                        <tr>
                            <td style="font-weight: 600; color: #0f172a;"><?= htmlspecialchars($p['nama_barang']); ?></td>
                            <td style="color: #475569; font-size: 13px;"><i class="fa-solid fa-building" style="font-size: 11px; margin-right: 4px;"></i> <?= htmlspecialchars($p['nama_vendor']); ?></td>
                            <td style="text-align: right; font-weight: 500; color: #1e3a8a;">Rp <?= number_format($p['harga'], 0, ',', '.'); ?></td>
                            <td style="text-align: center; font-weight: 600;"><?= htmlspecialchars($p['stok']); ?></td>
                            <td><span style="color:#64748b; font-size:13px;"><?= htmlspecialchars($p['satuan']); ?></span></td>
                            <td style="text-align: center;">
                                <a href="products.php?action=delete&id=<?= $p['id_product']; ?>" 
                                   class="btn-delete" 
                                   onclick="return confirm('Hapus item <?= htmlspecialchars($p['nama_barang']); ?> dari katalog?')">
                                    <i class="fa-solid fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="panel-box">
        <h3 style="font-size: 16px; font-weight: 600; margin-bottom: 15px; color: #0f172a;"><i class="fa-solid fa-box-open" style="color:#2563eb; margin-right:6px;"></i> Registrasi Barang</h3>
        <form action="products.php" method="POST" autocomplete="off">
            <div class="form-group">
                <label class="form-label" for="nama_barang">Nama Barang / Spesifikasi</label>
                <input type="text" id="nama_barang" name="nama_barang" class="form-input" placeholder="Contoh: Asus Zenbook OLED 13" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="id_vendor">Pemasok / Vendor Terkait</label>
                <select id="id_vendor" name="id_vendor" class="form-input" required>
                    <option value="">-- Pilih Vendor Penyedia --</option>
                    <?php foreach ($vendors as $v): ?>
                        <option value="<?= $v['id_vendor']; ?>"><?= htmlspecialchars($v['nama_vendor']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label" for="harga">Harga Pagu Per Satuan (Rp)</label>
                <input type="number" id="harga" name="harga" class="form-input" min="1" placeholder="Contoh: 15000000" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="stok">Jumlah Ketersediaan Stok</label>
                <input type="number" id="stok" name="stok" class="form-input" min="0" placeholder="Contoh: 50" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="satuan">Satuan Takaran Unit</label>
                <input type="text" id="satuan" name="satuan" class="form-input" placeholder="Contoh: Unit, Pcs, Box, Rim" required>
            </div>

            <button type="submit" name="tambah_barang" class="btn-submit">
                <i class="fa-solid fa-square-plus"></i> Masukkan ke Katalog
            </button>
        </form>
    </div>
</div>

<?php include_once __DIR__ . '/../layouts/footer.php'; ?>