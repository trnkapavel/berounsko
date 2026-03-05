<?php
/**
 * Admin konfigurace.
 * Heslo nastavte v admin/local.php (zkopírujte z admin/local.php.example).
 * V local.php lze přepsat i $contentFile, pokud je data/content.json jinde.
 */
$adminDir = __DIR__;
$projectRoot = dirname($adminDir);
$contentFile = (realpath($projectRoot) ?: $projectRoot) . '/data/content.json';

if (file_exists($adminDir . '/local.php')) {
    require_once $adminDir . '/local.php';
}
if (!defined('ADMIN_PASSWORD_HASH')) {
    // Výchozí hash pro heslo "admin" – v produkci vytvořte admin/local.php a nastavte vlastní heslo
    define('ADMIN_PASSWORD_HASH', 'jahoda123');
}

function admin_logged_in(): bool {
    return !empty($_SESSION['admin_ok']);
}

/** Ověří heslo – podporuje bcrypt hash (z local.php) i výchozí plain text. */
function is_valid_admin_password(string $password): bool {
    $hash = ADMIN_PASSWORD_HASH;
    if (strpos($hash, '$2y$') === 0 || strpos($hash, '$2a$') === 0) {
        return password_verify($password, $hash);
    }
    return $password === $hash;
}

function content_path(): string {
    global $contentFile;
    return $contentFile;
}

function load_content(): array {
    $GLOBALS['_content_load_error'] = null;
    $GLOBALS['_content_raw_length'] = null;
    $path = content_path();
    if (!file_exists($path)) {
        return [];
    }
    if (!is_readable($path)) {
        return [];
    }
    $raw = file_get_contents($path);
    if ($raw === false) {
        return [];
    }
    // Odstranit UTF-8 BOM (některé editory ho přidávají, json_decode pak selže)
    if (substr($raw, 0, 3) === "\xEF\xBB\xBF") {
        $raw = substr($raw, 3);
    }
    $data = json_decode($raw, true);
    if (!is_array($data)) {
        $GLOBALS['_content_load_error'] = json_last_error_msg();
        $GLOBALS['_content_raw_length'] = strlen($raw);
        return [];
    }
    return $data;
}

/** Po neúspěšném load_content() vrátí důvod (nebo null). */
function get_content_load_error(): ?string {
    return $GLOBALS['_content_load_error'] ?? null;
}

/** Po neúspěšném load_content() vrátí délku načteného řetězce. */
function get_content_raw_length(): ?int {
    return $GLOBALS['_content_raw_length'] ?? null;
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
