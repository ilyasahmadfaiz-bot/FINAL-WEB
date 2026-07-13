<?php
// Lokasi: logout.php
require_once __DIR__ . '/config/config.php';

$_SESSION = [];
session_unset();
session_destroy();

header('Location: login.php');
exit();
