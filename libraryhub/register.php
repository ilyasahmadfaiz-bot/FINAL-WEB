<?php
// Lokasi: register.php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

if (isLoggedIn()) {
    redirect(BASE_URL . 'dashboard.php');
}

$errors = [];
$old = ['name' => '', 'email' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    $old['name'] = $name;
    $old['email'] = $email;

    if ($name === '' || $email === '' || $password === '' || $confirmPassword === '') {
        $errors[] = 'Semua field wajib diisi.';
    }
    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Format email tidak valid.';
    }
    if ($password !== '' && strlen($password) < 6) {
        $errors[] = 'Password minimal 6 karakter.';
    }
    if ($password !== $confirmPassword) {
        $errors[] = 'Konfirmasi password tidak sama dengan password.';
    }

    if (empty($errors)) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);

        if ($stmt->fetch()) {
            $errors[] = 'Email sudah terdaftar. Gunakan email lain.';
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->prepare('INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, "user")');
            $stmt->execute([$name, $email, $hashed]);

            setFlash('success', 'Registrasi berhasil! Silakan login.');
            redirect(BASE_URL . 'login.php');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi - LibraryHub</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
</head>
<body>
<div class="auth-wrapper">
    <div class="auth-card">
        <h1>📚 LibraryHub</h1>
        <p class="subtitle">Buat akun baru untuk mulai membaca &amp; berbagi buku</p>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <?php foreach ($errors as $err): ?>
                    <div><?= sanitize($err) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="name">Nama Lengkap</label>
                <input type="text" id="name" name="name" value="<?= sanitize($old['name']) ?>" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?= sanitize($old['email']) ?>" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required minlength="6">
                <span class="form-hint">Minimal 6 karakter</span>
            </div>
            <div class="form-group">
                <label for="confirm_password">Konfirmasi Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required minlength="6">
            </div>
            <button type="submit" class="btn btn-block">Daftar</button>
        </form>

        <p class="auth-footer-link">Sudah punya akun? <a href="<?= BASE_URL ?>login.php">Login di sini</a></p>
    </div>
</div>
<script src="<?= BASE_URL ?>assets/js/script.js"></script>
</body>
</html>
