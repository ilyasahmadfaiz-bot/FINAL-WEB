<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

if (isLoggedIn()) {
    redirect(BASE_URL . 'dashboard.php');
} else {
    redirect(BASE_URL . 'login.php');
}