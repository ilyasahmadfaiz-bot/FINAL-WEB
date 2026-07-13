<?php
// Lokasi: profile.php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

requireLogin();

$db = Database::getInstance()->getConnection();
$stmt = $db->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([currentUserId()]);
$user = $stmt->fetch();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if ($name === '' || $email === '') {
        $errors[] = 'Nama dan email wajib diisi.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Format email tidak valid.';
    }

    // cek email dipakai user lain
    if (empty($errors)) {
        $stmt = $db->prepare('SELECT id FROM users WHERE email = ? AND id != ?');
        $stmt->execute([$email, currentUserId()]);
        if ($stmt->fetch()) {
            $errors[] = 'Email sudah digunakan oleh akun lain.';
        }
    }

    $updatePassword = false;
    if ($newPassword !== '' || $confirmPassword !== '' || $currentPassword !== '') {
        if ($currentPassword === '' || $newPassword === '' || $confirmPassword === '') {
            $errors[] = 'Untuk mengganti password, semua field password wajib diisi.';
        } elseif (!password_verify($currentPassword, $user['password'])) {
            $errors[] = 'Password saat ini salah.';
        } elseif (strlen($newPassword) < 6) {
            $errors[] = 'Password baru minimal 6 karakter.';
        } elseif ($newPassword !== $confirmPassword) {
            $errors[] = 'Konfirmasi password baru tidak sama.';
        } else {
            $updatePassword = true;
        }
    }

    if (empty($errors)) {
        if ($updatePassword) {
            $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $db->prepare('UPDATE users SET name = ?, email = ?, password = ? WHERE id = ?');
            $stmt->execute([$name, $email, $hashed, currentUserId()]);
        } else {
            $stmt = $db->prepare('UPDATE users SET name = ?, email = ? WHERE id = ?');
            $stmt->execute([$name, $email, currentUserId()]);
        }

        $_SESSION['user_name'] = $name;
        setFlash('success', 'Profil berhasil diperbarui.');
        redirect(BASE_URL . 'profile.php');
    }
}

$pageTitle = 'Profil Saya';
require_once __DIR__ . '/includes/header.php';
?>

<div class="page-heading">
    <h1>Profil Saya</h1>
</div>

<?php if (!empty($errors)): ?>
    <div class="alert alert-error">
        <?php foreach ($errors as $err): ?>
            <div><?= sanitize($err) ?></div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<div class="auth-card" style="max-width: 500px; margin: 0;">
    <form method="POST" action="">
        <div class="form-group">
            <label for="name">Nama Lengkap</label>
            <input type="text" id="name" name="name" value="<?= sanitize($user['name']) ?>" required>
        </div>
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?= sanitize($user['email']) ?>" required>
        </div>
        <div class="form-group">
            <label>Role</label>
            <input type="text" value="<?= sanitize($user['role']) ?>" disabled>
        </div>

        <hr style="margin: 20px 0; border: none; border-top: 1px solid #e2e8f0;">
        <p class="form-hint" style="margin-bottom: 12px;">Kosongkan bagian di bawah jika tidak ingin mengubah password.</p>

        <div class="form-group">
            <label for="current_password">Password Saat Ini</label>
            <input type="password" id="current_password" name="current_password">
        </div>
        <div class="form-group">
            <label for="new_password">Password Baru</label>
            <input type="password" id="new_password" name="new_password">
        </div>
        <div class="form-group">
            <label for="confirm_password">Konfirmasi Password Baru</label>
            <input type="password" id="confirm_password" name="confirm_password">
        </div>

        <button type="submit" class="btn btn-block">Simpan Perubahan</button>
    </form>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
