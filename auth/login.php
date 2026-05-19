<?php
// auth/login.php
session_start();
// Jika sudah login, paksa masuk ke halaman dashboard masing-masing, tidak perlu login lagi
if (isset($_SESSION['is_logged']) && $_SESSION['is_logged'] === true) {
    if ($_SESSION['role'] === 'Admin') header("Location: ../admin/index.php");
    if ($_SESSION['role'] === 'Staff Purchasing') header("Location: ../purchasing/index.php");
    if ($_SESSION['role'] === 'Manager') header("Location: ../manager/index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIMADA - e-Purchasing Login</title>
    <!-- Gunakan Google Fonts Inter untuk kesan modern & clean -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- FontAwesome untuk icon modern -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0; padding: 0; box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }
        body {
            background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 100%);
            min-height: 100vh;
            display: flex; align-items: center; justify-content: center;
            padding: 20px;
        }
        .login-container {
            background: #ffffff;
            width: 100%; max-width: 420px;
            border-radius: 12px;
            padding: 40px 30px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s ease;
        }
        .login-header {
            text-align: center; margin-bottom: 30px;
        }
        .login-header h2 {
            color: #0f172a; font-size: 24px; font-weight: 700; margin-bottom: 5px;
        }
        .login-header p {
            color: #64748b; font-size: 14px;
        }
        .form-group {
            margin-bottom: 20px; position: relative;
        }
        .form-group label {
            display: block; color: #334155; font-size: 13px; font-weight: 500; margin-bottom: 6px;
        }
        .input-icon-wrapper {
            position: relative;
        }
        .input-icon-wrapper i {
            position: absolute; left: 14px; top: 50%; transform: translateY(-50%);
            color: #94a3b8; font-size: 16px;
        }
        .form-control {
            width: 100%; padding: 12px 14px 12px 42px;
            border: 1px solid #cbd5e1; border-radius: 8px;
            font-size: 14px; color: #334155; outline: none;
            transition: all 0.3s ease;
        }
        .form-control:focus {
            border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
        }
        .btn-login {
            width: 100%; background: #2563eb; color: #ffffff;
            border: none; padding: 12px; border-radius: 8px;
            font-size: 15px; font-weight: 600; cursor: pointer;
            transition: background 0.3s ease; margin-top: 10px;
        }
        .btn-login:hover { background: #1d4ed8; }
        .alert {
            padding: 12px; border-radius: 6px; font-size: 13px; margin-bottom: 20px;
            display: flex; align-items: center; gap: 8px;
        }
        .alert-danger { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
    </style>
</head>
<body>

<div class="login-container">
    <div class="login-header">
        <h2>SIMADA Enterprise</h2>
        <p>e-Purchasing Pengadaan Barang & Jasa</p>
    </div>

    <!-- Menangkap Trigger Error dari URL Parameter -->
    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger">
            <i class="fa-solid fa-circle-exclamation"></i>
            <?php 
                if($_GET['error'] == 'invalid') echo "Username atau Password salah!";
                elseif($_GET['error'] == 'empty') echo "Semua form wajib diisi!";
                else echo "Terjadi kesalahan sistem, coba lagi.";
            ?>
        </div>
    <?php endif; ?>

    <form action="process-login.php" method="POST" autocomplete="off">
        <div class="form-group">
            <label for="username">Username Sistem</label>
            <div class="input-icon-wrapper">
                <i class="fa-solid fa-user"></i>
                <input type="text" id="username" name="username" class="form-control" placeholder="Masukkan username" required>
            </div>
        </div>

        <div class="form-group">
            <label for="password">Kata Sandi</label>
            <div class="input-icon-wrapper">
                <i class="fa-solid fa-lock"></i>
                <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" required>
            </div>
        </div>

        <button type="submit" class="btn-login">Masuk ke Sistem <i class="fa-solid fa-arrow-right-to-bracket" style="margin-left:5px"></i></button>
    </form>
</div>

</body>
</html>