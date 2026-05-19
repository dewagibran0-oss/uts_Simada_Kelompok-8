<?php
// admin/reports.php
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

// Inisialisasi parameter filter tanggal
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date   = isset($_GET['end_date']) ? $_GET['end_date'] : '';

// LOGIKA QUERY DENGAN FILTER TANGGAL DINAMIS
try {
    $query_str = "SELECT t.no_transaksi, t.tanggal_transaksi, pr.no_request, pr.jumlah, pr.total_harga, 
                         p.nama_barang, p.harga AS harga_satuan, u.nama_lengkap AS nama_staff
                  FROM transactions t
                  JOIN purchase_requests pr ON t.id_request = pr.id_request
                  JOIN products p ON pr.id_product = p.id_product
                  JOIN users u ON pr.id_user = u.id_user";
                  
    // Jika user mengisi filter rentang tanggal
    if (!empty($start_date) && !empty($end_date)) {
        $query_str .= " WHERE t.tanggal_transaksi BETWEEN :start_date AND :end_date";
    }
    
    $query_str .= " ORDER BY t.id_transaction DESC";
    $stmt = $pdo->prepare($query_str);
    
    // Binding parameter jika filter aktif
    if (!empty($start_date) && !empty($end_date)) {
        $stmt->execute(['start_date' => $start_date, 'end_date' => $end_date]);
    } else {
        $stmt->execute();
    }
    
    $report_data = $stmt->fetchAll();
    
    // Hitung akumulasi total pengeluaran dana corpro
    $grand_total = 0;
    foreach ($report_data as $row) {
        $grand_total += $row['total_harga'];
    }

} catch (PDOException $e) {
    die("Gagal memuat laporan: " . $e->getMessage());
}
?>

<style>
    .filter-card { background: #fff; border-radius: 12px; padding: 20px; box-shadow: var(--shadow-soft); border: 1px solid var(--border-color); margin-bottom: 25px; }
    .filter-form { display: flex; gap: 15px; align-items: flex-end; flex-wrap: wrap; }
    .form-group { display: flex; flex-direction: column; gap: 6px; }
    .form-label { font-size: 12px; font-weight: 600; color: #475569; }
    .form-input { padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; color: #334155; outline: none; }
    
    .btn-filter { background: #0f172a; color: white; border: none; padding: 11px 20px; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 14px; }
    .btn-filter:hover { background: #1e293b; }
    .btn-print { background: #16a34a; color: white; border: none; padding: 11px 20px; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 14px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; }
    .btn-print:hover { background: #15803d; }
    .btn-reset { background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; padding: 11px 20px; border-radius: 8px; text-decoration: none; font-size: 14px; font-weight: 500; }
    
    .table-container { background: #fff; border-radius: 12px; box-shadow: var(--shadow-soft); overflow-x: auto; border: 1px solid var(--border-color); }
    .modern-table { width: 100%; border-collapse: collapse; text-align: left; font-size: 14px; }
    .modern-table th { background: #f8fafc; padding: 16px 20px; color: #475569; font-weight: 600; border-bottom: 1px solid var(--border-color); }
    .modern-table td { padding: 16px 20px; color: #334155; border-bottom: 1px solid var(--border-color); }
    
    .summary-box { margin-top: 20px; text-align: right; background: #f8fafc; padding: 20px; border-radius: 12px; border: 1px solid var(--border-color); }

    /* CSS ATURAN CETAK (PRINT HACKS) */
    @media print {
        body * { visibility: hidden; }
        #print-area, #print-area * { visibility: visible; }
        #print-area { position: absolute; left: 0; top: 0; width: 100%; }
        .filter-card, .sidebar, .navbar, .btn-print, .btn-filter, .btn-reset, header, footer { display: none !important; }
    }
</style>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
    <div>
        <h1 style="font-size: 22px; font-weight: 700; color: #0f172a;">Laporan Realisasi Pengadaan</h1>
        <p style="color: #64748b; font-size: 13px; margin-top: 2px;">Data ledger komparatif seluruh transaksi e-Purchasing yang sukses dilakukan.</p>
    </div>
    <button onclick="window.print()" class="btn-print">
        <i class="fa-solid fa-print"></i> Cetak Laporan (PDF)
    </button>
</div>

<div class="filter-card">
    <form action="reports.php" method="GET" class="filter-form">
        <div class="form-group">
            <label class="form-label">Tanggal Mulai</label>
            <input type="date" name="start_date" class="form-input" value="<?= htmlspecialchars($start_date); ?>">
        </div>
        <div class="form-group">
            <label class="form-label">Tanggal Selesai</label>
            <input type="date" name="end_date" class="form-input" value="<?= htmlspecialchars($end_date); ?>">
        </div>
        <button type="submit" class="btn-filter"><i class="fa-solid fa-magnifying-glass"></i> Filter</button>
        <?php if (!empty($start_date)): ?>
            <a href="reports.php" class="btn-reset">Reset</a>
        <?php endif; ?>
    </form>
</div>

<div id="print-area">
    <div class="table-container">
        <table class="modern-table">
            <thead>
                <tr>
                    <th>No. Transaksi</th>
                    <th>Tanggal</th>
                    <th>No. Request</th>
                    <th>Staff Pengaju</th>
                    <th>Nama Barang</th>
                    <th style="text-align: right;">Harga Satuan</th>
                    <th style="text-align: center;">Kuantitas</th>
                    <th style="text-align: right;">Total Pengeluaran</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($report_data) === 0): ?>
                    <tr>
                        <td colspan="8" style="text-align: center; color: #94a3b8; padding: 40px;">Tidak ada riwayat transaksi pada periode terpilih.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($report_data as $r): ?>
                        <tr>
                            <td style="font-weight: 700; color: #0f172a;"><?= htmlspecialchars($r['no_transaksi']); ?></td>
                            <td><?= date('d/m/Y', strtotime($r['tanggal_transaksi'])); ?></td>
                            <td style="color: #64748b; font-size: 13px;"><?= htmlspecialchars($r['no_request']); ?></td>
                            <td><?= htmlspecialchars($r['nama_staff']); ?></td>
                            <td style="font-weight: 500;"><?= htmlspecialchars($r['nama_barang']); ?></td>
                            <td style="text-align: right;">Rp <?= number_format($r['harga_satuan'], 0, ',', '.'); ?></td>
                            <td style="text-align: center;"><?= htmlspecialchars($r['jumlah']); ?></td>
                            <td style="text-align: right; font-weight: 600; color: #1e3a8a;">Rp <?= number_format($r['total_harga'], 0, ',', '.'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="summary-box">
        <span style="font-size: 14px; color: #64748b; font-weight: 500;">TOTAL ALOKASI ANGGARAN KORPORASI KELUAR:</span>
        <h2 style="font-size: 24px; font-weight: 800; color: #16a34a; margin-top: 5px;">Rp <?= number_format($grand_total, 0, ',', '.'); ?></h2>
    </div>
</div>

<?php include_once __DIR__ . '/../layouts/footer.php'; ?>