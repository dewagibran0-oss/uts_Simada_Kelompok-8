<?php
// admin/index.php
require_once '../config/database.php';
include_once '../layouts/header.php';
include_once '../layouts/sidebar.php';
include_once '../layouts/navbar.php';

// Proteksi Tambahan: Pastikan hanya Admin yang bisa membuka halaman dashboard ini
if ($_SESSION['role'] !== 'Admin') {
    echo "<div style='padding:30px;' class='alert env-badge'><h3>Akses Ditolak!</h3>Halaman ini hanya untuk Administrator.</div>";
    include_once '../layouts/footer.php';
    exit();
}

// Mengambil Data Metrik dari Database secara Real-time
try {
    $total_vendor = $pdo->query("SELECT COUNT(*) FROM vendors")->fetchColumn();
    $total_barang = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
    $total_request = $pdo->query("SELECT COUNT(*) FROM purchase_requests")->fetchColumn();
} catch (PDOException $e) {
    $total_vendor = $total_barang = $total_request = 0;
}
?>

<div class="dashboard-header" style="margin-bottom: 25px;">
    <h1 style="font-size: 24px; font-weight: 700; color: #0f172a;">Selamat Datang Kembali, <?= htmlspecialchars($_SESSION['nama']); ?>!</h1>
    <p style="color: #64748b; font-size: 14px; margin-top: 4px;">Berikut adalah ringkasan performa sistem e-Purchasing SIMADA hari ini.</p>
</div>

<!-- Grid Metric Cards Dashboard -->
<div class="dashboard-grid">
    <div class="card-metric">
        <div class="metric-info">
            <p>Total Mitra Vendor</p>
            <h3><?= $total_vendor; ?></h3>
        </div>
        <div class="metric-icon icon-blue">
            <i class="fa-solid fa-truck-field"></i>
        </div>
    </div>

    <div class="card-metric">
        <div class="metric-info">
            <p>Katalog Barang</p>
            <h3><?= $total_barang; ?></h3>
        </div>
        <div class="metric-icon icon-green">
            <i class="fa-solid fa-boxes-stacked"></i>
        </div>
    </div>

    <div class="card-metric">
        <div class="metric-info">
            <p>Pengajuan Pengadaan</p>
            <h3><?= $total_request; ?></h3>
        </div>
        <div class="metric-icon icon-orange">
            <i class="fa-solid fa-file-invoice-dollar"></i>
        </div>
    </div>
</div>

<div style="background: white; padding: 25px; border-radius: 12px; box-shadow: var(--shadow-soft);">
    <h2 style="font-size: 16px; font-weight: 600; margin-bottom: 10px; color: #0f172a;">Sistem Informasi Manajemen Pengadaan Barang (SIMADA)</h2>
    <p style="font-size: 14px; color: #475569; line-height: 1.6;">
        Gunakan menu di samping kiri untuk mengelola master data vendor dan katalog barang pengadaan. Anda memiliki hak akses penuh sebagai <strong>Administrator</strong> tingkat korporasi/pemerintahan. Seluruh aktivitas data pada sistem ini direkam ke dalam log enkripsi audit trail demi keamanan sistem transaksi.
    </p>
</div>

<?php
include_once '../layouts/footer.php';
?>