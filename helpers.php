<?php
// helpers.php
$config = require __DIR__ . '/config.php';

/**
 * Ensure a PHP session is started.
 */
function ensure_session() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

/**
 * Connect to the database using PDO.
 */
function pdo_connect() {
    global $config;
    $dsn = "mysql:host={$config['db_host']};dbname={$config['db_name']};charset=utf8mb4";
    try {
        $pdo = new PDO($dsn, $config['db_user'], $config['db_pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        return $pdo;
    } catch (Exception $e) {
        die('Database connection failed: ' . $e->getMessage());
    }
}

/**
 * Escape output to prevent XSS.
 */
function e($v) {
    return htmlspecialchars($v ?? '', ENT_QUOTES);
}

/**
 * Get a flash message and remove it from session.
 */
function flash_get() {
    ensure_session();
    $f = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $f;
}

/**
 * Set a flash message in session.
 */
function flash_set($msg) {
    ensure_session();
    $_SESSION['flash'] = $msg;
}
