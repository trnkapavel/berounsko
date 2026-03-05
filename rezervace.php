<?php
// rezervace.php – AKTUALIZOVANÁ VERZE s bezpečnostními opravami
header('Content-Type: application/json; charset=utf-8');

// ============================================================
// SMTP KONFIGURACE
// Zkopírujte smtp.local.php.example → smtp.local.php a vyplňte
// přihlašovací údaje. Soubor smtp.local.php je v .gitignore.
// ============================================================
if (file_exists(__DIR__ . '/smtp.local.php')) {
    require_once __DIR__ . '/smtp.local.php';
} else {
    define('SMTP_HOST',       'smtp.example.com');
    define('SMTP_USER',       '');
    define('SMTP_PASS',       '');
    define('SMTP_PORT',       587);
    define('SMTP_SECURE',     'tls');
    define('SMTP_FROM_EMAIL', '');
    define('SMTP_FROM_NAME',  '');
}

// ============================================================
// RATE LIMITING (max požadavků z jedné IP za časové okno)
// ============================================================
const RATE_LIMIT_MAX    = 20;    // max. odeslání (pro testování; pro produkci lze snížit na 5)
const RATE_LIMIT_WINDOW = 3600;  // za 1 hodinu (sekundy)

// ============================================================
// POMOCNÉ FUNKCE
// ============================================================

