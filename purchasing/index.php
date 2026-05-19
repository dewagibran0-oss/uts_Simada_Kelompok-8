<?php
// purchasing/index.php
require_once __DIR__ . '/../config/database.php';
include_once __DIR__ . '/../layouts/header.php';
include_once __DIR__ . '/../layouts/sidebar.php';
include_once __DIR__ . '/../layouts/navbar.php';

// Proteksi Hak Akses: Pastikan hanya Staff Purchasing yang bisa mengakses halaman ini
if ($_SESSION['role'] !== 'Staff Purchasing') {
    echo "<div style='padding:30px; background:#fee2e2; color:#991b1b; border:1px solid #fca5a5; border-radius:8px;'>";
    echo "<h3>Akses Ditolak!</h3>Halaman ini hanya untuk Staff Purchasing.</div>";
    include_once __DIR__ . '/../layouts/footer.php';
    exit();
}

$id_user_staff = $_SESSION['user_id'];

// Mengambil Data Metrik Pengajuan Khusus Staff yang Sedang Login secara Real-time
try {
    // 1. Total Semua Pengajuan dari Staff Ini
    $stmt_all = $pdo->prepare("SELECT COUNT(*) FROM purchase_requests WHERE id_user = :id_user");
    $stmt_all->execute(['id_user' => $id_user_staff]);
    $total_pengajuan = $stmt_all->fetchColumn();

    // 2. Total Pengajuan yang Masih Pending
    $stmt_pending = $pdo->prepare("SELECT COUNT(*) FROM purchase_requests WHERE id_user = :id_user AND status = 'Pending'");
    $stmt_pending->execute(['id_user' => $id_user_staff]);
    $total_pending = $stmt_pending->fetchColumn();

    // 3. Total Pengajuan yang Berhasil Disetujui (Approved)
    $stmt_approved = $pdo->prepare("SELECT COUNT(*) FROM purchase_requests WHERE id_user = :id_user AND status = 'Approved'");
    $stmt_approved->execute(['id_user' => $id_user_staff]);
    $total_approved = $stmt_approved->fetchColumn();

} catch (PDOException $e) {
    $total_pengajuan = $total_pending = $total_approved = 0;
}
?>

<div class="dashboard-header" style="margin-bottom: 25px;">
    <h1 style="font-size: 24px; font-weight: 700; color: #0f172a;">Dashboard Staff Purchasing</h1>
    <p style="color: #64748b; font-size: 14px; margin-top: 4px;">Selamat datang, <strong><?= htmlspecialchars($_SESSION['nama']); ?></strong>. Pantau status alokasi pengadaan komoditas Anda.</p>
</div>

<div class="dashboard-grid">
    <div class="card-metric">
        <div class="metric-info">
            <p>Total Pengajuan Saya</p>
            <h3><?= $total_pengajuan; ?></h3>
        </div>
        <div class="metric-icon icon-blue">
            <i class="fa-solid fa-folder-open"></i>
        </div>
    </div>

    <div class="card-metric">
        <div class="metric-info">
            <p>Menunggu Persetujuan</p>
            <h3><?= $total_pending; ?></h3>
        </div>
        <div class="metric-icon icon-orange">
            <i class="fa-solid fa-hourglass-half"></i>
        </div>
    </div>

    <div class="card-metric">
        <div class="metric-info">
            <p>Pengajuan Disetujui</p>
            <h3><?= $total_approved; ?></h3>
        </div>
        <div class="metric-icon icon-green">
            <i class="fa-solid fa-circle-check"></i>
        </div>
    </div>
</div>

<div style="background: white; padding: 25px; border-radius: 12px; box-shadow: var(--shadow-soft); border: 1px solid var(--border-color);">
    <h2 style="font-size: 16px; font-weight: 600; margin-bottom: 12px; color: #0f172a;">Panduan Operasional Alur Pengadaan (e-Purchasing)</h2>
    <ol style="font-size: 14px; color: #475569; line-height: 1.8; padding-left: 20px;">
        <li>Buka menu <strong>Purchase Request</strong> pada panel menu di samping kiri untuk mengelola dokumen ajuan.</li>
        <li>Klik tombol <em>"Buat Pengajuan Baru"</em> untuk memilih barang kebutuhan dari katalog vendor aktif.</li>
        <li>Sistem akan otomatis menghitung estimasi anggaran dan mengirimkan dokumen ke **Manager** untuk diverifikasi.</li>
        <li>Jika status berubah menjadi <span style="color:#16a34a; font-weight:600;">Approved</span>, transaksi pengadaan akan otomatis diproses ke lembar data transaksi operasional.</li>
    </ol>
</div>

<?php
include_once __DIR__ . '/../layouts/footer.php';
?>