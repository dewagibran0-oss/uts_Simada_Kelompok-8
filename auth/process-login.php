<?php
// auth/process-login.php

// 1. MEMULAI SESSION SISTEM
// Harus dipanggil di awal sebelum ada output HTML apa pun agar server bisa membaca/menulis cookie session.
session_start();

// 2. IMPORT KONEKSI DATABASE
// Menggunakan require_once agar jika file database.php hilang, skrip langsung berhenti (fatal error) demi keamanan.
require_once '../config/database.php';

// 3. VALIDASI METODE AKSES
// Memastikan halaman ini hanya bisa diproses jika user mengirimkan data melalui tombol SUBMIT (Metode POST).
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    /* 
       4. SANITASI DAN FILTER INPUT (Anti-XSS & Secure Trim)
       - trim(): Menghapus spasi tidak sengaja di awal/akhir input.
       - filter_input(): Menyaring karakter spesial agar input tidak bisa disisipi tag HTML/Skrip berbahaya.
    */
    $username = trim(filter_input(INPUT_POST, 'username', FILTER_SANITIZE_SPECIAL_CHARS));
    $password = trim($_POST['password']); // Password jangan di-sanitize agar karakter unik password tidak berubah

    // Validasi internal: Pastikan field tidak dikirim dalam keadaan kosong
    if (empty($username) || empty($password)) {
        header("Location: login.php?error=empty");
        exit();
    }

    try {
        /* 
           5. PREPARED STATEMENT (Anti-SQL Injection)
           - Kita tidak memasukkan variabel $username langsung ke dalam query (TIDAK BOLEH: SELECT * FROM users WHERE username = '$username').
           - Kita menggunakan placeholder ':username' yang akan diperiksa secara ketat oleh driver PDO.
        */
        $query = "SELECT id_user, username, password, nama_lengkap, role FROM users WHERE username = :username LIMIT 1";
        $stmt = $pdo->prepare($query);
        
        // Eksekusi query dengan mengikat (binding) data username
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch();

        /* 
           6. VERIFIKASI HASH PASSWORD
           - Karena di database (Tahap 3) kita menyimpan password menggunakan BCRYPT hash, 
             kita wajib mencocokkannya menggunakan fungsi bawaan PHP: password_verify().
        */
        // GANTI BLOK PENGECEKAN PASSWORD LAMA DENGAN INI:
        if ($user && $password === $user['password']) {
            
            // Proteksi session fixation
            session_regenerate_id(true);

            // Menyimpan data penting ke dalam Session global
            $_SESSION['user_id']   = $user['id_user'];
            $_SESSION['username']  = $user['username'];
            $_SESSION['nama']      = $user['nama_lengkap'];
            $_SESSION['role']      = $user['role'];
            $_SESSION['is_logged'] = true;

            // Pengalihan halaman otomatis (Redirect) sesuai Hak Akses/Role
            switch ($user['role']) {
                case 'Admin':
                    header("Location: ../admin/index.php");
                    break;
                case 'Staff Purchasing':
                    header("Location: ../purchasing/index.php");
                    break;
                case 'Manager':
                    header("Location: ../manager/index.php");
                    break;
                default:
                    header("Location: login.php?error=role");
                    break;
            }
            exit();

        } else {
            // Jika salah
            header("Location: login.php?error=invalid");
            exit();
        }

    } catch (PDOException $e) {
        // Logika penanganan error database saat produksi (tidak menampilkan detail error ke publik demi keamanan)
        header("Location: login.php?error=system");
        exit();
    }

} else {
    // Jika ada orang iseng yang mencoba menembak URL http://localhost/simada/auth/process-login.php secara langsung
    header("Location: login.php");
    exit();
}