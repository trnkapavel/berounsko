<?php
// rezervace.php
header('Content-Type: application/json; charset=utf-8');

// --- NASTAVENÍ ---
$adminEmail = "trnkapavel@gmail.com";
$rawIban = "CZ15 3030 0000 0011 4692 8017"; 
$iban = str_replace(' ', '', $rawIban);

// URL vašeho Google Apps Scriptu
$googleScriptUrl = "https://script.google.com/macros/s/AKfycbzly0sIZtjukHx9EPX_9z5sgQ_0aEoUCdEEe61Y-HuBQo5vdvx9tg4_FNo8bw58fn2Fbw/exec"; 

// --- DATA O VYCHÁZKÁCH (CENTRÁLNÍ MOZEK) ---
// Zde jsou definována data, časy a obrázky pro e-maily a kalendář
$walksData = [
    'kras' => [
        'name' => 'CHKO Český kras',
        'date_txt' => '15. 4. 2024',       // Text pro e-mail
        'start' => '20240415T100000',      // Formát pro kalendář (RRRRMMDDThhmmss)
        'end'   => '20240415T140000',      // Konec akce (o 4h později)
        'location' => 'Srbsko, Česká republika',
        'guide' => 'RNDr. Petr Skála',
        'img' => 'https://images.unsplash.com/photo-1605199216405-0239b35d143a?w=600&q=80'
    ],
    'svatojan' => [
        'name' => 'Svatojanský okruh',
        'date_txt' => '22. 4. 2024',
        'start' => '20240422T100000', 
        'end'   => '20240422T130000',
        'location' => 'Svatý Jan pod Skálou',
        'guide' => 'Mgr. Jana Veselá',
        'img' => 'https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?w=600&q=80'
    ],
    'krivoklat' => [
        'name' => 'CHKO Křivoklátsko',
        'date_txt' => '12. 5. 2024',
        'start' => '20240512T100000', 
        'end'   => '20240512T140000',
        'location' => 'Roztoky u Křivoklátu',
        'guide' => 'Ing. Karel Les',
        'img' => 'https://images.unsplash.com/photo-1441974231531-c6227db76b6e?w=600&q=80'
    ],
    'alkazar' => [
        'name' => 'Alkazar',
        'date_txt' => '19. 5. 2024',
        'start' => '20240519T100000', 
        'end'   => '20240519T130000',
        'location' => 'Hostim u Berouna',
        'guide' => 'Tomáš Průvodce',
        'img' => 'https://images.unsplash.com/photo-1560097020-591b96717792?w=600&q=80'
    ]
];

// --- ZPRACOVÁNÍ DAT Z FORMULÁŘE ---
$walkId = $_POST['walk_id'] ?? 'kras';
$email = $_POST['email'] ?? '';
$count = (int)($_POST['count'] ?? 0);

if (!$email || !$count) {
    echo json_encode(['success' => false, 'message' => 'Chybí e-mail nebo počet osob.']);
    exit;
}

// Načtení detailů o konkrétní vycházce
$info = $walksData[$walkId] ?? $walksData['kras'];
$walkName = $info['name'];
$walkDateTxt = $info['date_txt']; // ZDE JE OPRAVA DATA PRO E-MAIL

// Ceny
$pricePerPerson = ($walkId === 'kras') ? 0 : 100;
$totalPrice = $count * $pricePerPerson;

// --- 1. GENERACE QR KÓDU ---
$qrUrl = "";
$qrImgTag = "";
if ($totalPrice > 0) {
    $msg = "Vychazka " . substr($walkName, 0, 30);
    $spayd = "SPD*1.0*ACC:{$iban}*AM:{$totalPrice}.00*CC:CZK*MSG:{$msg}";
    $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=" . urlencode($spayd);
    $qrImgTag = "<div style='text-align:center; margin: 20px 0;'><img src='$qrUrl' alt='QR Platba' style='border: 5px solid #fff; box-shadow: 0 0 10px rgba(0,0,0,0.1); width: 200px;'></div>";
}

// --- 2. ODESLÁNÍ DO GOOGLE SHEETS ---
if (!empty($googleScriptUrl)) {
    $sheetData = [
        'date'    => date('Y-m-d H:i:s'),
        'walk'    => $walkName,
        'email'   => $email,
        'count'   => $count,
        'price'   => $totalPrice,
        'qr_link' => $qrUrl
    ];
    
    $ch = curl_init($googleScriptUrl);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($sheetData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); 
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    $result = curl_exec($ch);
    curl_close($ch);
}

// --- 3. SESTAVENÍ .ICS SOUBORU (KALENDÁŘ) ---
$uid = md5(uniqid(mt_rand(), true)) . "@berounsko.net";
$icsContent = "BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//Berounsko.net//Rezervace//CZ
METHOD:PUBLISH
BEGIN:VEVENT
UID:{$uid}
DTSTAMP:" . gmdate('Ymd').'T'. gmdate('His') . "Z
DTSTART:{$info['start']}
DTEND:{$info['end']}
SUMMARY:Vycházka: {$walkName}
LOCATION:{$info['location']}
DESCRIPTION:Průvodce: {$info['guide']}\\nPočet osob: {$count}\\nRezervace přes Berounsko.net
STATUS:CONFIRMED
END:VEVENT
END:VCALENDAR";


