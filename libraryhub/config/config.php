<?php
// Lokasi: config/config.php
// Konfigurasi umum aplikasi (bukan koneksi database)

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Sesuaikan jika nama folder project Anda berbeda dari "libraryhub"
define('BASE_URL', '/libraryhub/');

define('UPLOAD_COVER_DIR', __DIR__ . '/../uploads/covers/');
define('UPLOAD_FILE_DIR', __DIR__ . '/../uploads/files/');
define('UPLOAD_COVER_URL', BASE_URL . 'uploads/covers/');
define('UPLOAD_FILE_URL', BASE_URL . 'uploads/files/');

define('ALLOWED_IMAGE_EXT', ['jpg', 'jpeg', 'png']);
define('ALLOWED_IMAGE_MIME', ['image/jpeg', 'image/png']);
define('ALLOWED_PDF_EXT', ['pdf']);
define('ALLOWED_PDF_MIME', ['application/pdf']);

date_default_timezone_set('Asia/Makassar');
error_reporting(E_ALL);
ini_set('display_errors', 1);
