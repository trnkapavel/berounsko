<?php
/**
 * Jednorázový skript: vytvoří admin/local.php s heslem "jahoda123".
 * Spusťte: php admin/setup_password.php
 * Pak tento soubor smažte (nebo ho nepřenášejte na produkční server).
 */
$password = 'jahoda123';
$hash = password_hash($password, PASSWORD_DEFAULT);
$content = "<?php\n/** Admin heslo – vygenerováno. Soubor je v .gitignore. */\ndefine('ADMIN_PASSWORD_HASH', " . var_export($hash, true) . ");\n";
$path = __DIR__ . '/local.php';
if (file_put_contents($path, $content) !== false) {
    echo "OK: admin/local.php byl vytvořen. Heslo je: jahoda123\n";
    echo "Doporučeno: smažte soubor admin/setup_password.php.\n";
} else {
    echo "CHYBA: Nepodařilo se zapsat admin/local.php (zkontrolujte oprávnění).\n";
}
