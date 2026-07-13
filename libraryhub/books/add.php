<?php
// Lokasi: books/add.php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

requireLogin();

$errors = [];
$old = ['title' => '', 'author' => '', 'description' => '', 'price' => ''];

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

    $coverResult = null;
    $fileResult = null;

    if (empty($errors)) {
        $coverResult = uploadFile('cover_image', UPLOAD_COVER_DIR, ALLOWED_IMAGE_EXT, ALLOWED_IMAGE_MIME, false);
        if (!$coverResult['success']) {
            $errors[] = $coverResult['error'];
        }

        $fileResult = uploadFile('book_file', UPLOAD_FILE_DIR, ALLOWED_PDF_EXT, ALLOWED_PDF_MIME, true);
        if (!$fileResult['success']) {
            $errors[] = $fileResult['error'];
        }
    }

    if (empty($errors)) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare(
            'INSERT INTO books (title, author, description, price, cover_image, file_path, uploaded_by)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $title,
            $author,
            $description,
            (float) $price,
            $coverResult['filename'],
            $fileResult['filename'],
            currentUserId(),
        ]);

        setFlash('success', 'Buku berhasil ditambahkan.');
        redirect(BASE_URL . 'books/list.php');
    } else {
        // hapus file yang sudah keburu tersimpan jika terjadi error setelah upload
        if ($coverResult && $coverResult['filename']) {
            deleteFileIfExists(UPLOAD_COVER_DIR . $coverResult['filename']);
        }
        if ($fileResult && $fileResult['filename']) {
            deleteFileIfExists(UPLOAD_FILE_DIR . $fileResult['filename']);
        }
    }
}

$pageTitle = 'Tambah Buku';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-heading">
    <h1>Tambah Buku</h1>
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
            <label for="cover_image">Cover Buku (JPG/JPEG/PNG, opsional)</label>
            <input type="file" id="cover_image" name="cover_image" accept=".jpg,.jpeg,.png">
            <img id="coverPreview" src="" alt="Preview" style="display:none; max-width:150px; margin-top:8px; border-radius:6px;">
        </div>
        <div class="form-group">
            <label for="book_file">File Buku (PDF, wajib)</label>
            <input type="file" id="book_file" name="book_file" accept=".pdf" required>
        </div>
        <button type="submit" class="btn btn-block">Simpan Buku</button>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
