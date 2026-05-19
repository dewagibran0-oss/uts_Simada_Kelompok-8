<?php
// purchasing/request-add.php
require_once __DIR__ . '/../config/database.php';
include_once __DIR__ . '/../layouts/header.php';
include_once __DIR__ . '/../layouts/sidebar.php';
include_once __DIR__ . '/../layouts/navbar.php';

if ($_SESSION['role'] !== 'Staff Purchasing') {
    echo "<div style='padding:30px;'><h3>Akses Ditolak!</h3></div>";
    include_once __DIR__ . '/../layouts/footer.php';
    exit();
}

// GENERATOR AUTOMATIS NOMOR DOKUMEN
$prefix = "PR-" . date('Ym') . "-";
try {
    $stmt_code = $pdo->prepare("SELECT no_request FROM purchase_requests WHERE no_request LIKE :prefix ORDER BY id_request DESC LIMIT 1");
    $stmt_code->execute(['prefix' => $prefix . '%']);
    $last_code = $stmt_code->fetchColumn();
    $next_num = $last_code ? str_pad((int)substr($last_code, -4) + 1, 4, '0', STR_PAD_LEFT) : "0001";
    $generated_no_request = $prefix . $next_num;
} catch (PDOException $e) {
    $generated_no_request = $prefix . "0001";
}

// AMBIL MASTER KATALOG BARANG (HANYA YANG STOKNYA > 0)
$products = $pdo->query("SELECT id_product, nama_barang, harga, stok, satuan FROM products WHERE stok > 0 ORDER BY nama_barang ASC")->fetchAll();

// PROSES SIMPAN DATA
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $no_request   = $_POST['no_request'];
    $id_product   = (int)$_POST['id_product'];
    $jumlah       = (int)$_POST['jumlah'];
    $catatan      = trim(filter_input(INPUT_POST, 'catatan_staff', FILTER_SANITIZE_SPECIAL_CHARS));
    $id_user_staff = $_SESSION['user_id'];
    $tanggal_sekarang = date('Y-m-d');

    // Ambil harga dan stok asli dari DB terbaru
    $stmt_p = $pdo->prepare("SELECT harga, stok FROM products WHERE id_product = :id");
    $stmt_p->execute(['id' => $id_product]);
    $product_db = $stmt_p->fetch();

    if ($product_db) {
        // VALIDASI BACKEND: Jika jumlah yang diminta melebihi stok di gudang
        if ($jumlah > $product_db['stok']) {
            $error_msg = "Gagal mengajukan! Kuantitas yang diminta ($jumlah) melebihi batas sisa stok gudang saat ini ({$product_db['stok']}).";
        } else if ($jumlah <= 0) {
            $error_msg = "Jumlah permintaan tidak valid!";
        } else {
            $total_harga = $product_db['harga'] * $jumlah;
            try {
                $sql_insert = "INSERT INTO purchase_requests (no_request, id_user, id_product, jumlah, total_harga, tanggal_pengajuan, status, catatan_staff) 
                               VALUES (:no_req, :id_user, :id_prod, :qty, :total, :tgl, 'Pending', :catatan)";
                $stmt_ins = $pdo->prepare($sql_insert);
                $stmt_ins->execute([
                    'no_req'  => $no_request,
                    'id_user' => $id_user_staff,
                    'id_prod' => $id_product,
                    'qty'     => $jumlah,
                    'total'   => $total_harga,
                    'tgl'     => $tanggal_sekarang,
                    'catatan' => $catatan
                ]);
                echo "<script>window.location.href='request.php?msg=success';</script>";
                exit();
            } catch (PDOException $e) {
                $error_msg = "Sistem gagal menyimpan pengajuan: " . $e->getMessage();
            }
        }
    } else {
        $error_msg = "Produk tidak ditemukan atau sudah tidak aktif!";
    }
}
?>

