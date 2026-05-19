<?php
if ($_SERVER['HTTP_HOST'] == 'localhost' || $_SERVER['HTTP_HOST'] == '127.0.0.1') {
    $host     = 'localhost';
    $db_name  = 'db_simada_epurchasing';          
    $username = 'root';             
    $password = '';                
} else {
    // --- KONFIGURASI NIAGAHOSTER ---
    $host     = 'localhost';        
    $db_name  = 'u437436359_simada';
    $username = 'u437436359_simada';
    $password = 'Simada@12';
}

try {
  
    $dsn = "mysql:host=$host;dbname=$db_name;charset=utf8mb4";
    
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, 
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,      
        PDO::ATTR_EMULATE_PREPARES   => false,                  
    ];

    $pdo = new PDO($dsn, $username, $password, $options);
    
    // Debugging (Opsional): Hapus atau beri komentar jika sudah berjalan lancar
    // echo "Koneksi Berhasil!"; 

} catch (PDOException $e) {
    // Jika koneksi gagal, tampilkan pesan error
    die("Koneksi ke database gagal: " . $e->getMessage());
}

/**
 * Tips Penggunaan:
 * Panggil file ini di setiap file yang butuh akses database dengan:
 * require_once __DIR__ . '/config/database.php';
 */
?>