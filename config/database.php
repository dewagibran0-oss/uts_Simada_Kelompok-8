<?php
// config/database.php

$host     = "localhost";
$username = "root";
$password = "";
$database = "db_simada_epurchasing";

try {
    // Mengaktifkan koneksi dengan PDO driver
    $pdo = new PDO("mysql:host=$host;dbname=$database;charset=utf8", $username, $password);
    
    // Set error mode ke Exception untuk mempermudah debugging saat development
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Set default fetch mode ke Associative Array agar hasil query berupa array string key
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    // Jika koneksi gagal, hentikan aplikasi dan tampilkan pesan error rapi
    die("Koneksi Database Gagal: " . $e->getMessage());
}
?>