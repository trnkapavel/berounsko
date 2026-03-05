<?php
session_start();
require_once __DIR__ . '/config.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pass = $_POST['password'] ?? '';
    if (ADMIN_PASSWORD_HASH === '') {
        $error = 'Nejprve vytvořte soubor admin/local.php z admin/local.php.example a nastavte heslo.';
    } elseif (is_valid_admin_password($pass)) {
        $_SESSION['admin_ok'] = true;
        header('Location: index.php');
        exit;
    } else {
        $error = 'Nesprávné heslo.';
    }
}

if (admin_logged_in()) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Přihlášení – Admin Berounsko</title>
    <style>
        body { font-family: sans-serif; max-width: 360px; margin: 80px auto; padding: 20px; }
        h1 { font-size: 1.3em; color: #192e7c; }
        input[type="password"] { width: 100%; padding: 10px; margin: 8px 0 20px; box-sizing: border-box; }
        button { background: #92be44; color: #fff; border: none; padding: 12px 24px; font-size: 1em; cursor: pointer; }
        .error { color: #c00; margin-bottom: 16px; font-size: 0.95em; }
    </style>
</head>
<body>
    <h1>Přihlášení do administrace</h1>
    <?php if ($error): ?><p class="error"><?= htmlspecialchars($error) ?></p><?php endif; ?>
    <form method="post">
        <label>Heslo:</label>
        <input type="password" name="password" autocomplete="current-password" required autofocus>
        <button type="submit">Přihlásit</button>
    </form>
</body>
</html>
