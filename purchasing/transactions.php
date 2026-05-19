<?php
// purchasing/transactions.php
require_once __DIR__ . '/../config/database.php';
include_once __DIR__ . '/../layouts/header.php';
include_once __DIR__ . '/../layouts/sidebar.php';
include_once __DIR__ . '/../layouts/navbar.php';

if ($_SESSION['role'] !== 'Staff Purchasing') {
    echo "<div style='padding:30px;'><h3>Akses Ditolak!</h3></div>";
    include_once __DIR__ . '/../layouts/footer.php';
    exit();
}

$id_user_staff = $_SESSION['user_id'];

// QUERY DENGAN MULTI-JOIN UNTUK MENGAMBIL LOG AUDIT YANG NYATA (TERMASUK NAMA MANAGER & CATATANNYA)
// QUERY MULTI-JOIN YANG SUDAH DISESUAIKAN (MENGAPUS ap.waktu_approval AGAR TIDAK ERROR)
try {
    $sql = "SELECT t.no_transaksi, t.tanggal_transaksi, pr.no_request, pr.jumlah, pr.total_harga, pr.catatan_staff,
                   p.nama_barang, p.harga AS harga_satuan, p.satuan, v.nama_vendor,
                   u_mgr.nama_lengkap AS nama_manager, ap.catatan_manager
            FROM transactions t
            JOIN purchase_requests pr ON t.id_request = pr.id_request
            JOIN products p ON pr.id_product = p.id_product
            JOIN vendors v ON p.id_vendor = v.id_vendor
            LEFT JOIN approvals ap ON pr.id_request = ap.id_request AND ap.status_approval = 'Approved'
            LEFT JOIN users u_mgr ON ap.id_user = u_mgr.id_user
            WHERE pr.id_user = :id_user
            ORDER BY t.id_transaction DESC";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id_user' => $id_user_staff]);
    $transactions = $stmt->fetchAll();
    
} catch (PDOException $e) {
    die("Gagal memuat data transaksi: " . $e->getMessage());
}
?>

