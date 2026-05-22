<?php
// Allow a development bypass so admin pages can be explored without login.
// Controlled by the `ALLOW_ADMIN_GUEST` constant in config/api_config.php.
@include_once __DIR__ . '/api_config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (defined('ALLOW_ADMIN_GUEST') && ALLOW_ADMIN_GUEST === true) {
    // Guest access enabled — do not enforce login redirect.
    return;
}

if (empty($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: /PJBL-main/halamanWeb/loginpage/signin.php');
    exit;
}