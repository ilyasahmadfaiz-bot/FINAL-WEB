<?php
// Lokasi: books/delete.php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

requireLogin();

$id = (int) ($_GET['id'] ?? 0);
$db = Database::getInstance()->getConnection();

$stmt = $db->prepare('SELECT * FROM books WHERE id = ?');
$stmt->execute([$id]);
$book = $stmt->fetch();

if (!$book) {
    setFlash('error', 'Buku tidak ditemukan.');
    redirect(BASE_URL . 'books/list.php');
}

if ($book['uploaded_by'] != currentUserId() && !isAdmin()) {
    setFlash('error', 'Anda tidak memiliki izin untuk menghapus buku ini.');
    redirect(BASE_URL . 'books/list.php');
}

$stmt = $db->prepare('DELETE FROM books WHERE id = ?');
$stmt->execute([$id]);

deleteFileIfExists(UPLOAD_COVER_DIR . $book['cover_image']);
deleteFileIfExists(UPLOAD_FILE_DIR . $book['file_path']);

setFlash('success', 'Buku berhasil dihapus.');
redirect(BASE_URL . 'books/list.php');
