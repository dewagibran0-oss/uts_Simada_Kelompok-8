<?php
// manager/approval.php
require_once __DIR__ . '/../config/database.php';
include_once __DIR__ . '/../layouts/header.php';
include_once __DIR__ . '/../layouts/sidebar.php';
include_once __DIR__ . '/../layouts/navbar.php';

if ($_SESSION['role'] !== 'Manager') {
    echo "<div style='padding:30px;'><h3>Akses Ditolak!</h3></div>";
    include_once __DIR__ . '/../layouts/footer.php';
    exit();
}

// ====================================================================
// PROSES EKSEKUSI TOMBOL APPROVAL / REJECT (DENGAN SECURITY CHECK STOK)
// ====================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['proses_approval'])) {
    $id_request     = (int)$_POST['id_request'];
    $tindakan       = $_POST['status_approval']; 
    $catatan_mgr    = trim(filter_input(INPUT_POST, 'catatan_manager', FILTER_SANITIZE_SPECIAL_CHARS));
    $id_user_mgr    = $_SESSION['user_id'];

    try {
        $pdo->beginTransaction();

        // 1. Ambil detail kuantitas barang dari PR dan cek stok terbaru di database produk
        $stmt_check = $pdo->prepare("SELECT pr.jumlah, pr.no_request, p.id_product, p.nama_barang, p.stok 
                                     FROM purchase_requests pr
                                     JOIN products p ON pr.id_product = p.id_product
                                     WHERE pr.id_request = :id");
        $stmt_check->execute(['id' => $id_request]);
        $data_validasi = $stmt_check->fetch();

        if (!$data_validasi) {
            throw new Exception("Data berkas pengajuan tidak valid atau tidak ditemukan.");
        }

        // 2. JIKA DISETUJUI, LAKUKAN PENGECEKAN INTERAL GUDANG
        if ($tindakan === 'Approved') {
            // Jika jumlah permintaan melebihi stok yang ada saat ini di gudang
            if ($data_validasi['jumlah'] > $data_validasi['stok']) {
                throw new Exception("Persetujuan Dibatalkan! Sisa stok barang [{$data_validasi['nama_barang']}] di gudang hanya tersisa {$data_validasi['stok']} unit, tidak mencukupi untuk permintaan sebanyak {$data_validasi['jumlah']} unit.");
            }

            // Jalankan potong stok otomatis jika aman
            $sql_stok = "UPDATE products SET stok = stok - :qty WHERE id_product = :id_prod";
            $stmt_stok = $pdo->prepare($sql_stok);
            $stmt_stok->execute(['qty' => $data_validasi['jumlah'], 'id_prod' => $data_validasi['id_product']]);

            // Generate nomor nota transaksi formal
            $no_trx = "TRX-" . date('Ymd') . "-" . rand(1000, 9999);
            $sql_trx = "INSERT INTO transactions (no_transaksi, id_request, tanggal_transaksi) VALUES (:no_trx, :id_req, :tgl)";
            $stmt_trx = $pdo->prepare($sql_trx);
            $stmt_trx->execute(['no_trx' => $no_trx, 'id_req' => $id_request, 'tgl' => date('Y-m-d')]);
        }

        // 3. Catat berkas log history ke tabel approvals
        $sql_app = "INSERT INTO approvals (id_request, id_user, status_approval, catatan_manager) 
                    VALUES (:id_req, :id_user, :status, :catatan)";
        $stmt_app = $pdo->prepare($sql_app);
        $stmt_app->execute([
            'id_req'  => $id_request,
            'id_user' => $id_user_mgr,
            'status'  => $tindakan,
            'catatan' => $catatan_mgr
        ]);

        // 4. Update status final berkas purchase request
        $sql_req = "UPDATE purchase_requests SET status = :status WHERE id_request = :id_req";
        $stmt_req = $pdo->prepare($sql_req);
        $stmt_req->execute(['status' => $tindakan, 'id_req' => $id_request]);

        // Berhasil melewati semua gerbang pengamanan data
        $pdo->commit();
        echo "<script>window.location.href='approval.php?msg=done';</script>";
        exit();

    } catch (Exception $e) {
        // Gagalkan semua perubahan data jika terdeteksi stok tidak cukup
        $pdo->rollBack();
        $error_system_msg = $e->getMessage();
    }
}

