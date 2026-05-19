<?php
// layouts/navbar.php
?>
<main class="main-content">
    <header class="top-navbar">
        <div class="toggle-sidebar">
            <i class="fa-solid fa-bars" id="sidebarToggle"></i>
        </div>
        <div class="navbar-right">
            <div class="live-date">
                <i class="fa-regular fa-calendar-days"></i>
                <span><?= date('d M Y'); ?></span>
            </div>
            <div class="system-badge">
                <span class="badge env-badge">Enterprise Production</span>
            </div>
        </div>
    </header>
    <div class="content-body">