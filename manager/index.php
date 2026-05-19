<?php
// manager/index.php
require_once __DIR__ . '/../config/database.php';
include_once __DIR__ . '/../layouts/header.php';
include_once __DIR__ . '/../layouts/sidebar.php';
include_once __DIR__ . '/../layouts/navbar.php';

// Proteksi Hak Akses: Pastikan hanya Manager yang bisa mengakses halaman ini
if ($_SESSION['role'] !== 'Manager') {
    echo "<div style='padding:30px; background:#fee2e2; color:#991b1b; border:1px solid #fca5a5; border-radius:8px;'>";
    echo "<h3>Akses Ditolak!</h3>Halaman ini hanya untuk tingkatan Manager.</div>";
    include_once __DIR__ . '/../layouts/footer.php';
    exit();
}

// Mengambil Data Statistik Pengajuan Global untuk Manager
try {
    $total_butuh_tindakan = $pdo->query("SELECT COUNT(*) FROM purchase_requests WHERE status = 'Pending'")->fetchColumn();
    $total_disetujui = $pdo->query("SELECT COUNT(*) FROM purchase_requests WHERE status = 'Approved'")->fetchColumn();
} catch (PDOException $e) {
    $total_butuh_tindakan = $total_disetujui = 0;
}
?>

<div class="dashboard-header" style="margin-bottom: 25px;">
    <h1 style="font-size: 24px; font-weight: 700; color: #0f172a;">Executive Management Panel</h1>
    <p style="color: #64748b; font-size: 14px; margin-top: 4px;">Selamat datang, <strong><?= htmlspecialchars($_SESSION['nama']); ?></strong>. Tinjau dan lakukan validasi pengadaan komoditas internal.</p>
</div>

<div class="dashboard-grid">
    <div class="card-metric">
        <div class="metric-info">
            <p>Butuh Verifikasi Segera</p>
            <h3 style="color: #dm9706;"><?= $total_butuh_tindakan; ?></h3>
        </div>
        <div class="metric-icon icon-orange">
            <i class="fa-solid fa-triangle-exclamation"></i>
        </div>
    </div>

    <div class="card-metric">
        <div class="metric-info">
            <p>Total PR Telah Disetujui</p>
            <h3><?= $total_disetujui; ?></h3>
        </div>
        <div class="metric-icon icon-green">
            <i class="fa-solid fa-boxes-packing"></i>
        </div>
    </div>
</div>

<div style="background: white; padding: 25px; border-radius: 12px; box-shadow: var(--shadow-soft); border: 1px solid var(--border-color);">
    <h2 style="font-size: 16px; font-weight: 600; margin-bottom: 12px; color: #0f172a;">Otorisasi Pagu Anggaran Corpro</h2>
    <p style="font-size: 14px; color: #475569; line-height: 1.6;">
        Sebagai Manager, Anda memiliki wewenang penuh untuk memeriksa kelayakan justifikasi belanja operasional yang diajukan oleh tim logistik/purchasing. Klik menu <strong>Approval Request</strong> di panel kiri untuk memulai proses peninjauan berkas secara berkala.
    </p>
</div>

<?php
include_once __DIR__ . '/../layouts/footer.php';
?>