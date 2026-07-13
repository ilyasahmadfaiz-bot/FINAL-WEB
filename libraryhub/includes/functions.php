<?php
// Lokasi: includes/functions.php
// Kumpulan fungsi bantu (helper) yang dipakai di seluruh aplikasi

function sanitize($str)
{
    return htmlspecialchars(trim($str ?? ''), ENT_QUOTES, 'UTF-8');
}

function redirect($url)
{
    header('Location: ' . $url);
    exit();
}

function setFlash($type, $message)
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash()
{
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function formatRupiah($number)
{
    return 'Rp ' . number_format((float) $number, 0, ',', '.');
}

/**
 * Menangani upload file dengan validasi ekstensi & mime type.
 *
 * @param string $inputName   nama field input file
 * @param string $destDir     folder tujuan penyimpanan (absolute path)
 * @param array  $allowedExt  daftar ekstensi yang diizinkan
 * @param array  $allowedMime daftar mime type yang diizinkan
 * @param bool   $required    apakah file wajib diupload
 * @return array ['success' => bool, 'filename' => string|null, 'error' => string|null]
 */
function uploadFile($inputName, $destDir, $allowedExt, $allowedMime, $required = true)
{
    if (!isset($_FILES[$inputName]) || $_FILES[$inputName]['error'] === UPLOAD_ERR_NO_FILE) {
        if ($required) {
            return ['success' => false, 'filename' => null, 'error' => 'File wajib diupload.'];
        }
        return ['success' => true, 'filename' => null, 'error' => null];
    }

    $file = $_FILES[$inputName];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'filename' => null, 'error' => 'Terjadi kesalahan saat upload file.'];
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExt)) {
        return [
            'success' => false,
            'filename' => null,
            'error' => 'Ekstensi file tidak diizinkan. Hanya: ' . implode(', ', $allowedExt),
        ];
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime, $allowedMime)) {
        return ['success' => false, 'filename' => null, 'error' => 'Format file tidak valid.'];
    }

    if (!is_dir($destDir)) {
        mkdir($destDir, 0755, true);
    }

    $safeName = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $file['name']);
    $newName = uniqid('', true) . '_' . $safeName;
    $destPath = rtrim($destDir, '/') . '/' . $newName;

    if (!move_uploaded_file($file['tmp_name'], $destPath)) {
        return ['success' => false, 'filename' => null, 'error' => 'Gagal menyimpan file ke server.'];
    }

    return ['success' => true, 'filename' => $newName, 'error' => null];
}

function deleteFileIfExists($path)
{
    if ($path && file_exists($path)) {
        unlink($path);
    }
}
