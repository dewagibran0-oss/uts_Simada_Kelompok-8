<?php
// config/database.php

// 1. Baca data dari Environment Variables (Vercel)
// Jika tidak ada (di PC lokal), maka otomatis pakai nilai default XAMPP (localhost, root, "")
$host     = getenv('DB_HOST') ?: 'localhost';
$db_name  = getenv('DB_NAME') ?: 'db_simada_epurchasing';
$username = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASS') ?: ''; 
$port     = getenv('DB_PORT') ?: '3306';

try {
    // 2. Setup DSN Koneksi PDO
    $dsn = "mysql:host=$host;dbname=$db_name;port=$port;charset=utf8mb4";
    
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    $pdo = new PDO($dsn, $username, $password, $options);
    
} catch (PDOException $e) {
    // Jika gagal terkoneksi
    die("Koneksi database SIMADA gagal: " . $e->getMessage());
}