/** Escapuje řetězec pro bezpečné vložení do HTML */
function h(string $str): string
{
    return htmlspecialchars($str, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

/**
 * Kontrola rate limitu.
 * Vrací false, pokud IP překročila povolený počet požadavků.
 */
function rateLimitCheck(string $ip): bool
{
    $dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'rezervace_rl';
    if (!is_dir($dir)) {
        @mkdir($dir, 0700, true);
    }
    $file = $dir . DIRECTORY_SEPARATOR . md5($ip) . '.json';
    $now  = time();
    $data = ['ts' => []];

    if (file_exists($file)) {
        $raw = @file_get_contents($file);
        if ($raw !== false) {
            $data = json_decode($raw, true) ?: $data;
        }
    }

    $data['ts'] = array_values(array_filter(
        $data['ts'],
        fn($t) => ($now - $t) < RATE_LIMIT_WINDOW
    ));

    if (count($data['ts']) >= RATE_LIMIT_MAX) {
        return false;
    }

    $data['ts'][] = $now;
    @file_put_contents($file, json_encode($data), LOCK_EX);
    return true;
}

// ============================================================
// LIGHTWEIGHT SMTP TŘÍDA (bez externích závislostí)
// Podporuje: TLS/STARTTLS, AUTH LOGIN, HTML e-mail + .ics příloha
// ============================================================
class SmtpMailer
{
    private string $host;
    private int    $port;
    private string $user;
    private string $pass;
    private string $secure;
    /** @var resource|false|null */
    private $socket = null;

    public function __construct(string $host, int $port, string $user, string $pass, string $secure = 'tls')
    {
        $this->host   = $host;
        $this->port   = $port;
        $this->user   = $user;
        $this->pass   = $pass;
        $this->secure = strtolower($secure);
    }

    /**
     * Odešle e-mail. Vrací true při úspěchu, false při chybě.
     */
    public function send(
        string  $fromEmail,
        string  $fromName,
        string  $toEmail,
        string  $subject,
        string  $htmlBody,
        ?string $icsContent = null,
        ?string $replyTo    = null
    ): bool {
        try {
            $this->open();
            $this->auth();
            $this->envelope($fromEmail, $toEmail);
            $this->writeData($fromEmail, $fromName, $toEmail, $subject, $htmlBody, $icsContent, $replyTo);
            $this->write("QUIT");
            $this->close();
            return true;
        } catch (\RuntimeException $e) {
            $this->close();
            $msg = date('c') . ' ' . $e->getMessage() . "\n";
            @file_put_contents(__DIR__ . '/smtp_error.log', $msg, FILE_APPEND | LOCK_EX);
            $tmpLog = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'rezervace_smtp_error.log';
            @file_put_contents($tmpLog, $msg, FILE_APPEND | LOCK_EX);
            return false;
        }
    }

    private function open(): void
    {
        $address = ($this->secure === 'ssl') ? "ssl://{$this->host}" : $this->host;
        $this->socket = @fsockopen($address, $this->port, $errno, $errstr, 10);
        if (!$this->socket) {
            throw new \RuntimeException("Nelze se připojit k SMTP: $errstr ($errno)");
        }
        stream_set_timeout($this->socket, 15);

        $this->expect('220');

        $ehlo = $_SERVER['SERVER_NAME'] ?? 'localhost';
        $this->write("EHLO $ehlo");
        $this->expect('250');

        if ($this->secure === 'tls') {
            $this->write("STARTTLS");
            $this->expect('220');
            if (!stream_socket_enable_crypto($this->socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                throw new \RuntimeException("STARTTLS se nezdařilo");
            }
            $this->write("EHLO $ehlo");
            $this->expect('250');
        }
    }

    private function auth(): void
    {
        $this->write("AUTH LOGIN");
        $this->expect('334');
        $this->write(base64_encode($this->user));
        $this->expect('334');
        $this->write(base64_encode($this->pass));
        $this->expect('235');
    }

    private function envelope(string $from, string $to): void
    {
        $this->write("MAIL FROM:<$from>");
        $this->expect('250');
        $this->write("RCPT TO:<$to>");
        $this->expect('250');
        $this->write("DATA");
        $this->expect('354');
    }

    private function writeData(
        string  $fromEmail,
        string  $fromName,
        string  $toEmail,
        string  $subject,
        string  $htmlBody,
        ?string $icsContent,
        ?string $replyTo
    ): void {
        $boundary       = bin2hex(random_bytes(8));
        $encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
        $encodedFrom    = '=?UTF-8?B?' . base64_encode($fromName) . '?=';

        $msg  = "From: {$encodedFrom} <{$fromEmail}>\r\n";
        $msg .= "To: {$toEmail}\r\n";
        $msg .= "Subject: {$encodedSubject}\r\n";
        $msg .= "MIME-Version: 1.0\r\n";
        if ($replyTo !== null) {
            $msg .= "Reply-To: {$replyTo}\r\n";
        }

        if ($icsContent !== null) {
            $msg .= "Content-Type: multipart/mixed; boundary=\"{$boundary}\"\r\n\r\n";
            $msg .= "--{$boundary}\r\n";
            $msg .= "Content-Type: text/html; charset=UTF-8\r\n";
            $msg .= "Content-Transfer-Encoding: base64\r\n\r\n";
            $msg .= chunk_split(base64_encode($htmlBody)) . "\r\n";
            $msg .= "--{$boundary}\r\n";
            $msg .= "Content-Type: text/calendar; name=\"pozvanka.ics\"\r\n";
            $msg .= "Content-Transfer-Encoding: base64\r\n";
            $msg .= "Content-Disposition: attachment; filename=\"pozvanka.ics\"\r\n\r\n";
            $msg .= chunk_split(base64_encode($icsContent)) . "\r\n";
            $msg .= "--{$boundary}--";
        } else {
            $msg .= "Content-Type: text/html; charset=UTF-8\r\n";
            $msg .= "Content-Transfer-Encoding: base64\r\n\r\n";
            $msg .= chunk_split(base64_encode($htmlBody));
        }

        // Dot-stuffing (RFC 5321): řádky začínající tečkou zdvojíme
        $lines = explode("\r\n", $msg);
        $lines = array_map(fn($l) => (isset($l[0]) && $l[0] === '.') ? '.' . $l : $l, $lines);
        $msg   = implode("\r\n", $lines);

        fwrite($this->socket, $msg . "\r\n.\r\n");
        $this->expect('250');
    }

    /** Zapíše příkaz na socket */
    private function write(string $command): void
    {
        fwrite($this->socket, $command . "\r\n");
    }

    /**
     * Přečte odpověď serveru a ověří očekávaný kód.
     * Správně zpracuje víceřádkové SMTP odpovědi (250-xxx / 250 xxx).
     */
    private function expect(string $code): string
    {
        $response = '';
        while (true) {
            $line = fgets($this->socket, 512);
            if ($line === false) {
                throw new \RuntimeException("Spojení přerušeno při čekání na kód $code");
            }
            $response .= $line;
            if (strlen($line) < 4 || $line[3] === ' ') {
                break;
            }
        }
        if (strncmp($response, $code, strlen($code)) !== 0) {
            throw new \RuntimeException("Očekáváno $code, přijato: " . trim($response));
        }
        return $response;
    }

    private function close(): void
    {
        if ($this->socket !== null) {
            fclose($this->socket);
            $this->socket = null;
        }
    }
}

// ============================================================
// NASTAVENÍ (admin e-mail a IBAN z content.json, viz níže po načtení)
// ============================================================
$googleScriptUrl = "https://script.google.com/macros/s/AKfycbzly0sIZtjukHx9EPX_9z5sgQ_0aEoUCdEEe61Y-HuBQo5vdvx9tg4_FNo8bw58fn2Fbw/exec";
// Volitelný tajný token pro ověření požadavku v Google Apps Scriptu.
// Nastavte stejnou hodnotu i ve skriptu; pro testování lze nechat prázdné.
$googleScriptToken = "uKp7Xq9R3bZ1mC5vT8yH2sN4wF6gJ0L";

// Základní URL webu – obrázky v e-mailu z vlastní domény
$baseUrl = 'https://' . ($_SERVER['HTTP_HOST'] ?? 'registrace.berounsko.net');

// Načtení obsahu z administrace (data/content.json)
require_once __DIR__ . '/inc/content.php';
$content = load_public_content();
$settings = $content['settings'] ?? [];
$adminEmail = $settings['adminEmail'] ?? 'trnkapavel@gmail.com';
$rawIban = $settings['iban'] ?? 'CZ15 3030 0000 0011 4692 8017';
$iban = str_replace(' ', '', $rawIban);

// DATA O VYCHÁZKÁCH z content.json (fallback na výchozí)
$walksFromContent = $content['walks']['items'] ?? [];
$walksData = [];
$walkPrices = []; // pricePerPerson per walk_id
foreach (['kras', 'svatojan', 'krivoklat', 'alkazar'] as $id) {
    $w = $walksFromContent[$id] ?? [];
    $walksData[$id] = [
        'name'     => $w['title'] ?? $id,
        'date_txt' => $w['date'] ?? '',
        'start'    => $w['start'] ?? '20240101T100000',
        'end'      => $w['end'] ?? '20240101T140000',
        'location' => $w['location'] ?? '',
        'guide'    => $w['guide'] ?? '',
        'img'      => $baseUrl . '/' . ltrim($w['img'] ?? 'img/placeholder.jpg', '/'),
    ];
    $walkPrices[$id] = (int)($w['pricePerPerson'] ?? 100);
}
if (empty($walksData)) {
    $walksData = ['kras' => ['name' => 'Vycházka', 'date_txt' => '', 'start' => '20240101T100000', 'end' => '20240101T140000', 'location' => '', 'guide' => '', 'img' => $baseUrl . '/img/placeholder.jpg']];
    $walkPrices = ['kras' => 0];
}

// ============================================================
// RATE LIMIT – kontrola před zpracováním dat
// ============================================================
$clientIp = isset($_SERVER['HTTP_X_FORWARDED_FOR'])
    ? trim(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0])
    : ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');

if (!rateLimitCheck($clientIp)) {
    http_response_code(429);
    echo json_encode(['success' => false, 'message' => 'Příliš mnoho požadavků. Zkuste to prosím za hodinu.']);
    exit;
}

// ============================================================
// VALIDACE A SANITIZACE VSTUPŮ
// ============================================================

// walk_id: musí být z předdefinovaného seznamu (whitelist)
$walkId = $_POST['walk_id'] ?? '';
if (!array_key_exists($walkId, $walksData)) {
    $walkId = 'kras';
}

// email: validace formátu
$email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Neplatná e-mailová adresa.']);
    exit;
}

// Ochrana proti header injection (nesmí obsahovat CR/LF)
if (preg_match('/[\r\n]/', $email)) {
    echo json_encode(['success' => false, 'message' => 'Neplatná e-mailová adresa.']);
    exit;
}

// count: celé číslo v rozsahu 1–20
$count = (int)($_POST['count'] ?? 0);
if ($count < 1 || $count > 20) {
    echo json_encode(['success' => false, 'message' => 'Neplatný počet osob (1–20).']);
    exit;
}

// Data vycházky pocházejí výhradně ze serverového pole – ne z POST
$info        = $walksData[$walkId];
$walkName    = $info['name'];
$walkDateTxt = $info['date_txt'];

// Escapované verze pro HTML výstup
$hEmail    = h($email);
$hWalkName = h($walkName);
$hWalkDate = h($walkDateTxt);
$hGuide    = h($info['guide']);
$hLocation = h($info['location']);
$hRawIban  = h($rawIban);
$hImg      = h($info['img']);

// Ceny (z content.json)
$pricePerPerson = $walkPrices[$walkId] ?? 100;
$totalPrice     = $count * $pricePerPerson;
$currentYear    = date('Y');

// ============================================================
// 1. GENERACE QR KÓDU
// ============================================================
$qrUrl    = '';
$qrImgTag = '';
if ($totalPrice > 0) {
    $msg   = 'Vychazka ' . substr($walkName, 0, 30);
    $spayd = "SPD*1.0*ACC:{$iban}*AM:{$totalPrice}.00*CC:CZK*MSG:" . rawurlencode($msg);
    $qrUrl    = 'https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=' . rawurlencode($spayd);
    $qrImgTag = '<div style="text-align:center; margin: 20px 0;"><img src="' . h($qrUrl) . '" alt="QR Platba" style="border: 5px solid #fff; box-shadow: 0 0 10px rgba(0,0,0,0.1); width: 200px;"></div>';
}

// ============================================================
// 2. ODESLÁNÍ DO GOOGLE SHEETS
// ============================================================
if (!empty($googleScriptUrl)) {
    $sheetData = [
        'date'    => date('Y-m-d H:i:s'),
        'walk'    => $walkName,
        'email'   => $email,
        'count'   => $count,
        'price'   => $totalPrice,
        'qr_link' => $qrUrl,
        'token'   => $googleScriptToken,
    ];
    $ch = curl_init($googleScriptUrl);
    curl_setopt($ch, CURLOPT_POSTFIELDS,     json_encode($sheetData));
    curl_setopt($ch, CURLOPT_HTTPHEADER,     ['Content-Type:application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT,        5);
    curl_exec($ch);
    curl_close($ch);
}

// ============================================================
// 3. SESTAVENÍ .ICS SOUBORU
// ============================================================
$uid = md5(uniqid(mt_rand(), true)) . '@berounsko.net';
$icsContent = implode("\r\n", [
    'BEGIN:VCALENDAR',
    'VERSION:2.0',
    'PRODID:-//Berounsko.net//Rezervace//CZ',
    'METHOD:PUBLISH',
    'BEGIN:VEVENT',
    "UID:{$uid}",
    'DTSTAMP:' . gmdate('Ymd') . 'T' . gmdate('His') . 'Z',
    "DTSTART:{$info['start']}",
    "DTEND:{$info['end']}",
    "SUMMARY:Vycházka: {$walkName}",
    "LOCATION:{$info['location']}",
    "DESCRIPTION:Průvodce: {$info['guide']}\\nPočet osob: {$count}\\nRezervace přes Berounsko.net",
    'STATUS:CONFIRMED',
    'END:VEVENT',
    'END:VCALENDAR',
]);

// ============================================================
// 4. HTML ŠABLONA E-MAILU PRO KLIENTA
// ============================================================
$paymentBlock = $totalPrice > 0
    ? <<<HTML
            <h3 style="color: #80C024; text-align: center;">Platba QR kódem</h3>
            <p style="text-align: center;">Pro dokončení rezervace prosím uhraďte částku pomocí QR kódu:</p>
            {$qrImgTag}
            <p style="text-align: center; font-size: 0.9em;">Číslo účtu: <strong>{$hRawIban}</strong></p>
HTML
    : '<h3 style="color: #80C024; text-align: center;">Vstup je zdarma</h3>';

$htmlClient = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Potvrzení rezervace</title>
    <style>
        body { font-family: 'Montserrat', sans-serif, Arial; background-color: #f4f4f4; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 0 auto; background: #ffffff; }
        .header { background-color: #30B0FF; padding: 30px; text-align: center; color: white; }
        .header h1 { margin: 0; font-size: 24px; text-transform: uppercase; letter-spacing: 1px; }
        .hero-image { width: 100%; height: auto; display: block; }
        .content { padding: 40px; color: #000000; line-height: 1.6; }
        .details-box { background-color: #f9f9f9; padding: 20px; border-left: 5px solid #80C024; margin: 20px 0; }
        .price-tag { font-size: 24px; color: #80C024; font-weight: bold; display: block; margin-top: 10px; }
        .footer { background-color: #333; color: #888; text-align: center; padding: 20px; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Rezervace potvrzena</h1>
        </div>

        <img src="{$hImg}" alt="{$hWalkName}" class="hero-image">

        <div class="content">
            <h2 style="color: #30B0FF; margin-top: 0;">Dobrý den,</h2>
            <p>děkujeme za Váš zájem o komentované vycházky <strong>Berounsko.net</strong>. Tímto potvrzujeme Vaši rezervaci.</p>

            <div class="details-box">
                <p><strong>Trasa:</strong> {$hWalkName}</p>
                <p><strong>Datum:</strong> {$hWalkDate}</p>
                <p><strong>Průvodce:</strong> {$hGuide}</p>
                <p><strong>Počet osob:</strong> {$count}</p>
                <hr style="border: 0; border-top: 1px solid #ddd;">
                <span class="price-tag">Cena celkem: {$totalPrice} Kč</span>
            </div>

            {$paymentBlock}

            <p style="font-size: 0.9em; color: #666; margin-top: 30px; text-align: center;">
                📅 <strong>Tip:</strong> V příloze tohoto e-mailu najdete soubor <strong>pozvanka.ics</strong>.<br>
                Uložte si událost přímo do svého kalendáře.
            </p>

            <p>Těšíme se na viděnou!</p>
        </div>

        <div class="footer">
            &copy; {$currentYear} Berounsko.net | Komentované vycházky
        </div>
    </div>
</body>
</html>
HTML;

// ============================================================
// 5. HTML ŠABLONA E-MAILU PRO ADMINA
// ============================================================
$htmlAdmin = <<<HTML
<html>
<body style="font-family: monospace; font-size: 14px; color: #333;">
    <h3 style="margin-bottom: 10px;">Nový účastník</h3>
    <hr>
    <strong>Vycházka:</strong> {$hWalkName}<br>
    <strong>Datum:</strong> {$hWalkDate}<br>
    <br>
    <strong>E-mail:</strong> <a href="mailto:{$hEmail}">{$hEmail}</a><br>
    <strong>Počet osob:</strong> {$count}<br>
    <strong>Cena celkem:</strong> {$totalPrice} Kč<br>
    <hr>
    <p style="color: #666; font-size: 12px;">Data odeslána do Google Sheet.</p>
</body>
</html>
HTML;

// ============================================================
// ODESLÁNÍ E-MAILŮ PŘES SMTP
// ============================================================
$subjectClient = "Potvrzení rezervace: {$walkName}";
$subjectAdmin  = "[Nová objednávka] {$walkName} ({$count} os.)";

$mailer = new SmtpMailer(SMTP_HOST, SMTP_PORT, SMTP_USER, SMTP_PASS, SMTP_SECURE);

$mail1 = $mailer->send(SMTP_FROM_EMAIL, SMTP_FROM_NAME, $email,      $subjectClient, $htmlClient, $icsContent);
usleep(300000);
$mail2 = $mailer->send(SMTP_FROM_EMAIL, SMTP_FROM_NAME, $adminEmail, $subjectAdmin,  $htmlAdmin,  null, $email);

echo json_encode($mail1
    ? ['success' => true]
    : ['success' => false, 'message' => 'Chyba odeslání e-mailu. Zkontrolujte SMTP nastavení v smtp.local.php.']
);
?>
