<?php
// purchasing/request.php
require_once __DIR__ . '/../config/database.php';
include_once __DIR__ . '/../layouts/header.php';
include_once __DIR__ . '/../layouts/sidebar.php';
include_once __DIR__ . '/../layouts/navbar.php';

// Proteksi Hak Akses: Hanya Staff Purchasing yang boleh masuk
if ($_SESSION['role'] !== 'Staff Purchasing') {
    echo "<div style='padding:30px;'><h3>Akses Ditolak!</h3>Halaman ini hanya untuk Staff Purchasing.</div>";
    include_once __DIR__ . '/../layouts/footer.php';
    exit();
}

$id_user_staff = $_SESSION['user_id'];

// QUERY AMBIL DATA PURCHASE REQUEST KHUSUS STAFF YANG SEDANG LOGIN
try {
    $sql = "SELECT pr.*, p.nama_barang, p.harga, p.satuan 
            FROM purchase_requests pr
            JOIN products p ON pr.id_product = p.id_product
            WHERE pr.id_user = :id_user
            ORDER BY pr.id_request DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id_user' => $id_user_staff]);
    $requests = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error data: " . $e->getMessage());
}
?>

<style>
    .crud-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
    .btn-primary { background: #2563eb; color: #fff; padding: 10px 18px; border-radius: 8px; text-decoration: none; font-size: 14px; font-weight: 600; display: inline-flex; align-items: center; gap: 8px; transition: background 0.2s; }
    .btn-primary:hover { background: #1d4ed8; }
    .table-container { background: #fff; border-radius: 12px; box-shadow: var(--shadow-soft); overflow-x: auto; border: 1px solid var(--border-color); }
    .modern-table { width: 100%; border-collapse: collapse; text-align: left; font-size: 14px; }
    .modern-table th { background: #f8fafc; padding: 16px 20px; color: #475569; font-weight: 600; border-bottom: 1px solid var(--border-color); }
    .modern-table td { padding: 16px 20px; color: #334155; border-bottom: 1px solid var(--border-color); vertical-align: middle; }
    .modern-table tr:hover { background: #f8fafc; }
    
    /* Style Badge Status Enterprise */
    .badge-status { padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; display: inline-block; }
    .status-pending { background: #fef3c7; color: #d97706; border: 1px solid #fde68a; }
    .status-approved { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
    .status-rejected { background: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5; }
    
    .alert-success { background: #f0fdf4; border: 1px solid #bbf7d0; color: #16a34a; padding: 15px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; }
</style>

<div class="crud-header">
    <div>
        <h1 style="font-size: 22px; font-weight: 700; color: #0f172a;">Pengajuan Pembelian (Purchase Request)</h1>
        <p style="color: #64748b; font-size: 13px; margin-top: 2px;">Pantau dan buat dokumen pengajuan pengadaan barang baru ke Management.</p>
    </div>
    <a href="request-add.php" class="btn-primary">
        <i class="fa-solid fa-file-circle-plus"></i> Buat Pengajuan Baru
    </a>
</div>

<?php if (isset($_GET['msg']) && $_GET['msg'] === 'success'): ?>
    <div class="alert-success">
        <i class="fa-solid fa-circle-check"></i> Dokumen Purchase Request berhasil dikirim! Menunggu proses verifikasi oleh Manager.
    </div>
<?php endif; ?>

<div class="table-container">
    <table class="modern-table">
        <thead>
            <tr>
                <th>No. Request</th>
                <th>Tanggal</th>
                <th>Nama Barang</th>
                <th style="text-align: right;">Harga Satuan</th>
                <th style="text-align: center;">Jumlah</th>
                <th style="text-align: right;">Total Harga</th>
                <th style="text-align: center;">Status Approval</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($requests) === 0): ?>
                <tr>
                    <td colspan="7" style="text-align: center; color: #94a3b8; padding: 40px;">Belum ada riwayat pengajuan barang.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($requests as $r): ?>
                    <tr>
                        <td style="font-weight: 600; color: #0f172a;"><?= htmlspecialchars($r['no_request']); ?></td>
                        <td><?= date('d/m/Y', strtotime($r['tanggal_pengajuan'])); ?></td>
                        <td style="font-weight: 500;"><?= htmlspecialchars($r['nama_barang']); ?></td>
                        <td style="text-align: right;">Rp <?= number_format($r['harga'], 0, ',', '.'); ?></td>
                        <td style="text-align: center;"><?= htmlspecialchars($r['jumlah']); ?> <?= htmlspecialchars($r['satuan']); ?></td>
                        <td style="text-align: right; font-weight: 600; color: #1e3a8a;">Rp <?= number_format($r['total_harga'], 0, ',', '.'); ?></td>
                        <td style="text-align: center;">
                            <?php 
                                $status_class = 'status-pending';
                                if ($r['status'] === 'Approved') $status_class = 'status-approved';
                                if ($r['status'] === 'Rejected') $status_class = 'status-rejected';
                            ?>
                            <span class="badge-status <?= $status_class; ?>">
                                <i class="fa-solid <?= $r['status'] === 'Pending' ? 'fa-clock' : ($r['status'] === 'Approved' ? 'fa-circle-check' : 'fa-circle-xmark'); ?>"></i>
                                <?= htmlspecialchars($r['status']); ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include_once __DIR__ . '/../layouts/footer.php'; ?>