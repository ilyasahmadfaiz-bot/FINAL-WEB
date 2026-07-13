<?php
// Lokasi: books/view.php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

requireLogin();

$id = (int) ($_GET['id'] ?? 0);
$db = Database::getInstance()->getConnection();

$stmt = $db->prepare(
    'SELECT b.*, u.name AS owner_name FROM books b
     JOIN users u ON u.id = b.uploaded_by
     WHERE b.id = ?'
);
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

$pageTitle = $book['title'];
require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-heading">
    <h1>Detail Buku</h1>
    <a href="<?= BASE_URL ?>books/list.php" class="btn btn-secondary">Kembali</a>
</div>

<div class="detail-wrapper">
    <img class="detail-cover"
         src="<?= $book['cover_image'] ? UPLOAD_COVER_URL . sanitize($book['cover_image']) : 'https://via.placeholder.com/280x360?text=No+Cover' ?>"
         alt="<?= sanitize($book['title']) ?>">

    <div class="detail-info">
        <h1><?= sanitize($book['title']) ?></h1>
        <p class="author">oleh <?= sanitize($book['author']) ?> &middot; Diunggah oleh <?= sanitize($book['owner_name']) ?></p>
        <p class="price"><?= formatRupiah($book['price']) ?></p>
        <p class="description"><?= nl2br(sanitize($book['description'])) ?></p>

        <?php if ($isOwner): ?>
            <span class="badge badge-owned">Ini adalah buku Anda</span>
            <br><br>
            <a href="<?= BASE_URL ?>books/edit.php?id=<?= $book['id'] ?>" class="btn btn-secondary">Edit Buku</a>
        <?php elseif ($isPurchased): ?>
            <span class="badge badge-purchased">Sudah Dibeli</span>
        <?php else: ?>
            <form method="POST" action="<?= BASE_URL ?>books/buy.php">
                <input type="hidden" name="book_id" value="<?= $book['id'] ?>">
                <button type="submit" class="btn btn-success">Beli Buku Ini</button>
            </form>
        <?php endif; ?>

        <?php if ($hasAccess): ?>
            <br><br>
            <a href="<?= BASE_URL ?>books/read.php?id=<?= $book['id'] ?>" class="btn">📖 Baca PDF</a>
        <?php else: ?>
            <p class="form-hint" style="margin-top:12px;">🔒 Beli buku ini untuk membuka akses membaca PDF.</p>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
