<?php
/**
 * Admin konfigurace.
 * Heslo nastavte v admin/local.php (zkopírujte z admin/local.php.example).
 */
$contentFile = dirname(__DIR__) . '/data/content.json';

if (file_exists(__DIR__ . '/local.php')) {
    require_once __DIR__ . '/local.php';
}
if (!defined('ADMIN_PASSWORD_HASH')) {
    // Výchozí hash pro heslo "admin" – v produkci vytvořte admin/local.php a nastavte vlastní heslo
    define('ADMIN_PASSWORD_HASH', 'jahoda123');
}

function admin_logged_in(): bool {
    return !empty($_SESSION['admin_ok']);
}

function content_path(): string {
    global $contentFile;
    return $contentFile;
}

function load_content(): array {
    $path = content_path();
    if (!is_readable($path)) {
        return [];
    }
    $raw = file_get_contents($path);
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

function save_content(array $data): bool {
    $path = content_path();
    $dir = dirname($path);
    if (!is_dir($dir) || !is_writable($dir)) {
        return false;
    }
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    return file_put_contents($path, $json, LOCK_EX) !== false;
}
