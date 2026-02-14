<?php
// rezervace.php
header('Content-Type: application/json; charset=utf-8');

// --- NASTAVENÍ ---
$adminEmail = "trnkapavel@gmail.com";
// Váš IBAN (odstraníme mezery pro jistotu)
$rawIban = "CZ15 3030 0000 0011 4692 8017"; 
$iban = str_replace(' ', '', $rawIban);

// URL vašeho Google Apps Scriptu
$googleScriptUrl = "https://script.google.com/macros/s/AKfycbzly0sIZtjukHx9EPX_9z5sgQ_0aEoUCdEEe61Y-HuBQo5vdvx9tg4_FNo8bw58fn2Fbw/exec"; 

// Odkazy na obrázky pro e-mail (stejné jako v JS)
$walkImages = [
    'kras'      => 'https://images.unsplash.com/photo-1605199216405-0239b35d143a?w=600&q=80',
    'svatojan'  => 'https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?w=600&q=80',
    'krivoklat' => 'https://images.unsplash.com/photo-1441974231531-c6227db76b6e?w=600&q=80',
    'alkazar'   => 'https://images.unsplash.com/photo-1560097020-591b96717792?w=600&q=80'
];

// --- ZPRACOVÁNÍ DAT ---
$walkId = $_POST['walk_id'] ?? 'kras';
$walkName = $_POST['walk_name'] ?? 'Neznámá vycházka';
$email = $_POST['email'] ?? '';
$count = (int)($_POST['count'] ?? 0);

if (!$email || !$count) {
    echo json_encode(['success' => false, 'message' => 'Chybí e-mail nebo počet osob.']);
    exit;
}

// Ceny
$pricePerPerson = ($walkId === 'kras') ? 0 : 100;
$totalPrice = $count * $pricePerPerson;

// Vybereme obrázek
$currentImage = $walkImages[$walkId] ?? $walkImages['kras'];

// --- 1. GENERACE QR KÓDU (SPAYD) ---
$qrUrl = "";
$qrImgTag = "";
if ($totalPrice > 0) {
    $msg = "Vychazka " . substr($walkName, 0, 30); // Zkrácení pro zprávu
    $spayd = "SPD*1.0*ACC:{$iban}*AM:{$totalPrice}.00*CC:CZK*MSG:{$msg}";
    $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=" . urlencode($spayd);
    $qrImgTag = "<div style='text-align:center; margin: 20px 0;'><img src='$qrUrl' alt='QR Platba' style='border: 5px solid #fff; box-shadow: 0 0 10px rgba(0,0,0,0.1); width: 200px;'></div>";
}

// --- 2. ODESLÁNÍ DO GOOGLE SHEETS (OPRAVENO) ---
if (!empty($googleScriptUrl)) {
    // Data, která chceme poslat do skriptu
    $sheetData = [
        'date'    => date('Y-m-d H:i:s'),
        'walk'    => $walkName,
        'email'   => $email,
        'count'   => $count,
        'price'   => $totalPrice,
        'qr_link' => $qrUrl
    ];
    
    // Použití cURL pro odeslání POST požadavku
    $ch = curl_init($googleScriptUrl);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($sheetData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); 
    curl_setopt($ch, CURLOPT_TIMEOUT, 10); // Timeout 10s, ať se nečeká věčně
    
    // Provedení a ignorování odpovědi (abychom nezdržovali uživatele, pokud je Google pomalý)
    $result = curl_exec($ch);
    curl_close($ch);
}

// --- 3. E-MAIL PRO KLIENTA (HTML) ---
$subjectClient = "Potvrzení rezervace: $walkName";

// Styly barev
$cBlue = "#30B0FF";
$cGreen = "#80C024";
$cOrange = "#FF9200";
$cText = "#000000";

$msgClient = "
<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>Potvrzení rezervace</title>
    <style>
        body { font-family: 'Montserrat', sans-serif, Arial; background-color: #f4f4f4; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 0 auto; background: #ffffff; }
        .header { background-color: $cBlue; padding: 30px; text-align: center; color: white; }
        .header h1 { margin: 0; font-size: 24px; text-transform: uppercase; letter-spacing: 1px; }
        .hero-image { width: 100%; height: auto; display: block; }
        .content { padding: 40px; color: $cText; line-height: 1.6; }
        .details-box { background-color: #f9f9f9; padding: 20px; border-left: 5px solid $cGreen; margin: 20px 0; }
        .price-tag { font-size: 24px; color: $cGreen; font-weight: bold; display: block; margin-top: 10px; }
        .footer { background-color: #333; color: #888; text-align: center; padding: 20px; font-size: 12px; }
        .btn { display: inline-block; background: $cOrange; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: bold; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h1>Rezervace potvrzena</h1>
        </div>

        <img src='$currentImage' alt='$walkName' class='hero-image'>

        <div class='content'>
            <h2 style='color: $cBlue; margin-top: 0;'>Dobrý den,</h2>
            <p>děkujeme za Váš zájem o komentované vycházky <strong>Berounsko.net</strong>. Tímto potvrzujeme Vaši rezervaci.</p>

            <div class='details-box'>
                <p><strong>Trasa:</strong> $walkName</p>
                <p><strong>Datum:</strong> (Dle webu)</p>
                <p><strong>Počet osob:</strong> $count</p>
                <hr style='border: 0; border-top: 1px solid #ddd;'>
                <span class='price-tag'>Cena celkem: $totalPrice Kč</span>
            </div>

            " . ($totalPrice > 0 ? "
            <h3 style='color: $cGreen; text-align: center;'>Platba QR kódem</h3>
            <p style='text-align: center;'>Pro dokončení rezervace prosím uhraďte částku pomocí QR kódu:</p>
            $qrImgTag
            <p style='text-align: center; font-size: 0.9em;'>Číslo účtu: <strong>$rawIban</strong></p>
            " : "<h3 style='color: $cGreen; text-align: center;'>Vstup je zdarma</h3>") . "

            <p>Těšíme se na viděnou!</p>
        </div>

        <div class='footer'>
            &copy; " . date('Y') . " Berounsko.net | Komentované vycházky
        </div>
    </div>
</body>
</html>
";

$headersClient = "MIME-Version: 1.0" . "\r\n";
$headersClient .= "Content-type:text/html;charset=UTF-8" . "\r\n";
$headersClient .= "From: rezervace@berounsko.net" . "\r\n";

// --- 4. E-MAIL PRO ADMINA ---
$subjectAdmin = "[Nová objednávka] $walkName ($count os.)";
$msgAdmin = "
<html>
<body style='font-family: monospace; font-size: 14px; color: #333;'>
    <h3 style='margin-bottom: 10px;'>Nový účastník</h3>
    <hr>
    <strong>Vycházka:</strong> $walkName<br>
    <strong>Datum:</strong> " . date('d.m.Y H:i') . "<br>
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
$mail1 = mail($email, $subjectClient, $msgClient, $headersClient);
usleep(500000); // 0.5s pauza
$mail2 = mail($adminEmail, $subjectAdmin, $msgAdmin, $headersAdmin);

if($mail1) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Chyba odeslání e-mailu']);
}
?>