<style>
    .form-container { background: #fff; border-radius: 12px; padding: 30px; box-shadow: var(--shadow-soft); max-width: 600px; border: 1px solid var(--border-color); }
    .form-group { margin-bottom: 20px; }
    .form-label { display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 8px; }
    .form-input { width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; color: #334155; outline: none; }
    .form-input:readonly { background: #f1f5f9; color: #64748b; cursor: not-allowed; }
    .btn-group { display: flex; gap: 12px; margin-top: 10px; }
    .btn-submit { background: #2563eb; color: #fff; border: none; padding: 12px 24px; border-radius: 8px; cursor: pointer; font-size: 14px; font-weight: 600; }
    .btn-submit:hover { background: #1d4ed8; }
    .btn-cancel { background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; padding: 12px 24px; border-radius: 8px; text-decoration: none; font-size: 14px; font-weight: 500; text-align: center; }
</style>

<div style="margin-bottom: 25px;">
    <h1 style="font-size: 22px; font-weight: 700; color: #0f172a;">Form Pengajuan Pengadaan Barang</h1>
    <p style="color: #64748b; font-size: 13px; margin-top: 2px;">Sistem otomatis membatasi jumlah pengadaan agar tidak melebihi stok yang tersedia.</p>
</div>

<?php if (isset($error_msg)): ?>
    <div style="background:#fee2e2; color:#991b1b; padding:15px; border-radius:8px; margin-bottom:20px; font-size:14px; border:1px solid #fca5a5;"><i class="fa-solid fa-circle-exclamation"></i> <?= $error_msg; ?></div>
<?php endif; ?>

<div class="form-container">
    <form action="request-add.php" method="POST" autocomplete="off">
        <div class="form-group">
            <label class="form-label">Nomor Pengajuan (Otomatis)</label>
            <input type="text" name="no_request" class="form-input" value="<?= $generated_no_request; ?>" readonly>
        </div>

        <div class="form-group">
            <label class="form-label" for="id_product">Pilih Barang Katalog</label>
            <select id="id_product" name="id_product" class="form-input" required onchange="updateMaxStock()">
                <option value="">-- Pilih Barang Komoditas --</option>
                <?php foreach ($products as $p): ?>
                    <option value="<?= $p['id_product']; ?>" data-stok="<?= $p['stok']; ?>">
                        <?= htmlspecialchars($p['nama_barang']); ?> (Rp <?= number_format($p['harga'], 0, ',', '.'); ?> | Sisa Stok: <?= $p['stok']; ?> <?= $p['satuan']; ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label class="form-label" for="jumlah">Kuantitas Kebutuhan (Volume)</label>
            <input type="number" id="jumlah" name="jumlah" class="form-input" min="1" placeholder="Masukkan jumlah unit" required>
            <small id="stock-warning" style="color: #b45309; font-size: 12px; display: none; margin-top: 5px; font-weight: 500;"></small>
        </div>

        <div class="form-group">
            <label class="form-label" for="catatan_staff">Justifikasi Kebutuhan / Catatan</label>
            <textarea id="catatan_staff" name="catatan_staff" class="form-input" rows="3" placeholder="Tulis alasan urgensi pengadaan barang ini..." style="resize:none;"></textarea>
        </div>

        <div class="btn-group">
            <button type="submit" class="btn-submit"><i class="fa-solid fa-paper-plane"></i> Kirim Pengajuan</button>
            <a href="request.php" class="btn-cancel">Batal</a>
        </div>
    </form>
</div>

<script>
function updateMaxStock() {
    const select = document.getElementById('id_product');
    const inputJumlah = document.getElementById('jumlah');
    const warning = document.getElementById('stock-warning');
    
    const selectedOption = select.options[select.selectedIndex];
    const maxStok = selectedOption.getAttribute('data-stok');
    
    if (maxStok) {
        inputJumlah.max = maxStok;
        warning.style.display = "block";
        warning.innerHTML = `<i class="fa-solid fa-info-circle"></i> Batas maksimal pembelian item ini adalah ${maxStok} unit.`;
    } else {
        inputJumlah.removeAttribute('max');
        warning.style.display = "none";
    }
}
</script>

<?php include_once __DIR__ . '/../layouts/footer.php'; ?>