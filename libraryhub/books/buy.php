<?php
// Lokasi: books/buy.php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL . 'books/list.php');
}

$bookId = (int) ($_POST['book_id'] ?? 0);
$db = Database::getInstance()->getConnection();

$stmt = $db->prepare('SELECT * FROM books WHERE id = ?');
$stmt->execute([$bookId]);
$book = $stmt->fetch();

if (!$book) {
    setFlash('error', 'Buku tidak ditemukan.');
    redirect(BASE_URL . 'books/list.php');
}

if ($book['uploaded_by'] == currentUserId()) {
    setFlash('error', 'Anda tidak dapat membeli buku milik Anda sendiri.');
    redirect(BASE_URL . 'books/view.php?id=' . $bookId);
}

$stmt = $db->prepare('SELECT id FROM purchases WHERE user_id = ? AND book_id = ?');
$stmt->execute([currentUserId(), $bookId]);

if ($stmt->fetch()) {
    setFlash('error', 'Anda sudah membeli buku ini sebelumnya.');
} else {
    $stmt = $db->prepare('INSERT INTO purchases (user_id, book_id) VALUES (?, ?)');
    $stmt->execute([currentUserId(), $bookId]);
    setFlash('success', 'Pembelian berhasil! Anda sekarang dapat membaca buku ini.');
}

redirect(BASE_URL . 'books/view.php?id=' . $bookId);