<style>
    :root {
        --primary-dark: #0f172a;
        --success-green: #10b981;
    }
    .table-container { background: #fff; border-radius: 12px; box-shadow: var(--shadow-soft); overflow-x: auto; border: 1px solid var(--border-color); }
    .modern-table { width: 100%; border-collapse: collapse; text-align: left; font-size: 14px; }
    .modern-table th { background: #f8fafc; padding: 16px 20px; color: #475569; font-weight: 600; border-bottom: 1px solid var(--border-color); }
    .modern-table td { padding: 16px 20px; color: #334155; border-bottom: 1px solid var(--border-color); vertical-align: middle; }
    .modern-table tr:hover { background: #f8fafc; }
    
    .badge-invoice { background: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0; padding: 6px 12px; border-radius: 6px; font-weight: 700; font-family: 'Courier New', Courier, monospace; font-size: 13px; }
    .btn-view-invoice { background: #0f172a; color: white; border: none; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; transition: background 0.2s; }
    .btn-view-invoice:hover { background: #1e293b; }

    /* STYLE MODAL POPUP NOTA INTERAKTIF */
    .invoice-modal { display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); align-items: center; justify-content: center; }
    .invoice-paper { background: white; width: 100%; max-width: 650px; border-radius: 16px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); overflow: hidden; animation: slideUp 0.3s ease-out; position: relative; }
    
    .invoice-header { background: #0f172a; color: white; padding: 25px; display: flex; justify-content: space-between; align-items: center; }
    .invoice-body { padding: 35px; background: #fff; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
    
    .company-logo { font-size: 22px; font-weight: 800; letter-spacing: 1px; color: #fff; }
    .company-logo span { color: #38bdf8; }
    
    .billing-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px; font-size: 13px; color: #475569; line-height: 1.6; }
    .billing-title { font-weight: 700; color: #0f172a; text-transform: uppercase; margin-bottom: 5px; font-size: 11px; letter-spacing: 0.5px; }
    
    .item-receipt-table { width: 100%; border-collapse: collapse; margin-top: 15px; margin-bottom: 25px; }
    .item-receipt-table th { background: #f8fafc; border-bottom: 2px solid #e2e8f0; padding: 10px; font-size: 12px; font-weight: 700; color: #475569; }
    .item-receipt-table td { padding: 12px 10px; border-bottom: 1px solid #f1f5f9; font-size: 13px; }
    
    .stamp-approved { border: 3px dashed #16a34a; color: #16a34a; text-transform: uppercase; font-size: 14px; font-weight: 800; padding: 6px 15px; display: inline-block; transform: rotate(-8deg); border-radius: 6px; font-family: Arial, Helvetica, sans-serif; letter-spacing: 1px; box-shadow: 0 0 0 2px #16a34a; opacity: 0.85; }
    
    @keyframes slideUp { from { transform: translateY(30px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
</style>

<div style="margin-bottom: 25px;">
    <h1 style="font-size: 22px; font-weight: 700; color: #0f172a;">Buku Besar Nota Transaksi</h1>
    <p style="color: #64748b; font-size: 13px; margin-top: 2px;">Klik aksi cetak untuk melihat lembar kwitansi resmi korporasi yang terbit otomatis.</p>
</div>

<div class="table-container">
    <table class="modern-table">
        <thead>
            <tr>
                <th>No. Nota Transaksi</th>
                <th>Tanggal Rilis</th>
                <th>No. Request Asal</th>
                <th>Spesifikasi Barang</th>
                <th style="text-align: right;">Total Pengeluaran</th>
                <th style="text-align: center;">Otorisator (Manager)</th>
                <th style="text-align: center;">Aksi Dokumen</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($transactions) === 0): ?>
                <tr>
                    <td colspan="7" style="text-align: center; color: #94a3b8; padding: 40px;">
                        <i class="fa-solid fa-receipt" style="font-size: 24px; margin-bottom: 10px; display: block; color: #cbd5e1;"></i>
                        Belum ada kuitansi transaksi yang diterbitkan untuk akun Anda.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($transactions as $t): ?>
                    <tr>
                        <td><span class="badge-invoice"><i class="fa-solid fa-file-invoice" style="margin-right: 5px;"></i><?= htmlspecialchars($t['no_transaksi']); ?></span></td>
                        <td><?= date('d M Y', strtotime($t['tanggal_transaksi'])); ?></td>
                        <td style="color: #64748b; font-size: 13px; font-weight: 500;"><?= htmlspecialchars($t['no_request']); ?></td>
                        <td style="font-weight: 600; color: #0f172a;"><?= htmlspecialchars($t['nama_barang']); ?> <span style="font-weight: 400; font-size:12px; color:#64748b;">(<?= $t['jumlah']; ?> <?= $t['satuan']; ?>)</span></td>
                        <td style="text-align: right; font-weight: 700; color: #1e3a8a;">Rp <?= number_format($t['total_harga'], 0, ',', '.'); ?></td>
                        <td style="text-align: center; font-size: 13px; font-weight: 500; color: #475569;"><i class="fa-solid fa-user-shield" style="font-size:11px; margin-right:4px; color:#16a34a;"></i> <?= htmlspecialchars($t['nama_manager'] ?: 'System'); ?></td>
                        <td style="text-align: center;">
                            <button class="btn-view-invoice" onclick="openInvoice(this)" 
                                    data-notrx="<?= $t['no_transaksi']; ?>"
                                    data-tgl="<?= date('d F Y', strtotime($t['tanggal_transaksi'])); ?>"
                                    data-noreq="<?= $t['no_request']; ?>"
                                    data-barang="<?= htmlspecialchars($t['nama_barang']); ?>"
                                    data-vendor="<?= htmlspecialchars($t['nama_vendor']); ?>"
                                    data-harga="Rp <?= number_format($t['harga_satuan'], 0, ',', '.'); ?>"
                                    data-qty="<?= $t['jumlah']; ?> <?= $t['satuan']; ?>"
                                    data-total="Rp <?= number_format($t['total_harga'], 0, ',', '.'); ?>"
                                    data-mgr="<?= htmlspecialchars($t['nama_manager'] ?: 'Executive Manager'); ?>"
                                    data-catatanmgr="<?= htmlspecialchars($t['catatan_manager'] ?: 'Valid & Approved'); ?>"
                                    data-catatanstaff="<?= htmlspecialchars($t['catatan_staff'] ?: '-'); ?>">
                                <i class="fa-solid fa-receipt"></i> Nota Resmi
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div id="invoiceModal" class="invoice-modal">
    <div class="invoice-paper">
        <div class="invoice-header">
            <div class="company-logo">SIMADA<span>.ERP</span></div>
            <div style="text-align: right;">
                <h4 style="margin: 0; font-size: 14px; text-transform: uppercase; letter-spacing: 1px;">Kuitansi Belanja Resmi</h4>
                <p id="inv-no" style="margin: 3px 0 0 0; font-family: monospace; font-size: 13px; color: #94a3b8;"></p>
            </div>
        </div>
        
        <div class="invoice-body">
            <div class="billing-grid">
                <div>
                    <div class="billing-title">Diterbitkan Oleh:</div>
                    <strong>Departemen Logistik Korporat</strong><br>
                    SIMADA Asset Management System<br>
                    Sistem Digitalisasi Dokumen Mandiri
                </div>
                <div style="text-align: right;">
                    <div class="billing-title">Detail Berkas Dokumen:</div>
                    Tanggal Rilis: <span id="inv-tgl" style="font-weight:600; color:#0f172a;"></span><br>
                    ID Pengajuan: <span id="inv-noreq" style="font-weight:600; color:#0f172a;"></span><br>
                    Mitra Penyedia: <span id="inv-vendor" style="font-weight:600; color:#0f172a;"></span>
                </div>
            </div>

            <table class="item-receipt-table">
                <thead>
                    <tr>
                        <th style="text-align: left;">Deskripsi Komoditas Barang</th>
                        <th style="text-align: right;">Harga Satuan</th>
                        <th style="text-align: center; width: 80px;">Qty</th>
                        <th style="text-align: right; width: 140px;">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td id="inv-barang" style="font-weight: 600; color: #0f172a;"></td>
                        <td id="inv-harga" style="text-align: right;"></td>
                        <td id="inv-qty" style="text-align: center; font-weight: 600;"></td>
                        <td id="inv-total" style="text-align: right; font-weight: 700; color: #1e3a8a;"></td>
                    </tr>
                </tbody>
            </table>

            <div class="billing-grid" style="margin-top: 10px; border-top: 1px dashed #cbd5e1; padding-top: 20px;">
                <div>
                    <div class="billing-title">Justifikasi Operasional Staff:</div>
                    <span id="inv-catatan-staff" style="font-style: italic; font-size:12px;"></span>
                </div>
                <div style="text-align: center; position: relative;">
                    <div class="billing-title" style="margin-bottom: 15px;">Validitas Status Finansial:</div>
                    <div class="stamp-approved">APPROVED</div>
                    <div style="font-size: 11px; margin-top: 8px; font-weight: 600; color: #334155;" id="inv-mgr"></div>
                    <div style="font-size: 10px; color: #64748b; font-style: italic; margin-top: 2px;" id="inv-catatan-mgr"></div>
                </div>
            </div>

            <div style="margin-top: 35px; display: flex; justify-content: flex-end; gap: 10px;">
                <button onclick="closeInvoice()" style="background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; padding: 10px 20px; border-radius: 8px; cursor: pointer; font-weight: 500; font-size:13px;">Tutup</button>
                <button onclick="window.print()" style="background: #16a34a; color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; font-weight: 600; font-size:13px;"><i class="fa-solid fa-print"></i> Print Nota</button>
            </div>
        </div>
    </div>
</div>

<script>
function openInvoice(button) {
    // Ambil data yang tersimpan dari atribut tombol yang di-klik
    document.getElementById('inv-no').innerText = button.getAttribute('data-notrx');
    document.getElementById('inv-tgl').innerText = button.getAttribute('data-tgl');
    document.getElementById('inv-noreq').innerText = button.getAttribute('data-noreq');
    document.getElementById('inv-barang').innerText = button.getAttribute('data-barang');
    document.getElementById('inv-vendor').innerText = button.getAttribute('data-vendor');
    document.getElementById('inv-harga').innerText = button.getAttribute('data-harga');
    document.getElementById('inv-qty').innerText = button.getAttribute('data-qty');
    document.getElementById('inv-total').innerText = button.getAttribute('data-total');
    document.getElementById('inv-mgr').innerText = "Oleh: " + button.getAttribute('data-mgr');
    document.getElementById('inv-catatan-mgr').innerText = `"${button.getAttribute('data-catatanmgr')}"`;
    document.getElementById('inv-catatan-staff').innerText = `"${button.getAttribute('data-catatanstaff')}"`;

    // Tampilkan modal dengan transisi flexbox
    document.getElementById('invoiceModal').style.display = 'flex';
}

function closeInvoice() {
    document.getElementById('invoiceModal').style.display = 'none';
}

// Deteksi jika user mengklik area gelap di luar kertas kuitansi, modal otomatis tertutup
window.onclick = function(event) {
    let modal = document.getElementById('invoiceModal');
    if (event.target == modal) {
        modal.style.display = "none";
    }
}
</script>

<style>
@media print {
    body * { visibility: hidden; }
    #invoiceModal, #invoiceModal * { visibility: visible; }
    #invoiceModal { position: absolute; left: 0; top: 0; width: 100%; height: 100%; background: white !important; display: flex !important; }
    .invoice-paper { box-shadow: none !important; border: none !important; width: 100% !important; max-width: 100% !important; }
    button { display: none !important; }
}
</style>

<?php include_once __DIR__ . '/../layouts/footer.php'; ?>