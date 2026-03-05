<?php
session_start();
require_once __DIR__ . '/config.php';

if (!admin_logged_in()) {
    header('Location: login.php');
    exit;
}

$c = load_content();
$contentPath = content_path();
$contentLoadOk = !empty($c);
$hero = $c['hero'] ?? [];
$benefits = $c['benefits'] ?? [];
$why = $c['whyBerounsko'] ?? [];
$guides = $c['guides'] ?? [];
$walks = $c['walks'] ?? [];
$faq = $c['faq'] ?? [];
$contact = $c['contact'] ?? [];
$footer = $c['footer'] ?? [];
$settings = $c['settings'] ?? [];
$walkItems = $walks['items'] ?? [];
$benefitItems = $benefits['items'] ?? [['title'=>'','text'=>'']];
while (count($benefitItems) < 4) $benefitItems[] = ['title'=>'','text'=>''];
$faqItems = $faq['items'] ?? [['q'=>'','a'=>'']];
while (count($faqItems) < 4) $faqItems[] = ['q'=>'','a'=>''];
$walkIds = ['kras' => 'Český kras', 'svatojan' => 'Svatojanský okruh', 'krivoklat' => 'Křivoklátsko', 'alkazar' => 'Alkazar'];
$success = isset($_GET['saved']);
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrace obsahu – Berounsko</title>
    <style>
        body { font-family: sans-serif; max-width: 720px; margin: 0 auto; padding: 24px; color: #333; }
        h1 { color: #192e7c; font-size: 1.5em; }
        h2 { color: #192e7c; font-size: 1.15em; margin-top: 28px; border-bottom: 1px solid #ddd; padding-bottom: 6px; }
        label { display: block; margin-top: 10px; font-weight: 600; font-size: 0.9em; }
        input[type="text"], input[type="email"], input[type="number"], textarea { width: 100%; padding: 8px; margin-top: 4px; box-sizing: border-box; font-family: inherit; }
        textarea { min-height: 80px; }
        .small { font-size: 0.85em; color: #666; }
        button { background: #92be44; color: #fff; border: none; padding: 12px 24px; font-size: 1em; cursor: pointer; margin-top: 20px; }
        .success { background: #e8f5e9; color: #2e7d32; padding: 12px; margin-bottom: 20px; border-radius: 6px; }
        .logout { font-size: 0.9em; margin-bottom: 20px; }
        .logout a { color: #68acf9; }
        .grid2 { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        /* Rychlá navigace */
        .nav-jump { background: #f0f4f8; padding: 12px 16px; margin-bottom: 24px; border-radius: 8px; border-left: 4px solid #192e7c; font-size: 0.9em; }
        .nav-jump strong { display: block; margin-bottom: 8px; color: #192e7c; }
        .nav-jump a { color: #68acf9; text-decoration: none; margin-right: 12px; }
        .nav-jump a:hover { text-decoration: underline; }
        /* Vycházky – výrazné barevné oddělení (paleta Berounsko) */
        .walk-block { margin-bottom: 32px; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        .walk-block-header { padding: 12px 20px; color: #fff; font-weight: 700; font-size: 1.05em; }
        .walk-block.walk-kras .walk-block-header   { background: #192e7c; }
        .walk-block.walk-svatojan .walk-block-header { background: #68acf9; }
        .walk-block.walk-krivoklat .walk-block-header { background: #92be44; }
        .walk-block.walk-alkazar .walk-block-header { background: #ea9b34; }
        .walk-block-body { padding: 18px 20px; border: 1px solid #e0e0e0; border-top: none; background: #fafafa; }
        .walk-block.walk-kras .walk-block-body   { border-left: 4px solid #192e7c; }
        .walk-block.walk-svatojan .walk-block-body { border-left: 4px solid #68acf9; }
        .walk-block.walk-krivoklat .walk-block-body { border-left: 4px solid #92be44; }
        .walk-block.walk-alkazar .walk-block-body { border-left: 4px solid #ea9b34; }
        .visually-hidden { position: absolute; width: 1px; height: 1px; margin: -1px; padding: 0; overflow: hidden; clip: rect(0,0,0,0); border: 0; }
    </style>
</head>
<body>
    <h1>Administrace obsahu</h1>
    <p class="logout"><a href="logout.php">Odhlásit</a></p>
    <?php if ($success): ?><p class="success">Obsah byl uložen.</p><?php endif; ?>
    <nav class="nav-jump" aria-label="Rychlý přeskok">
        <strong>Rychlý přeskok:</strong>
        <a href="#hero">Hero</a>
        <a href="#benefity">Benefity</a>
        <a href="#why">Proč Berounsko</a>
        <a href="#guides">Průvodci</a>
        <a href="#walks-intro">Vycházky – úvod</a>
        <a href="#walk-kras">Český kras</a>
        <a href="#walk-svatojan">Svatojanský okruh</a>
        <a href="#walk-krivoklat">Křivoklátsko</a>
        <a href="#walk-alkazar">Alkazar</a>
        <a href="#faq">FAQ</a>
        <a href="#contact">Kontakt</a>
        <a href="#footer">Patička</a>
        <a href="#settings">Nastavení</a>
    </nav>

    <?php if (!$contentLoadOk): ?>
    <p class="error" style="background:#ffebee;color:#c62828;padding:12px;margin-bottom:20px;border-radius:6px;">
        <strong>Obsah se nenačetl.</strong> Kontroluje se soubor:<br>
        <code style="font-size:0.85em;word-break:break-all;"><?= htmlspecialchars($contentPath) ?></code><br>
        <?php
        if (!file_exists($contentPath)) {
            echo 'Soubor neexistuje. Zkontrolujte, že složka <code>data/</code> je vedle složky <code>admin/</code> a v ní je <code>content.json</code>.';
        } elseif (!is_readable($contentPath)) {
            echo 'Soubor existuje, ale není čitelný (oprávnění).';
        } else {
            $err = get_content_load_error();
            $len = get_content_raw_length();
            if ($len === 0) {
                echo 'Soubor je prázdný (0 bajtů). Nahrajte do <code>data/content.json</code> platný JSON z repozitáře.';
            } else {
                echo 'Soubor je čitelný (' . (int)$len . ' bajtů), ale JSON se nepodařilo parsovat.';
                if ($err) {
                    echo ' <strong>Chyba:</strong> ' . htmlspecialchars($err);
                }
            }
        }
        ?>
    </p>
    <?php endif; ?>

    <form method="post" action="save.php">
        <h2 id="hero">Hero (úvodní sekce)</h2>
        <label>Nadpis</label>
        <input type="text" name="hero[title]" value="<?= htmlspecialchars($hero['title'] ?? '') ?>">
        <label>Podtitul</label>
        <textarea name="hero[subtitle]" rows="2"><?= htmlspecialchars($hero['subtitle'] ?? '') ?></textarea>

        <h2 id="benefity">Benefity (Proč komentované procházky)</h2>
        <label>Nadpis sekce</label>
        <input type="text" name="benefits[sectionTitle]" value="<?= htmlspecialchars($benefits['sectionTitle'] ?? '') ?>">
        <label>Úvodní odstavec</label>
        <textarea name="benefits[intro]" rows="3"><?= htmlspecialchars($benefits['intro'] ?? '') ?></textarea>
        <?php for ($i = 0; $i < 4; $i++): $b = $benefitItems[$i] ?? []; ?>
        <label>Benefit <?= $i + 1 ?> – nadpis</label>
        <input type="text" name="benefits[items][<?= $i ?>][title]" value="<?= htmlspecialchars($b['title'] ?? '') ?>">
        <label>Benefit <?= $i + 1 ?> – text</label>
        <textarea name="benefits[items][<?= $i ?>][text]" rows="2"><?= htmlspecialchars($b['text'] ?? '') ?></textarea>
        <?php endfor; ?>

        <h2 id="why">Proč to dělá Berounsko</h2>
        <label>Nadpis</label>
        <input type="text" name="whyBerounsko[title]" value="<?= htmlspecialchars($why['title'] ?? '') ?>">
        <label>Odstavec 1</label>
        <textarea name="whyBerounsko[paragraphs][]" rows="2"><?= htmlspecialchars($why['paragraphs'][0] ?? '') ?></textarea>
        <label>Odstavec 2</label>
        <textarea name="whyBerounsko[paragraphs][]" rows="2"><?= htmlspecialchars($why['paragraphs'][1] ?? '') ?></textarea>

        <h2 id="guides">Kdo vás provede</h2>
        <label>Nadpis</label>
        <input type="text" name="guides[title]" value="<?= htmlspecialchars($guides['title'] ?? '') ?>">
        <label>Odstavec 1</label>
        <textarea name="guides[paragraphs][]" rows="2"><?= htmlspecialchars($guides['paragraphs'][0] ?? '') ?></textarea>
        <label>Odstavec 2</label>
        <textarea name="guides[paragraphs][]" rows="2"><?= htmlspecialchars($guides['paragraphs'][1] ?? '') ?></textarea>

        <h2 id="walks-intro">Vycházky – společný úvod</h2>
        <label>Nadpis sekce</label>
        <input type="text" name="walks[sectionTitle]" value="<?= htmlspecialchars($walks['sectionTitle'] ?? '') ?>">
        <label>Úvodní text</label>
        <textarea name="walks[intro]" rows="2"><?= htmlspecialchars($walks['intro'] ?? '') ?></textarea>

        <?php foreach ($walkIds as $id => $label): $w = $walkItems[$id] ?? []; ?>
        <h2 id="walk-<?= $id ?>" class="visually-hidden">Vycházka: <?= htmlspecialchars($label) ?></h2>
        <div class="walk-block walk-<?= $id ?>">
            <div class="walk-block-header">Vycházka: <?= htmlspecialchars($label) ?></div>
            <div class="walk-block-body">
                <label>Název</label>
                <input type="text" name="walks[items][<?= $id ?>][title]" value="<?= htmlspecialchars($w['title'] ?? '') ?>">
                <label>Obrázek (cesta, např. img/srbsko-chlum.jpg)</label>
                <input type="text" name="walks[items][<?= $id ?>][img]" value="<?= htmlspecialchars($w['img'] ?? '') ?>">
                <label>Průvodce</label>
                <input type="text" name="walks[items][<?= $id ?>][guide]" value="<?= htmlspecialchars($w['guide'] ?? '') ?>">
                <div class="grid2">
                    <div><label>Datum (zobrazení)</label><input type="text" name="walks[items][<?= $id ?>][date]" value="<?= htmlspecialchars($w['date'] ?? '') ?>" placeholder="18. 4. 2026"></div>
                    <div><label>Délka</label><input type="text" name="walks[items][<?= $id ?>][distance]" value="<?= htmlspecialchars($w['distance'] ?? '') ?>" placeholder="4 km"></div>
                </div>
                <div class="grid2">
                    <div><label>Náročnost (1–5)</label><input type="number" name="walks[items][<?= $id ?>][difficulty]" min="1" max="5" value="<?= (int)($w['difficulty'] ?? 3) ?>"></div>
                    <div><label>Cena za osobu (Kč)</label><input type="number" name="walks[items][<?= $id ?>][pricePerPerson]" min="0" value="<?= (int)($w['pricePerPerson'] ?? 0) ?>"></div>
                </div>
                <label>Místo (pro e-mail/ICS)</label>
                <input type="text" name="walks[items][<?= $id ?>][location]" value="<?= htmlspecialchars($w['location'] ?? '') ?>">
                <label>Začátek (ICS, formát 20260418T100000)</label>
                <input type="text" name="walks[items][<?= $id ?>][start]" value="<?= htmlspecialchars($w['start'] ?? '') ?>">
                <label>Konec (ICS, formát 20260418T140000)</label>
                <input type="text" name="walks[items][<?= $id ?>][end]" value="<?= htmlspecialchars($w['end'] ?? '') ?>">
                <label>Popis (jedna věta na řádek)</label>
                <textarea name="walks[items][<?= $id ?>][desc_text]" rows="6"><?= htmlspecialchars(implode("\n", $w['desc'] ?? [])) ?></textarea>
            </div>
        </div>
        <?php endforeach; ?>

        <h2 id="faq">FAQ</h2>
        <label>Nadpis sekce</label>
        <input type="text" name="faq[title]" value="<?= htmlspecialchars($faq['title'] ?? '') ?>">
        <?php for ($i = 0; $i < 4; $i++): $f = $faqItems[$i] ?? []; ?>
        <label>Otázka <?= $i + 1 ?></label>
        <input type="text" name="faq[items][<?= $i ?>][q]" value="<?= htmlspecialchars($f['q'] ?? '') ?>">
        <label>Odpověď <?= $i + 1 ?></label>
        <textarea name="faq[items][<?= $i ?>][a]" rows="2"><?= htmlspecialchars($f['a'] ?? '') ?></textarea>
        <?php endfor; ?>

        <h2 id="contact">Kontakt</h2>
        <label>Nadpis</label>
        <input type="text" name="contact[title]" value="<?= htmlspecialchars($contact['title'] ?? '') ?>">
        <label>Úvodní text</label>
        <textarea name="contact[intro]" rows="2"><?= htmlspecialchars($contact['intro'] ?? '') ?></textarea>
        <label>E-mail</label>
        <input type="email" name="contact[email]" value="<?= htmlspecialchars($contact['email'] ?? '') ?>">

        <h2 id="footer">Patička</h2>
        <label>Text patičky</label>
        <input type="text" name="footer[text]" value="<?= htmlspecialchars($footer['text'] ?? '') ?>">

        <h2 id="settings">Nastavení (e-maily, platby)</h2>
        <label>Admin e-mail (kam chodí oznámení o rezervacích)</label>
        <input type="email" name="settings[adminEmail]" value="<?= htmlspecialchars($settings['adminEmail'] ?? '') ?>">
        <label>IBAN (zobrazení v e-mailu)</label>
        <input type="text" name="settings[iban]" value="<?= htmlspecialchars($settings['iban'] ?? '') ?>">

        <button type="submit">Uložit veškerý obsah</button>
    </form>
</body>
</html>
