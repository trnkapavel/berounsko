<?php
/**
 * Načte obsah z data/content.json. Používá index.php i rezervace.php.
 */
function load_public_content(): array {
    $path = dirname(__DIR__) . '/data/content.json';
    if (!is_readable($path)) {
        return [];
    }
    $raw = file_get_contents($path);
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}
