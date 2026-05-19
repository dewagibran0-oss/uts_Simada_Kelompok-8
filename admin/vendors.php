<?php
// admin/vendors.php
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
// LOGIKA PROSES HAPUS DATA (DELETE)
// ==========================================
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id_vendor = (int)$_GET['id'];
    
    try {
        $stmt_delete = $pdo->prepare("DELETE FROM vendors WHERE id_vendor = :id");
        $stmt_delete->execute(['id' => $id_vendor]);
        
        // Redirect dengan status sukses hps
        echo "<script>window.location.href='vendors.php?msg=deleted';</script>";
        exit();
    } catch (PDOException $e) {
        echo "<script>alert('Gagal menghapus vendor karena data ini masih digunakan di tabel produk!'); window.location.href='vendors.php';</script>";
        exit();
    }
}

// ==========================================
// QUERY AMBIL DATA VENDOR (READ)
// ==========================================
try {
    $stmt = $pdo->query("SELECT * FROM vendors ORDER BY id_vendor DESC");
    $vendors = $stmt->fetchAll();
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
    .badge-status { padding: 4px 10px; border-radius: 12px; font-size: 12px; font-weight: 500; }
    .badge-aktif { background: #e6f4ea; color: #137333; }
    .btn-delete { background: #fee2e2; color: #b91c1c; border: none; padding: 6px 12px; border-radius: 6px; cursor: pointer; font-size: 13px; text-decoration: none; font-weight: 500; transition: all 0.2s; }
    .btn-delete:hover { background: #fca5a5; }
    .alert-success { background: #f0fdf4; border: 1px solid #bbf7d0; color: #16a34a; padding: 15px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; }
</style>

<div class="crud-header">
    <div>
        <h1 style="font-size: 22px; font-weight: 700; color: #0f172a;">Data Mitra Vendor</h1>
        <p style="color: #64748b; font-size: 13px; margin-top: 2px;">Kelola penyedia barang/jasa resmi yang terintegrasi dengan e-Purchasing SIMADA.</p>
    </div>
    <a href="vendor-add.php" class="btn-primary">
        <i class="fa-solid fa-plus"></i> Tambah Vendor Baru
    </a>
</div>

<?php if (isset($_GET['msg'])): ?>
    <div class="alert-success">
        <i class="fa-solid fa-circle-check"></i> 
        <?php 
            if ($_GET['msg'] === 'success') echo "Data Vendor baru berhasil didaftarkan ke sistem!";
            if ($_GET['msg'] === 'deleted') echo "Data Vendor berhasil dihapus dari sistem.";
        ?>
    </div>
<?php endif; ?>

<div class="table-container">
    <table class="modern-table">
        <thead>
            <tr>
                <th>Nama Perusahaan</th>
                <th>Nama Kontak</th>
                <th>Email Vendor</th>
                <th>No. Telepon</th>
                <th>Alamat Perusahaan</th>
                <th>Status</th>
                <th style="text-align: center;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($vendors) === 0): ?>
                <tr>
                    <td colspan="7" style="text-align: center; color: #94a3b8; padding: 30px;">Belum ada data vendor terdaftar.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($vendors as $v): ?>
                    <tr>
                        <td style="font-weight: 600; color: #0f172a;"><?= htmlspecialchars($v['nama_vendor']); ?></td>
                        <td><?= htmlspecialchars($v['nama_kontak']); ?></td>
                        <td><?= htmlspecialchars($v['email']); ?></td>
                        <td><?= htmlspecialchars($v['telepon']); ?></td>
                        <td style="max-width: 250px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?= htmlspecialchars($v['alamat']); ?></td>
                        <td>
                            <span class="badge-status badge-aktif"><?= htmlspecialchars($v['status']); ?></span>
                        </td>
                        <td style="text-align: center;">
                            <a href="vendors.php?action=delete&id=<?= $v['id_vendor']; ?>" 
                               class="btn-delete" 
                               onclick="return confirm('Apakah Anda yakin ingin menghapus vendor <?= htmlspecialchars($v['nama_vendor']); ?>? Semua produk terkait vendor ini juga akan terhapus.')">
                                <i class="fa-solid fa-trash-can"></i> Hapus
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include_once __DIR__ . '/../layouts/footer.php'; ?>