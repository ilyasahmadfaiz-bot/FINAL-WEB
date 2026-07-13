<?php
// Lokasi: includes/header.php
// Header + navbar, dipakai di semua halaman setelah login
$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? sanitize($pageTitle) . ' - LibraryHub' : 'LibraryHub' ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
</head>
<body>
<header class="navbar">
    <div class="navbar-inner">
        <a href="<?= BASE_URL ?>dashboard.php" class="brand">📚 LibraryHub</a>
        <button class="nav-toggle" id="navToggle">☰</button>
        <nav class="nav-links" id="navLinks">
            <a href="<?= BASE_URL ?>dashboard.php">Dashboard</a>
            <a href="<?= BASE_URL ?>books/list.php">Daftar Buku</a>
            <a href="<?= BASE_URL ?>books/add.php">Tambah Buku</a>
            <a href="<?= BASE_URL ?>profile.php">Profil</a>
            <span class="nav-user">Hai, <?= sanitize(currentUserName()) ?></span>
            <a href="<?= BASE_URL ?>logout.php" class="btn-logout">Logout</a>
        </nav>
    </div>
</header>
<main class="container">
    <?php if ($flash): ?>
        <div class="alert alert-<?= sanitize($flash['type']) ?>"><?= sanitize($flash['message']) ?></div>
    <?php endif; ?>
