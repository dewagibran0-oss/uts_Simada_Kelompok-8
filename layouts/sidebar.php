<?php
// layouts/sidebar.php
$current_page = basename($_SERVER['PHP_SELF']);
$role = $_SESSION['role'];
?>
<aside class="sidebar">
    <div class="sidebar-brand">
        <i class="fa-solid fa-layer-group brand-icon"></i>
        <div>
            <h2>SIMADA</h2>
            <span>e-Purchasing v1.0</span>
        </div>
    </div>
    
    <div class="sidebar-user">
        <div class="user-avatar">
            <i class="fa-solid fa-user-shield"></i>
        </div>
        <div class="user-info">
            <span class="user-name"><?= htmlspecialchars($_SESSION['nama']); ?></span>
            <span class="user-role"><?= htmlspecialchars($role); ?></span>
        </div>
    </div>

    <nav class="sidebar-menu">
        <p class="menu-label">Main Menu</p>
        <ul>
            <!-- Menu Dashboard tersedia untuk semua role -->
            <li class="<?= $current_page == 'index.php' ? 'active' : ''; ?>">
                <a href="index.php"><i class="fa-solid fa-chart-pie"></i> Dashboard</a>
            </li>

            <!-- MENU KHUSUS ADMIN -->
            <?php if ($role === 'Admin'): ?>
                <li class="<?= $current_page == 'vendors.php' ? 'active' : ''; ?>">
                    <a href="vendors.php"><i class="fa-solid fa-truck-field"></i> Kelola Vendor</a>
                </li>
                <li class="<?= $current_page == 'products.php' ? 'active' : ''; ?>">
                    <a href="products.php"><i class="fa-solid fa-boxes-stacked"></i> Kelola Barang</a>
                </li>
            <?php endif; ?>

            <!-- MENU KHUSUS STAFF PURCHASING -->
            <?php if ($role === 'Staff Purchasing'): ?>
                <li class="<?= $current_page == 'request.php' ? 'active' : ''; ?>">
                    <a href="request.php"><i class="fa-solid fa-file-invoice-dollar"></i> Purchase Request</a>
                </li>
                <li class="<?= $current_page == 'transactions.php' ? 'active' : ''; ?>">
                    <a href="transactions.php"><i class="fa-solid fa-receipt"></i> Data Transaksi</a>
                </li>
            <?php endif; ?>

            <!-- MENU KHUSUS MANAGER -->
            <?php if ($role === 'Manager'): ?>
                <li class="<?= $current_page == 'approval.php' ? 'active' : ''; ?>">
                    <a href="approval.php"><i class="fa-solid fa-clipboard-check"></i> Approval Request</a>
                </li>
            <?php endif; ?>
            
            <p class="menu-label">Laporan & Sistem</p>
            <li class="<?= $current_page == 'reports.php' ? 'active' : ''; ?>">
                <a href="reports.php"><i class="fa-solid fa-file-shield"></i> Laporan Pengadaan</a>
            </li>
            
            <li class="logout-item">
                <a href="../auth/logout.php" onclick="return confirm('Apakah Anda yakin ingin keluar dari sistem?')">
                    <i class="fa-solid fa-right-from-bracket"></i> Keluar Sistem
                </a>
            </li>
        </ul>
    </nav>
</aside>