// AMBIL DAFTAR PENGAJUAN PENDING
$pending_list = $pdo->query("SELECT pr.*, p.nama_barang, p.stok AS stok_gudang, u.nama_lengkap AS nama_staff 
                             FROM purchase_requests pr
                             JOIN products p ON pr.id_product = p.id_product
                             JOIN users u ON pr.id_user = u.id_user
                             WHERE pr.status = 'Pending'
                             ORDER BY pr.id_request ASC")->fetchAll();
?>

<style>
    .table-container { background: #fff; border-radius: 12px; box-shadow: var(--shadow-soft); overflow-x: auto; border: 1px solid var(--border-color); margin-top: 20px; }
    .modern-table { width: 100%; border-collapse: collapse; text-align: left; font-size: 14px; }
    .modern-table th { background: #f8fafc; padding: 16px 20px; color: #475569; font-weight: 600; border-bottom: 1px solid var(--border-color); }
    .modern-table td { padding: 16px 20px; color: #334155; border-bottom: 1px solid var(--border-color); }
    .btn-approve { background: #16a34a; color: white; border: none; padding: 8px 14px; border-radius: 6px; font-weight: 600; cursor: pointer; }
    .btn-reject { background: #dc2626; color: white; border: none; padding: 8px 14px; border-radius: 6px; font-weight: 600; cursor: pointer; }
    .form-inline { display: flex; flex-direction: column; gap: 8px; background: #f8fafc; padding: 15px; border-radius: 8px; border: 1px dashed #cbd5e1; }
    .alert { padding: 15px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; }
    .alert-success { background: #f0fdf4; border: 1px solid #bbf7d0; color: #16a34a; }
    .alert-danger { background: #fee2e2; border: 1px solid #fca5a5; color: #991b1b; }
</style>

<div style="margin-bottom: 20px;">
    <h1 style="font-size: 22px; font-weight: 700; color: #0f172a;">Verifikasi Berkas Purchase Request</h1>
    <p style="color: #64748b; font-size: 13px; margin-top: 2px;">Sistem dilengkapi pengaman otomatis anti-stok minus (Database Constraint Isolation).</p>
</div>

<?php if (isset($_GET['msg']) && $_GET['msg'] === 'done'): ?>
    <div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> Dokumen berhasil diproses secara aman!</div>
<?php endif; ?>

<?php if (isset($error_system_msg)): ?>
    <div class="alert alert-danger"><i class="fa-solid fa-triangle-exclamation"></i> <?= $error_system_msg; ?></div>
<?php endif; ?>

<div class="table-container">
    <table class="modern-table">
        <thead>
            <tr>
                <th>No. Berkas</th>
                <th>Staff Pengaju</th>
                <th>Spesifikasi Komoditas</th>
                <th style="text-align: center;">Minta</th>
                <th style="text-align: center;">Stok Gudang</th>
                <th style="text-align: right;">Total Anggaran</th>
                <th style="width: 300px; text-align: center;">Tindakan Kebijakan</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($pending_list) === 0): ?>
                <tr>
                    <td colspan="7" style="text-align: center; color: #94a3b8; padding: 40px; font-weight: 500;">
                        <i class="fa-solid fa-circle-check" style="color: #16a34a; margin-right: 6px;"></i> Antrean bersih! Tidak ada berkas baru.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($pending_list as $p): ?>
                    <tr>
                        <td style="font-weight: 700; color: #0f172a;"><?= htmlspecialchars($p['no_request']); ?></td>
                        <td><?= htmlspecialchars($p['nama_staff']); ?></td>
                        <td style="font-weight: 500;"><?= htmlspecialchars($p['nama_barang']); ?></td>
                        
                        <td style="text-align: center; font-weight: 700; color: <?= ($p['jumlah'] > $p['stok_gudang']) ? '#dc2626' : '#1e293b'; ?>;">
                            <?= htmlspecialchars($p['jumlah']); ?>
                        </td>
                        <td style="text-align: center; background: #f8fafc; font-weight: 600;"><?= htmlspecialchars($p['stok_gudang']); ?></td>
                        
                        <td style="text-align: right; font-weight: 600; color: #1e3a8a;">Rp <?= number_format($p['total_harga'], 0, ',', '.'); ?></td>
                        <td>
                            <form action="approval.php" method="POST" class="form-inline">
                                <input type="hidden" name="id_request" value="<?= $p['id_request']; ?>">
                                <input type="text" name="catatan_manager" placeholder="Tulis catatan di sini..." style="padding: 6px; font-size:12px; border:1px solid #cbd5e1; border-radius:4px;" required>
                                
                                <div style="display:flex; gap:10px; justify-content: space-between;">
                                    <button type="submit" name="proses_approval" value="1" class="btn-approve" onclick="return confirm('Setujui alokasi belanja ini?')">Setujui</button>
                                    <button type="submit" name="proses_approval" value="1" class="btn-reject" onclick="return confirm('Tolak berkas ini?')" onmousedown="this.form.status_approval.value='Rejected';">Tolak</button>
                                </div>
                                <input type="hidden" name="status_approval" value="Approved">
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include_once __DIR__ . '/../layouts/footer.php'; ?>