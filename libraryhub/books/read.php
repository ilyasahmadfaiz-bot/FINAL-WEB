<?php
// Lokasi: books/read.php
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

$isOwner = $book['uploaded_by'] == currentUserId();

$stmt = $db->prepare('SELECT id FROM purchases WHERE user_id = ? AND book_id = ?');
$stmt->execute([currentUserId(), $id]);
$isPurchased = (bool) $stmt->fetch();

$hasAccess = $isOwner || isAdmin() || $isPurchased;

if (!$hasAccess) {
    setFlash('error', 'Anda harus membeli buku ini terlebih dahulu untuk membacanya.');
    redirect(BASE_URL . 'books/view.php?id=' . $id);
}

$pageTitle = 'Baca: ' . $book['title'];
require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-heading">
    <h1><?= sanitize($book['title']) ?></h1>
    <a href="<?= BASE_URL ?>books/view.php?id=<?= $book['id'] ?>" class="btn btn-secondary">Kembali</a>
</div>

<iframe class="pdf-viewer" src="<?= UPLOAD_FILE_URL . sanitize($book['file_path']) ?>"></iframe>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
