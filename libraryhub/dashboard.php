<?php
// Lokasi: dashboard.php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

requireLogin();

$db = Database::getInstance()->getConnection();

$totalBooks = $db->query('SELECT COUNT(*) AS total FROM books')->fetch()['total'];

$stmt = $db->prepare('SELECT COUNT(*) AS total FROM books WHERE uploaded_by = ?');
$stmt->execute([currentUserId()]);
$myBooks = $stmt->fetch()['total'];

$stmt = $db->prepare('SELECT COUNT(*) AS total FROM purchases WHERE user_id = ?');
$stmt->execute([currentUserId()]);
$myPurchases = $stmt->fetch()['total'];

$latestBooks = $db->query(
    'SELECT b.*, u.name AS owner_name FROM books b
     JOIN users u ON u.id = b.uploaded_by
     ORDER BY b.created_at DESC LIMIT 6'
)->fetchAll();

$pageTitle = 'Dashboard';
require_once __DIR__ . '/includes/header.php';
?>

<div class="page-heading">
    <h1>Dashboard</h1>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <h3><?= (int) $totalBooks ?></h3>
        <p>Total Buku di Perpustakaan</p>
    </div>
    <div class="stat-card">
        <h3><?= (int) $myBooks ?></h3>
        <p>Buku yang Saya Unggah</p>
    </div>
    <div class="stat-card">
        <h3><?= (int) $myPurchases ?></h3>
        <p>Buku yang Sudah Saya Beli</p>
    </div>
</div>

<div class="page-heading">
    <h2>Buku Terbaru</h2>
    <a href="<?= BASE_URL ?>books/list.php" class="btn btn-sm btn-secondary">Lihat Semua</a>
</div>

<div class="book-grid">
    <?php if (empty($latestBooks)): ?>
        <p>Belum ada buku yang diunggah.</p>
    <?php endif; ?>
    <?php foreach ($latestBooks as $book): ?>
        <div class="book-card">
            <img class="book-cover"
                 src="<?= $book['cover_image'] ? UPLOAD_COVER_URL . sanitize($book['cover_image']) : 'https://via.placeholder.com/220x220?text=No+Cover' ?>"
                 alt="<?= sanitize($book['title']) ?>">
            <div class="book-info">
                <h3><?= sanitize($book['title']) ?></h3>
                <p class="author">oleh <?= sanitize($book['author']) ?></p>
                <p class="price"><?= formatRupiah($book['price']) ?></p>
                <div class="book-actions">
                    <a href="<?= BASE_URL ?>books/view.php?id=<?= $book['id'] ?>" class="btn btn-sm">Detail</a>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
