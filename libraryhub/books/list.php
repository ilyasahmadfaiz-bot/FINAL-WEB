<?php
// Lokasi: books/list.php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

requireLogin();

$db = Database::getInstance()->getConnection();
$keyword = sanitize($_GET['q'] ?? '');

if ($keyword !== '') {
    $stmt = $db->prepare(
        'SELECT b.*, u.name AS owner_name FROM books b
         JOIN users u ON u.id = b.uploaded_by
         WHERE b.title LIKE ? OR b.author LIKE ?
         ORDER BY b.created_at DESC'
    );
    $like = '%' . $keyword . '%';
    $stmt->execute([$like, $like]);
} else {
    $stmt = $db->query(
        'SELECT b.*, u.name AS owner_name FROM books b
         JOIN users u ON u.id = b.uploaded_by
         ORDER BY b.created_at DESC'
    );
}
$books = $stmt->fetchAll();

// Ambil daftar buku yang sudah dibeli user
$stmt2 = $db->prepare('SELECT book_id FROM purchases WHERE user_id = ?');
$stmt2->execute([currentUserId()]);
$purchasedIds = array_column($stmt2->fetchAll(), 'book_id');

$pageTitle = 'Daftar Buku';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-heading">
    <h1>Daftar Buku</h1>
    <a href="<?= BASE_URL ?>books/add.php" class="btn">+ Tambah Buku</a>
</div>

<form method="GET" action="" class="search-bar">
    <input type="text" name="q" placeholder="Cari judul atau penulis buku..." value="<?= sanitize($keyword) ?>">
    <button type="submit" class="btn">Cari</button>
    <?php if ($keyword !== ''): ?>
        <a href="<?= BASE_URL ?>books/list.php" class="btn btn-secondary">Reset</a>
    <?php endif; ?>
</form>

<div class="book-grid">
    <?php if (empty($books)): ?>
        <p>Tidak ada buku ditemukan.</p>
    <?php endif; ?>

    <?php foreach ($books as $book): ?>
        <?php
        $isOwner = $book['uploaded_by'] == currentUserId();
        $isPurchased = in_array($book['id'], $purchasedIds);
        ?>
        <div class="book-card">
            <img class="book-cover"
                 src="<?= $book['cover_image'] ? UPLOAD_COVER_URL . sanitize($book['cover_image']) : 'https://via.placeholder.com/220x220?text=No+Cover' ?>"
                 alt="<?= sanitize($book['title']) ?>">
            <div class="book-info">
                <h3><?= sanitize($book['title']) ?></h3>
                <p class="author">oleh <?= sanitize($book['author']) ?></p>
                <p class="price"><?= formatRupiah($book['price']) ?></p>

                <?php if ($isOwner): ?>
                    <span class="badge badge-owned">Milik Anda</span>
                <?php elseif ($isPurchased): ?>
                    <span class="badge badge-purchased">Sudah Dibeli</span>
                <?php endif; ?>

                <div class="book-actions">
                    <a href="<?= BASE_URL ?>books/view.php?id=<?= $book['id'] ?>" class="btn btn-sm">Detail</a>
                    <?php if ($isOwner || isAdmin()): ?>
                        <a href="<?= BASE_URL ?>books/edit.php?id=<?= $book['id'] ?>" class="btn btn-sm btn-secondary">Edit</a>
                        <a href="<?= BASE_URL ?>books/delete.php?id=<?= $book['id'] ?>" class="btn btn-sm btn-danger confirm-delete">Hapus</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
