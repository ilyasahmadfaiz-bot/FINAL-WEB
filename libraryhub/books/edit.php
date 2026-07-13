<?php
// Lokasi: books/edit.php
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
    setFlash('error', 'Anda tidak memiliki izin untuk mengedit buku ini.');
    redirect(BASE_URL . 'books/list.php');
}

$errors = [];
$old = [
    'title' => $book['title'],
    'author' => $book['author'],
    'description' => $book['description'],
    'price' => $book['price'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize($_POST['title'] ?? '');
    $author = sanitize($_POST['author'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $price = trim($_POST['price'] ?? '');

    $old = compact('title', 'author', 'description', 'price');

    if ($title === '' || $author === '' || $description === '' || $price === '') {
        $errors[] = 'Semua field wajib diisi.';
    }

    if ($price !== '' && (!is_numeric($price) || (float) $price <= 0)) {
        $errors[] = 'Harga buku harus berupa angka positif.';
    }

    $newCover = $book['cover_image'];
    $newFile = $book['file_path'];
    $coverResult = null;
    $fileResult = null;

    if (empty($errors)) {
        // Cover opsional: hanya diproses jika user memilih file baru
        if (!empty($_FILES['cover_image']['name'])) {
            $coverResult = uploadFile('cover_image', UPLOAD_COVER_DIR, ALLOWED_IMAGE_EXT, ALLOWED_IMAGE_MIME, false);
            if (!$coverResult['success']) {
                $errors[] = $coverResult['error'];
            } else {
                $newCover = $coverResult['filename'];
            }
        }

        // File PDF opsional saat edit: hanya diganti jika upload baru
        if (!empty($_FILES['book_file']['name'])) {
            $fileResult = uploadFile('book_file', UPLOAD_FILE_DIR, ALLOWED_PDF_EXT, ALLOWED_PDF_MIME, false);
            if (!$fileResult['success']) {
                $errors[] = $fileResult['error'];
            } else {
                $newFile = $fileResult['filename'];
            }
        }
    }

    if (empty($errors)) {
        $stmt = $db->prepare(
            'UPDATE books SET title=?, author=?, description=?, price=?, cover_image=?, file_path=? WHERE id=?'
        );
        $stmt->execute([$title, $author, $description, (float) $price, $newCover, $newFile, $id]);

        // Hapus file lama jika berhasil diganti
        if ($coverResult && $coverResult['success'] && $book['cover_image']) {
            deleteFileIfExists(UPLOAD_COVER_DIR . $book['cover_image']);
        }
        if ($fileResult && $fileResult['success'] && $book['file_path']) {
            deleteFileIfExists(UPLOAD_FILE_DIR . $book['file_path']);
        }

        setFlash('success', 'Buku berhasil diperbarui.');
        redirect(BASE_URL . 'books/list.php');
    }
}

$pageTitle = 'Edit Buku';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-heading">
    <h1>Edit Buku</h1>
    <a href="<?= BASE_URL ?>books/list.php" class="btn btn-secondary">Kembali</a>
</div>

<?php if (!empty($errors)): ?>
    <div class="alert alert-error" id="jsErrorBox">
        <?php foreach ($errors as $err): ?>
            <div><?= sanitize($err) ?></div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<div class="auth-card" style="max-width: 600px; margin: 0;">
    <form method="POST" action="" enctype="multipart/form-data" id="bookForm">
        <div class="form-group">
            <label for="title">Judul Buku</label>
            <input type="text" id="title" name="title" value="<?= sanitize($old['title']) ?>" required>
        </div>
        <div class="form-group">
            <label for="author">Penulis</label>
            <input type="text" id="author" name="author" value="<?= sanitize($old['author']) ?>" required>
        </div>
        <div class="form-group">
            <label for="description">Deskripsi</label>
            <textarea id="description" name="description" rows="4" required><?= sanitize($old['description']) ?></textarea>
        </div>
        <div class="form-group">
            <label for="price">Harga (Rp)</label>
            <input type="number" id="price" name="price" min="1" step="0.01" value="<?= sanitize($old['price']) ?>" required>
        </div>
        <div class="form-group">
            <label for="cover_image">Ganti Cover (JPG/JPEG/PNG, opsional)</label>
            <?php if ($book['cover_image']): ?>
                <div><img src="<?= UPLOAD_COVER_URL . sanitize($book['cover_image']) ?>" style="max-width:120px; border-radius:6px; margin-bottom:8px;"></div>
            <?php endif; ?>
            <input type="file" id="cover_image" name="cover_image" accept=".jpg,.jpeg,.png">
            <img id="coverPreview" src="" alt="Preview" style="display:none; max-width:150px; margin-top:8px; border-radius:6px;">
        </div>
        <div class="form-group">
            <label for="book_file">Ganti File PDF (opsional)</label>
            <p class="form-hint">File saat ini: <?= sanitize($book['file_path']) ?></p>
            <input type="file" id="book_file" name="book_file" accept=".pdf">
        </div>
        <button type="submit" class="btn btn-block">Simpan Perubahan</button>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
