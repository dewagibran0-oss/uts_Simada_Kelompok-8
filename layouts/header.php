<?php
// layouts/header.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Security Checkpoint: Jika belum login, tendang kembali ke halaman login
if (!isset($_SESSION['is_logged']) || $_SESSION['is_logged'] !== true) {
    header("Location: ../auth/login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIMADA e-Purchasing - Enterprise Dashboard</title>
    <!-- Google Fonts Inter & FontAwesome Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Link ke style utama dashboard -->
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="app-container">