// --- 4. E-MAIL PRO KLIENTA (MULTIPART - HTML + PŘÍLOHA) ---
$boundary = md5(time()); // Oddělovač částí e-mailu

$subjectClient = "Potvrzení rezervace: $walkName";

// Hlavičky definují, že jde o složený e-mail
$headersClient = "MIME-Version: 1.0\r\n";
$headersClient .= "From: rezervace@berounsko.net\r\n";
$headersClient .= "Content-Type: multipart/mixed; boundary=\"{$boundary}\"\r\n";

// -- TĚLO E-MAILU --

// ČÁST 1: HTML E-mail
$msgClient = "--{$boundary}\r\n";
$msgClient .= "Content-Type: text/html; charset=UTF-8\r\n";
$msgClient .= "Content-Transfer-Encoding: 7bit\r\n\r\n";

// HTML Šablona (Váš design)
$msgClient .= "
<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
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
    <div class='container'>
        <div class='header'>
            <h1>Rezervace potvrzena</h1>
        </div>

        <img src='{$info['img']}' alt='$walkName' class='hero-image'>

        <div class='content'>
            <h2 style='color: #30B0FF; margin-top: 0;'>Dobrý den,</h2>
            <p>děkujeme za Váš zájem o komentované vycházky <strong>Berounsko.net</strong>. Tímto potvrzujeme Vaši rezervaci.</p>

            <div class='details-box'>
                <p><strong>Trasa:</strong> $walkName</p>
                <p><strong>Datum:</strong> $walkDateTxt</p>
                <p><strong>Průvodce:</strong> {$info['guide']}</p>
                <p><strong>Počet osob:</strong> $count</p>
                <hr style='border: 0; border-top: 1px solid #ddd;'>
                <span class='price-tag'>Cena celkem: $totalPrice Kč</span>
            </div>

            " . ($totalPrice > 0 ? "
            <h3 style='color: #80C024; text-align: center;'>Platba QR kódem</h3>
            <p style='text-align: center;'>Pro dokončení rezervace prosím uhraďte částku pomocí QR kódu:</p>
            $qrImgTag
            <p style='text-align: center; font-size: 0.9em;'>Číslo účtu: <strong>$rawIban</strong></p>
            " : "<h3 style='color: #80C024; text-align: center;'>Vstup je zdarma</h3>") . "
            
            <p style='font-size: 0.9em; color: #666; margin-top: 30px; text-align: center;'>
                📅 <strong>Tip:</strong> V příloze tohoto e-mailu najdete soubor <strong>pozvanka.ics</strong>.<br>
                Uložte si událost přímo do svého kalendáře.
            </p>

            <p>Těšíme se na viděnou!</p>
        </div>

        <div class='footer'>
            &copy; " . date('Y') . " Berounsko.net | Komentované vycházky
        </div>
    </div>
</body>
</html>
\r\n";

// ČÁST 2: Příloha .ICS
$msgClient .= "--{$boundary}\r\n";
$msgClient .= "Content-Type: text/calendar; name=\"pozvanka.ics\"; method=REQUEST\r\n";
$msgClient .= "Content-Transfer-Encoding: base64\r\n";
$msgClient .= "Content-Disposition: attachment; filename=\"pozvanka.ics\"\r\n\r\n";
$msgClient .= chunk_split(base64_encode($icsContent)) . "\r\n";
$msgClient .= "--{$boundary}--"; // Konec e-mailu


// --- 5. E-MAIL PRO ADMINA (Zůstává jednoduchý) ---
$subjectAdmin = "[Nová objednávka] $walkName ($count os.)";
$msgAdmin = "
<html>
<body style='font-family: monospace; font-size: 14px; color: #333;'>
    <h3 style='margin-bottom: 10px;'>Nový účastník</h3>
    <hr>
    <strong>Vycházka:</strong> $walkName<br>
    <strong>Datum:</strong> $walkDateTxt<br>
    <br>
    <strong>Jméno/Email:</strong> <a href='mailto:$email'>$email</a><br>
    <strong>Počet osob:</strong> $count<br>
    <strong>Cena celkem:</strong> $totalPrice Kč<br>
    <hr>
    <p style='color: #666; font-size: 12px;'>Data odeslána do Google Sheet.</p>
</body>
</html>
";

$headersAdmin = "MIME-Version: 1.0" . "\r\n";
$headersAdmin .= "Content-type:text/html;charset=UTF-8" . "\r\n";
$headersAdmin .= "From: rezervace@berounsko.net" . "\r\n";
$headersAdmin .= "Reply-To: $email" . "\r\n";

// Odeslání
$mail1 = mail($email, $subjectClient, $msgClient, $headersClient); // Klient dostane Multipart
usleep(500000); 
$mail2 = mail($adminEmail, $subjectAdmin, $msgAdmin, $headersAdmin); // Admin dostane HTML

if($mail1) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Chyba odeslání e-mailu']);
}
?>