<?php
// Lokasi: includes/auth.php
// Fungsi terkait autentikasi & otorisasi

function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}

function requireLogin()
{
    if (!isLoggedIn()) {
        setFlash('error', 'Silakan login terlebih dahulu.');
        redirect(BASE_URL . 'login.php');
    }
}

function isAdmin()
{
    return isLoggedIn() && $_SESSION['role'] === 'admin';
}

function currentUserId()
{
    return $_SESSION['user_id'] ?? null;
}

function currentUserName()
{
    return $_SESSION['user_name'] ?? '';
}
