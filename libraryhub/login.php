<?php
// Lokasi: login.php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

if (isLoggedIn()) {
    redirect(BASE_URL . 'dashboard.php');
}

$flash = getFlash();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $errors[] = 'Semua field wajib diisi.';
    } else {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['role'] = $user['role'];
            redirect(BASE_URL . 'dashboard.php');
        } else {
            $errors[] = 'Email atau password salah.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - LibraryHub</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
</head>
<body>
<div class="auth-wrapper">
    <div class="auth-card">
        <h1>📚 LibraryHub</h1>
        <p class="subtitle">Perpustakaan mini online — baca dimana saja</p>

        <?php if ($flash): ?>
            <div class="alert alert-<?= sanitize($flash['type']) ?>"><?= sanitize($flash['message']) ?></div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <?php foreach ($errors as $err): ?>
                    <div><?= sanitize($err) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-block">Login</button>
        </form>

        <p class="auth-footer-link">Belum punya akun? <a href="<?= BASE_URL ?>register.php">Daftar di sini</a></p>
    </div>
</div>
<script src="<?= BASE_URL ?>assets/js/script.js"></script>
</body>
</html>
