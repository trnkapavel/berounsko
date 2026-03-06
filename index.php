<?php
require_once __DIR__ . '/inc/content.php';
$c = load_public_content();
$hero = array_merge(['title' => 'Komentované vycházky přírodou', 'subtitle' => 'Objevte Berounsko, Český kras a Křivoklátsko s odborným výkladem. Rezervujte si místo na procházce.'], $c['hero'] ?? []);
$benefits = array_merge(['sectionTitle' => 'Proč komentované procházky?', 'intro' => '', 'items' => []], $c['benefits'] ?? []);
$why = array_merge(['title' => 'Proč to dělá Berounsko?', 'paragraphs' => []], $c['whyBerounsko'] ?? []);
$guides = array_merge(['title' => 'Kdo vás provede', 'paragraphs' => []], $c['guides'] ?? []);
$walksData = $c['walks'] ?? [];
$walksSection = array_merge(['sectionTitle' => 'Aktuální vycházky a registrace', 'intro' => 'Vyberte si trasu a zaregistrujte se.'], $walksData);
$walksItems = $walksSection['items'] ?? [];
$faq = array_merge(['title' => 'Časté dotazy', 'items' => []], $c['faq'] ?? []);
$contact = array_merge(['title' => 'Kontakt', 'intro' => 'Máte dotaz k vycházkám nebo spolupráci? Napište nám.', 'email' => 'info@berounsko.net'], $c['contact'] ?? []);
$footer = array_merge(['text' => '© 2026 Berounsko.net – Komentované vycházky po Berounsku, Českém krasu a Křivoklátsku.'], $c['footer'] ?? []);
// Data vycházek pro JS (stejná struktura jako dříve)
$walksForJs = [];
foreach (['kras' => 'kras', 'svatojan' => 'svatojan', 'krivoklat' => 'krivoklat', 'alkazar' => 'alkazar'] as $id) {
    $w = $walksItems[$id] ?? [];
    $walksForJs[$id] = [
        'title' => $w['title'] ?? '',
        'img' => $w['img'] ?? 'img/placeholder.jpg',
        'guide' => $w['guide'] ?? '',
        'date' => $w['date'] ?? '',
        'distance' => $w['distance'] ?? '',
        'difficulty' => (int)($w['difficulty'] ?? 3),
        'pricePerPerson' => (int)($w['pricePerPerson'] ?? 0),
        'desc' => $w['desc'] ?? [],
    ];
}
function h($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

// Nejbližší nadcházející vycházka (pro odpočet v hero)
$now = time();
$countdownTarget = null;
$countdownWalkTitle = '';
foreach ($walksItems as $w) {
    $s = $w['start'] ?? '';
    if (preg_match('/^(\d{4})(\d{2})(\d{2})T(\d{2})(\d{2})(\d{2})$/', $s, $m)) {
        $ts = mktime((int)$m[4], (int)$m[5], (int)$m[6], (int)$m[2], (int)$m[3], (int)$m[1]);
        if ($ts > $now && ($countdownTarget === null || $ts < $countdownTarget)) {
            $countdownTarget = $ts;
            $countdownWalkTitle = $w['title'] ?? '';
        }
    }
}
$countdownIso = $countdownTarget ? date('Y-m-d\TH:i:s', $countdownTarget) : '';
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Berounsko.net - Komentované vycházky</title>
    
    <!-- Meta tagy pro popis -->
    <meta name="description" content="Komentované vycházky po Berounsku, Českém krasu a Křivoklátsku. Objevte krásy přírody s odborným výkladem. Rezervujte si místo na procházce!">
    <meta name="keywords" content="Berounsko, vycházky, Český kras, Křivoklátsko, procházky, turistika, příroda">
    
    <!-- Open Graph tagy pro Facebook, WhatsApp, Messenger -->
    <meta property="og:title" content="Berounsko.net - Komentované vycházky">
    <meta property="og:description" content="Komentované vycházky po Berounsku, Českém krasu a Křivoklátsku s odborným výkladem. Objevte krásy přírody s profesionálními průvodci!">
    <meta property="og:image" content="https://trnka.website/berounsko/screenshot/screenshot.jpg">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:url" content="https://berounsko.net">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="cs_CZ">
    <meta property="og:site_name" content="Berounsko.net">
    
    <!-- Twitter Card tagy -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Berounsko.net - Komentované vycházky">
    <meta name="twitter:description" content="Komentované vycházky po Berounsku, Českém krasu a Křivoklátsku s odborným výkladem.">
    <meta name="twitter:image" content="https://trnka.website/berounsko/screenshot/screenshot.jpg">
    
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        /* --- 0. RESET & GLOBAL & ANIMACE --- */
        body, html { margin: 0; padding: 0; font-family: 'Montserrat', sans-serif; height: 100%; }
        * { box-sizing: border-box; outline: none; }

        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        @keyframes slideInUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes popIn { 
            0% { transform: scale(0); opacity: 0; } 
            70% { transform: scale(1.2); opacity: 1; } 
            100% { transform: scale(1); opacity: 1; } 
        }
        
        /* --- 1. HERO SEKCE (šikmý přechod 2026) --- */
        .hero-section {
            min-height: 85vh; display: flex; align-items: center; justify-content: center; position: relative; overflow: hidden;
            clip-path: polygon(0 0, 100% 0, 100% 97%, 0 100%);
            padding-bottom: 8vh;
        }
        .hero-bg { position: absolute; inset: 0; z-index: 0; }
        .hero-bg-slide {
            position: absolute; inset: 0; background-size: cover; background-position: center;
            opacity: 0; transition: opacity 1.8s ease-in-out;
        }
        .hero-bg-slide.active { opacity: 1; z-index: 1; }
        .hero-overlay { position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(25,46,124,0.45); z-index: 2; }
        .hero-content { position: relative; z-index: 3; text-align: center; color: white; animation: slideInUp 0.8s ease-out; padding: 0 20px; }
        .hero-title { font-size: clamp(2.2rem, 5vw, 4rem); font-weight: 700; margin-bottom: 24px; text-shadow: 0 2px 20px rgba(0,0,0,0.5); line-height: 1.15; }
        .hero-subtitle { font-size: clamp(1.15rem, 2.2vw, 1.5rem); margin-bottom: 24px; max-width: 560px; margin-left: auto; margin-right: auto; opacity: 0.95; line-height: 1.5; }
        .hero-countdown { margin-bottom: 32px; }
        .hero-countdown-label { font-size: 0.95em; opacity: 0.9; margin-bottom: 12px; }
        .hero-countdown-grid { display: flex; justify-content: center; flex-wrap: wrap; gap: 12px 20px; }
        .hero-countdown-item { background: rgba(255,255,255,0.15); backdrop-filter: blur(4px); border-radius: 10px; padding: 14px 20px; min-width: 72px; text-align: center; border: 1px solid rgba(255,255,255,0.25); }
        .hero-countdown-item span { display: block; font-size: 1.8em; font-weight: 700; line-height: 1.2; }
        .hero-countdown-item small { font-size: 0.7em; opacity: 0.9; text-transform: uppercase; letter-spacing: 0.5px; }
        .hero-countdown-done { font-size: 1.1em; font-weight: 600; padding: 12px 24px; background: rgba(146,190,68,0.9); border-radius: 8px; }

        /* Hlavní CTA – zelená jako „Další místa“ na referenčním webu */
        .hero-btn {
            padding: 18px 44px; font-size: 1.15em; background-color: var(--c-green);
            color: #111; border: none; border-radius: 50px; cursor: pointer; font-weight: 600;
            box-shadow: 0 4px 16px rgba(146,190,68,0.35);
            transition: transform 0.3s, background-color 0.2s;
            text-transform: uppercase; letter-spacing: 1px;
        }
        .hero-btn:hover { transform: scale(1.05) translateY(-3px); background-color: #7cc936; color: #111; }

        /* --- 2. BAREVNÁ PALETA BEROUNSKO --- */
        :root {
            /* primární tmavě modrá – navigace, patička */
            --c-dark: #192e7c;
            /* světlejší modrá – akcent, tlačítka */
            --c-accent: #68acf9;
            /* pomocné odstíny modré pro texty/pozadí */
            --c-navy: #192e7c;
            --c-muted: #68acf9;
            /* zelená a oranžová z palety */
            --c-blue: #68acf9;
            --c-green: #92be44;
            --c-orange: #ea9b34;
            --c-red: #FF4444;
            --c-text: #1a1a1a;
            --font-main: 'Montserrat', sans-serif;
        }

        /* --- 3. MODAL --- */
        .modal-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.8); z-index: 1000;
            display: none; justify-content: center; align-items: center;
            backdrop-filter: blur(5px);
            opacity: 0; transition: opacity 0.3s ease;
        }
        .modal-overlay.is-visible { opacity: 1; }

        .modal-window {
            background: #fff; width: 90%; max-width: 1000px; height: 90vh; /* Fixní výška pro desktop */
            display: flex; flex-direction: row; border-radius: 8px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.3);
            font-family: var(--font-main); color: var(--c-text); position: relative;
            transform: scale(0.95); opacity: 0;
            transition: transform 0.3s, opacity 0.3s;
            overflow: hidden; /* Důležité pro sticky footer */
        }
        .modal-overlay.is-visible .modal-window { transform: scale(1); opacity: 1; }

        .modal-left {
            flex: 1; background-size: cover; background-position: center; min-height: 300px; position: relative;
            transition: opacity 0.2s ease;
        }
        .modal-left::after {
            content: ''; position: absolute; top:0; left:0; right:0; bottom:0;
            background: linear-gradient(to bottom, transparent 70%, rgba(0,0,0,0.6));
        }

        /* UPRAVENÝ MODAL RIGHT PRO STICKY FOOTER */
        .modal-right {
            flex: 1; 
            display: flex; flex-direction: column; /* Pod sebe */
            position: relative;
            overflow: hidden; /* Skryje scrollbar na hlavním kontejneru */
            padding: 0; /* Padding přesunut dovnitř */
        }

        /* Scrollable content area */
        .scroll-content {
            flex: 1; /* Zabere zbytek místa */
            overflow-y: auto; /* Scrollování pouze zde */
            padding: 40px;
            padding-bottom: 20px;
        }
        
        /* Fixed footer area */
        .fixed-footer {
            flex-shrink: 0; /* Nesmrskne se */
            padding: 20px 40px;
            background: #fff;
            border-top: 1px solid #f0f0f0;
            box-shadow: 0 -5px 20px rgba(0,0,0,0.05); /* Stín nahoru */
            z-index: 20;
        }

        /* Wrapper pro přepínání view (Form vs Success) */
        .view-wrapper {
            display: flex; flex-direction: column; height: 100%;
        }

        .close-modal {
            position: absolute; right: 20px; top: 15px; cursor: pointer; z-index: 50;
            background: rgba(255,255,255,0.95); border-radius: 50%; width: 44px; height: 44px;
            display: flex; align-items: center; justify-content: center;
            border: none; padding: 0; transition: transform 0.25s ease, background 0.2s;
        }
        .close-modal { color: #333; }
        .close-modal svg { width: 22px; height: 22px; transition: color 0.2s; }
        .close-modal:hover { background: #fff; color: var(--c-dark); transform: rotate(90deg); }

        h2 { font-weight: 600; margin-bottom: 20px; color: var(--c-dark); margin-top: 0; }
        
        /* --- 4. PŘEPÍNAČE --- */
        .walk-selector { display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 20px; }
        .walk-btn {
            padding: 10px 18px; border: 2px solid #ddd; border-radius: 50px;
            cursor: pointer; font-size: 0.9em; background: #fff; color: #333; font-weight: 500;
            transition: all 0.25s ease;
        }
        .walk-btn:hover { border-color: var(--c-accent); color: var(--c-dark); }
        .walk-btn.active {
            border-color: var(--c-orange); background: var(--c-orange); color: #111; font-weight: 600; transform: scale(1.02);
        }

        /* --- 5. ROZBALOVÁNÍ TEXTU --- */
        .annotation-box ul { padding-left: 20px; margin-bottom: 5px; }
        .annotation-box li { margin-bottom: 5px; font-size: 0.95em; line-height: 1.4; }
        
        .hidden-content-wrapper {
            max-height: 0; overflow: hidden; opacity: 0; transition: max-height 0.4s ease, opacity 0.4s ease;
        }
        .hidden-content-wrapper.is-open { max-height: 500px; opacity: 1; }
        
        .toggle-details-btn {
            color: var(--c-accent); font-weight: 600; font-size: 0.9em;
            cursor: pointer; display: inline-block; margin-left: 20px; margin-bottom: 20px;
            text-decoration: underline;
        }

        /* --- 6. INFO ROW --- */
        .info-row {
            background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 20px;
            border-left: 4px solid var(--c-accent);
            display: grid; grid-template-columns: 1fr 1fr; gap: 15px;
        }
        .info-item { display: flex; flex-direction: column; }
        .info-label { font-weight: 400; font-size: 0.85em; color: #666; margin-bottom: 3px; }
        .info-value { font-weight: 600; font-size: 1em; color: #000; }

        /* --- 7. INDIKÁTOR NÁROČNOSTI --- */
        .difficulty-wrapper { display: flex; gap: 4px; align-items: center; margin-top: 2px; }
        .diff-dot {
            width: 12px; height: 12px; border-radius: 50%; background-color: #ddd; transform: scale(0);
        }
        .diff-dot.animate { animation: popIn 0.3s forwards; }
        .diff-dot.active-green { background-color: var(--c-green); }
        .diff-dot.active-orange { background-color: var(--c-orange); }
        .diff-dot.active-red { background-color: var(--c-red); }

        /* --- 8. FORMULÁŘ --- */
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: 600; font-size: 0.9em; }
        input, select {
            width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 6px;
            font-family: var(--font-main); font-size: 1em; background: #fafafa;
        }
        
        /* Cena a tlačítko jsou nyní v patičce, ale styly zůstávají */
        .price-display {
            font-size: 1.4em; font-weight: 600; color: var(--c-green); text-align: center; margin-bottom: 15px;
        }
        .btn-submit {
            background: var(--c-green); color: #111; border: none; padding: 14px 24px;
            width: 100%; font-size: 1.1em; font-weight: 600; border-radius: 50px; cursor: pointer;
            transition: background 0.25s, color 0.2s;
        }
        .btn-submit:hover { background: #7cc936; color: #111; }
        .btn-submit:disabled { background: #ccc; color: #555; cursor: not-allowed; }
        .btn-submit.btn-secondary { background: #444; color: #fff; }
        .btn-submit.btn-secondary:hover { background: #333; color: #fff; }

        /* --- 9. SUCCESS STAV --- */
        .success-message {
            display: none;
            flex-direction: column; align-items: center; justify-content: center;
            text-align: center; height: 100%; width: 100%;
            padding: 40px;
        }
        
        .checkmark-circle {
            width: 80px; height: 80px; position: relative; display: inline-block;
            border-radius: 50%; border: 3px solid #80C024; margin-bottom: 20px;
            transform: scale(0); opacity: 0;
        }
        
        .success-message.is-active .checkmark-circle {
            animation: popIn 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
        }

        .checkmark-circle::after {
            content: ''; position: absolute; top: 50%; left: 50%; width: 25px; height: 13px;
            border-left: 4px solid #80C024; border-bottom: 4px solid #80C024;
            transform: translate(-50%, -65%) rotate(-45deg);
        }
        .success-text h3 { color: #80C024; font-size: 1.8em; margin: 0 0 10px 0; }

        /* --- 10. LANDING PAGE: HEADER (paleta Berounsko) --- */
        .site-header {
            position: fixed; top: 0; left: 0; right: 0; z-index: 100;
            width: 100%;
            margin: 0;
            background: var(--c-dark);
            box-shadow: 0 2px 20px rgba(0,0,0,0.2);
            padding: 14px 28px; display: flex; align-items: center; justify-content: space-between; gap: 32px;
            border-radius: 0;
            transition: top 0.4s cubic-bezier(0.4, 0, 0.2, 1), left 0.4s cubic-bezier(0.4, 0, 0.2, 1), width 0.4s cubic-bezier(0.4, 0, 0.2, 1), margin-left 0.4s cubic-bezier(0.4, 0, 0.2, 1), padding 0.4s cubic-bezier(0.4, 0, 0.2, 1), border-radius 0.4s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .site-header.is-scrolled {
            top: 24px;
            left: 50%;
            right: auto;
            width: 1040px;
            max-width: calc(100vw - 48px);
            margin-left: -520px; /* polovina 1040px pro centrování */
            margin-right: 0;
            border-radius: 9999px;
            padding: 10px 28px;
            box-shadow: 0 4px 28px rgba(0,0,0,0.3);
            background-color: var(--c-dark);
        }
        .logo { font-size: 1.35em; font-weight: 700; color: #fff; text-decoration: none; letter-spacing: -0.5px; display: block; }
        .logo:hover { opacity: 0.9; }
        .logo img { height: 42px; width: auto; display: block; transition: height 0.4s cubic-bezier(0.4, 0, 0.2, 1); }
        .site-header.is-scrolled .logo img { height: 36px; }
        .nav-links { display: flex; align-items: center; gap: 22px; flex-shrink: 0; }
        .nav-links a { color: rgba(255,255,255,0.9); text-decoration: none; font-weight: 600; font-size: 0.95em; }
        .nav-links a:hover { color: #fff; }
        .nav-links .hero-btn { background-color: var(--c-green); background-image: none; color: #111; padding: 10px 22px; font-size: 0.9em; }
        .site-header .nav-links .hero-btn:hover { background-color: #7cc936; color: #111; }
        .site-header.is-scrolled .nav-links .hero-btn { background-color: var(--c-green); background-image: none; color: #111; }

        .nav-toggle {
            display: none;
            cursor: pointer;
            color: #fff;
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.35);
            border-radius: 999px;
            padding: 6px 10px;
        }
        .nav-toggle svg {
            width: 24px;
            height: 24px;
            stroke-width: 2.4;
        }

        /* --- 11. LANDING SECTIONS (větší, výraznější, překryvy) --- */
        .section { padding: 88px 28px; max-width: 1000px; margin: 0 auto; position: relative; }
        /* První sekce pod hero – překrývá šikmý okraj, ostré rohy */
        #benefity { margin-top: -5vw; padding-top: calc(32px + 2vw); z-index: 2; background: #fff; box-shadow: 0 -10px 40px rgba(0,0,0,0.06); }
        #benefity .benefits-inner { padding: 0; }
        #benefity .section-title { margin-bottom: 12px; margin-top: 0; }
        #benefity > p { padding: 0 28px; }
        /* Druhá šikmá hrana – sekce „Proč Berounsko“ má šikmý horní okraj */
        #why { position: relative; overflow: hidden; }
        #why::before {
            content: ''; position: absolute; top: 0; left: 0; right: 0; height: 8vw; background: #f8f9fa;
            transform: skewY(-1.8deg); transform-origin: left center; z-index: -1;
        }
        .section.reveal-ready { opacity: 0; transform: translateY(24px); transition: opacity 0.6s ease-out, transform 0.6s ease-out; }
        .section.reveal-ready.is-visible { opacity: 1; transform: translateY(0); }
        .section-title { font-size: clamp(1.75rem, 3vw, 2.25rem); font-weight: 700; color: var(--c-dark); margin-bottom: 32px; text-align: center; }
        .section > p { line-height: 1.75; color: #444; margin-bottom: 20px; font-size: 1.05em; }
        .section ul { padding-left: 22px; line-height: 1.8; color: #444; }

        /* Benefity – boxy s ikonami (4 v řadě na desktopu) */
        .benefits-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 24px; margin-top: 36px; }
        .benefit-card {
            background: #fff; padding: 32px 28px; border-radius: 16px;
            box-shadow: 0 6px 24px rgba(25,46,124,0.08); border: 1px solid rgba(25,46,124,0.12);
            text-align: center; transition: transform 0.2s, box-shadow 0.2s;
        }
        .benefit-card:hover { transform: translateY(-4px); box-shadow: 0 12px 32px rgba(31,58,102,0.12); }
        .benefit-icon { width: 56px; height: 56px; margin: 0 auto 20px; display: flex; align-items: center; justify-content: center; background: rgba(104,172,249,0.12); border-radius: 14px; color: var(--c-accent); }
        .benefit-card strong { display: block; color: var(--c-dark); font-size: 1.1em; margin-bottom: 10px; }
        .benefit-card span { color: #555; font-size: 0.95em; line-height: 1.55; }

        /* Vycházky – velké karty pod sebou */
        .walks-grid { display: flex; flex-direction: column; gap: 28px; margin-top: 40px; max-width: 820px; margin-left: auto; margin-right: auto; }
        .walk-card {
            background: #fff; border-radius: 16px; overflow: hidden;
            box-shadow: 0 6px 28px rgba(0,0,0,0.08); border: 1px solid #eee;
            display: grid; grid-template-columns: 320px 1fr; min-height: 220px; transition: box-shadow 0.2s;
        }
        .walk-card:hover { box-shadow: 0 10px 36px rgba(25,46,124,0.12); }
        .walk-card-image { min-height: 220px; background-size: cover; background-position: center; }
        .walk-card-body { padding: 28px 32px; display: flex; flex-direction: column; justify-content: space-between; }
        .walk-card-title { font-size: 1.4em; font-weight: 700; color: var(--c-dark); margin-bottom: 10px; }
        .walk-card-meta { font-size: 0.95em; color: #555; margin-bottom: 12px; line-height: 1.5; }
        .walk-card-desc { font-size: 0.95em; color: #666; line-height: 1.55; margin-bottom: 20px; flex: 1; }
        .walk-card .hero-btn { align-self: flex-start; padding: 14px 28px; font-size: 1em; }

        /* FAQ rozbalovací */
        .faq-list { max-width: 700px; margin: 0 auto; }
        .faq-item { border: 1px solid #e5e5e5; border-radius: 12px; margin-bottom: 12px; overflow: hidden; background: #fff; box-shadow: 0 2px 8px rgba(0,0,0,0.04); }
        .faq-q { font-weight: 600; color: var(--c-dark); padding: 20px 24px; cursor: pointer; display: flex; justify-content: space-between; align-items: center; font-size: 1.05em; }
        .faq-q:hover { background: #fafafa; }
        .faq-q::after { content: '+'; font-size: 1.4em; color: var(--c-accent); font-weight: 400; transition: transform 0.25s; }
        .faq-item.is-open .faq-q::after { transform: rotate(45deg); }
        .faq-a { color: #555; line-height: 1.65; font-size: 0.98em; padding: 0 24px; max-height: 0; overflow: hidden; transition: max-height 0.3s ease, padding 0.3s ease; }
        .faq-item.is-open .faq-a { max-height: 280px; padding: 0 24px 20px; }

        .contact-box { background: linear-gradient(135deg, rgba(25,46,124,0.05) 0%, rgba(104,172,249,0.08) 100%); padding: 40px; border-radius: 16px; text-align: center; max-width: 520px; margin: 0 auto; border: 1px solid rgba(25,46,124,0.15); }
        .contact-box a { color: var(--c-accent); font-weight: 600; text-decoration: none; font-size: 1.1em; }
        .contact-box a:hover { text-decoration: underline; }

        /* Plynulé scrollování při kliknutí na kotvy */
        html { scroll-behavior: smooth; }
        .site-footer {
            background: var(--c-dark);
            color: rgba(255,255,255,0.85);
            margin-top: 80px;
            padding: 48px 28px 32px;
            position: relative;
        }
        .footer-inner { max-width: 1000px; margin: 0 auto; position: relative; z-index: 1; }
        .footer-top { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 32px; padding-bottom: 28px; border-bottom: 1px solid rgba(255,255,255,0.2); }
        .footer-logo { display: block; }
        .footer-logo img { height: 48px; width: auto; display: block; opacity: 1; }
        .footer-nav { display: flex; flex-wrap: wrap; gap: 8px 24px; }
        .footer-nav a { color: rgba(255,255,255,0.9); text-decoration: none; font-weight: 600; font-size: 0.95em; }
        .footer-nav a:hover { color: #fff; text-decoration: underline; }
        .footer-bottom { padding-top: 24px; text-align: center; font-size: 0.9em; color: rgba(255,255,255,0.7); }

        /* --- 12. RESPONZIVITA --- */
        @media (max-width: 900px) {
            .benefits-grid { grid-template-columns: repeat(2, 1fr); }
        }
        @media (max-width: 768px) {
            .site-header { padding: 10px 16px; }
            .site-header.is-scrolled { left: 0; right: 0; width: 340px; max-width: calc(100vw - 24px); margin-left: auto; margin-right: auto; top: 20px; padding: 10px 16px; background-color: var(--c-dark); }
            .nav-links {
                position: absolute;
                top: 100%;
                right: 0;
                background: var(--c-dark);
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
                padding: 12px 16px 16px;
                min-width: 180px;
                box-shadow: 0 8px 20px rgba(0,0,0,0.25);
                display: none;
            }
            .nav-links.is-open { display: flex; }
            .nav-links .hero-btn { width: 100%; text-align: center; }
            .nav-toggle { display: block; }

            .hero-section { min-height: 75vh; padding-top: 70px; padding-bottom: 48px; clip-path: polygon(0 0, 100% 0, 100% 98%, 0 100%); }
            #benefity { margin-top: -8vw; padding-top: calc(28px + 4vw); }
            .site-footer { padding: 32px 18px 24px; }
            .footer-logo img { height: 40px; }
            .hero-content { padding-bottom: 32px; }
            .hero-countdown-grid { width: 100%; }
            .hero-countdown-item { flex: 1; min-width: 0; }
            .nav-links { gap: 14px; font-size: 0.9em; }
            .section { padding: 56px 18px; }
            .benefits-grid { grid-template-columns: 1fr; gap: 20px; }
            .walk-card { grid-template-columns: 1fr; min-height: auto; }
            .walk-card-image { min-height: 200px; }
            .walk-card-body { padding: 22px; }
            .modal-window { flex-direction: column; width: 95%; max-height: 95vh; }
            .modal-left { height: 150px; flex: none; }
            .scroll-content { padding: 20px; }
            .fixed-footer { padding: 15px 20px; }
            .site-footer { padding: 32px 18px 24px; }
            .footer-top { flex-direction: column; text-align: center; gap: 24px; }
            .footer-nav { justify-content: center; }
            .footer-logo img { height: 40px; }
        }
    </style>
</head>
<body>

    <header class="site-header">
        <a href="#uvod" class="logo" aria-label="Berounsko.net – úvod">
            <img src="img/logo_berounsko_white.svg" alt="Berounsko.net">
        </a>
        <nav class="nav-links" id="mainNav">
            <a href="#uvod">Úvod</a>
            <a href="#benefity">Benefity</a>
            <a href="#why">Proč Berounsko</a>
            <a href="#průvodci">Průvodci</a>
            <a href="#vychazky">Vycházky</a>
            <a href="#faq">FAQ</a>
            <a href="#kontakt">Kontakt</a>
            <button class="hero-btn" onclick="openModal()" style="padding: 10px 24px; font-size: 0.9em;">Rezervovat</button>
        </nav>
        <button class="nav-toggle" type="button" aria-label="Menu" onclick="toggleNav()">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M3 6h18M3 12h18M3 18h18"/>
            </svg>
        </button>
    </header>

    <section class="hero-section" id="uvod">
        <div class="hero-bg" id="heroBg">
            <div class="hero-bg-slide active" style="background-image: url('img/cover1.jpg');"></div>
            <div class="hero-bg-slide" style="background-image: url('img/cover2.jpg');"></div>
            <div class="hero-bg-slide" style="background-image: url('img/cover3.jpg');"></div>
        </div>
        <div class="hero-overlay"></div>
        <div class="hero-content" id="heroContent" data-countdown="<?= h($countdownIso) ?>">
            <h1 class="hero-title"><?= h($hero['title']) ?></h1>
            <p class="hero-subtitle"><?= h($hero['subtitle']) ?></p>
            <?php if ($countdownIso !== ''): ?>
            <div class="hero-countdown" id="heroCountdown">
                <p class="hero-countdown-label"><?= $countdownWalkTitle !== '' ? 'Do vycházky „' . h($countdownWalkTitle) . '“ zbývá' : 'Do první komentované vycházky zbývá' ?></p>
                <div class="hero-countdown-grid">
                    <div class="hero-countdown-item"><span id="cd-days">0</span><small>dní</small></div>
                    <div class="hero-countdown-item"><span id="cd-hours">0</span><small>hodin</small></div>
                    <div class="hero-countdown-item"><span id="cd-mins">0</span><small>minut</small></div>
                    <div class="hero-countdown-item"><span id="cd-secs">0</span><small>sekund</small></div>
                </div>
            </div>
            <div class="hero-countdown-done" id="heroCountdownDone" style="display: none;">První vycházka právě začíná! Rezervujte si místo.</div>
            <?php endif; ?>
            <button class="hero-btn" onclick="openModal()">Rezervovat vycházku</button>
        </div>
    </section>

    <section class="section reveal-ready" id="benefity">
        <h2 class="section-title"><?= h($benefits['sectionTitle']) ?></h2>
        <?php if (!empty($benefits['intro'])): ?><p><?= h($benefits['intro']) ?></p><?php endif; ?>
        <div class="benefits-inner">
        <div class="benefits-grid">
            <?php
            $icons = [
                '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/><path d="M8 7h8"/><path d="M8 11h8"/></svg>',
                '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>',
                '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
                '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>',
            ];
            for ($i = 0; $i < 4; $i++): $b = $benefits['items'][$i] ?? ['title'=>'','text'=>'']; ?>
            <div class="benefit-card">
                <div class="benefit-icon" aria-hidden="true"><?= $icons[$i] ?></div>
                <strong><?= h($b['title']) ?></strong>
                <span><?= h($b['text']) ?></span>
            </div>
            <?php endfor; ?>
        </div>
        </div>
    </section>

    <section class="section reveal-ready" id="why" style="background: #f8f9fa;">
        <h2 class="section-title"><?= h($why['title']) ?></h2>
        <?php foreach ($why['paragraphs'] ?? [] as $p): ?><p><?= h($p) ?></p><?php endforeach; ?>
    </section>

    <section class="section reveal-ready" id="průvodci">
        <h2 class="section-title"><?= h($guides['title']) ?></h2>
        <?php foreach ($guides['paragraphs'] ?? [] as $p): ?><p><?= h($p) ?></p><?php endforeach; ?>
    </section>

    <section class="section reveal-ready" id="vychazky" style="background: #f8f9fa;">
        <h2 class="section-title"><?= h($walksSection['sectionTitle']) ?></h2>
        <p><?= h($walksSection['intro']) ?></p>
        <div class="walks-grid" id="walksGrid"></div>
    </section>

    <section class="section reveal-ready" id="faq">
        <h2 class="section-title"><?= h($faq['title']) ?></h2>
        <div class="faq-list">
            <?php foreach ($faq['items'] ?? [] as $f): ?>
            <div class="faq-item">
                <div class="faq-q" onclick="toggleFaq(this)"><?= h($f['q']) ?></div>
                <div class="faq-a"><?= nl2br(h($f['a'])) ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="section reveal-ready" id="kontakt" style="background: #f8f9fa;">
        <h2 class="section-title"><?= h($contact['title']) ?></h2>
        <div class="contact-box">
            <p><?= h($contact['intro']) ?></p>
            <p><a href="mailto:<?= h($contact['email']) ?>"><?= h($contact['email']) ?></a></p>
        </div>
    </section>

    <footer class="site-footer">
        <div class="footer-inner">
            <div class="footer-top">
                <a href="#uvod" class="footer-logo" aria-label="Berounsko.net – úvod">
                    <img src="img/logo_berounsko_white.svg" alt="Berounsko.net">
                </a>
                <nav class="footer-nav" aria-label="Odkazy na sekce">
                    <a href="#uvod">Úvod</a>
                    <a href="#benefity">Benefity</a>
                    <a href="#why">Proč Berounsko</a>
                    <a href="#průvodci">Průvodci</a>
                    <a href="#vychazky">Vycházky</a>
                    <a href="#faq">FAQ</a>
                    <a href="#kontakt">Kontakt</a>
                </nav>
            </div>
            <div class="footer-bottom">
                <?= h($footer['text']) ?>
            </div>
        </div>
    </footer>

    <div id="bookingModal" class="modal-overlay" onclick="if(event.target===this) closeAndReset()">
        <div class="modal-window">
            <button type="button" class="close-modal" onclick="closeAndReset()" aria-label="Zavřít">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
            
            <div class="modal-left" id="modalImage" style="background-image: url('img/cesky-kras.jpg');"></div>

            <div class="modal-right">
                
                <div id="mainFormView" class="view-wrapper">
                    
                    <div class="scroll-content">
                        <h2>Komentované vycházky</h2>

                        <label>Vyberte trasu:</label>
                        <div class="walk-selector">
                            <div class="walk-btn active" data-walk="kras" onclick="changeWalk('kras')">Český kras</div>
                            <div class="walk-btn" data-walk="svatojan" onclick="changeWalk('svatojan')">Svatojanský okruh</div>
                            <div class="walk-btn" data-walk="krivoklat" onclick="changeWalk('krivoklat')">Křivoklátsko</div>
                            <div class="walk-btn" data-walk="alkazar" onclick="changeWalk('alkazar')">Alkazar</div>
                        </div>

                        <div class="annotation-box" id="annotationContent"></div>

                        <div class="info-row">
                            <div class="info-item">
                                <span class="info-label">Průvodce:</span>
                                <span class="info-value" id="guideName">Jan Novák</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Datum:</span>
                                <span class="info-value" id="walkDate">20. 5. 2024</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Délka trasy:</span>
                                <span class="info-value" id="walkDist">8 km</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Náročnost:</span>
                                <div class="difficulty-wrapper" id="diffContainer"></div>
                            </div>
                        </div>

                        <form id="reservationForm" onsubmit="submitForm(event)">
                            <input type="hidden" name="walk_id" id="inputWalkId" value="kras">
                            <input type="hidden" name="walk_name" id="inputWalkName" value="">
                            
                            <div class="form-group">
                                <label>Váš e-mail</label>
                                <input type="email" name="email" required placeholder="jan.novak@email.cz" value="jméno@domena.cz">
                            </div>

                            <div class="form-group">
                                <label>Počet účastníků</label>
                                <select name="count" id="participantCount" onchange="calculatePrice()">
                                </select>
                            </div>
                        </form>
                    </div>

                    <div class="fixed-footer">
                        <div class="price-display" id="finalPrice">Zdarma</div>
                        <button type="submit" form="reservationForm" class="btn-submit" id="submitBtn">Zaplatit a rezervovat místo</button>
                    </div>
                </div>

                <div id="successView" class="success-message">
                    <div class="checkmark-circle"></div>
                    <div class="success-text">
                        <h3>Odesláno!</h3>
                        <p>Rezervace byla úspěšně vytvořena.</p>
                        <p>Potvrzení a platební údaje dorazí na Váš e-mail.</p>
                        <button type="button" onclick="closeAndReset()" class="btn-submit btn-secondary" style="margin-top: 30px;">Zavřít okno</button>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script>
    // DATA PROCHÁZEK (z administrace / data/content.json)
    const walks = <?= json_encode($walksForJs, JSON_UNESCAPED_UNICODE) ?>;

    // Inicializace
    const select = document.getElementById('participantCount');
    for (let i = 1; i <= 20; i++) {
        let opt = document.createElement('option');
        opt.value = i; opt.innerHTML = i; select.appendChild(opt);
    }

    // Zavření a reset
    function closeAndReset() {
        const modal = document.getElementById('bookingModal');
        modal.classList.remove('is-visible');
        setTimeout(() => {
            modal.style.display = 'none';
            
            // Resetování view
            document.getElementById('mainFormView').style.display = 'flex';
            document.getElementById('successView').style.display = 'none';
            document.getElementById('successView').classList.remove('is-active');

            document.getElementById('reservationForm').reset();
            changeWalk(document.getElementById('inputWalkId').value);
            calculatePrice();
        }, 300);
    }

    function changeWalk(id) {
        const data = walks[id];
        
        // Animace fotky
        const imgDiv = document.getElementById('modalImage');
        imgDiv.style.opacity = '0';
        setTimeout(() => {
            imgDiv.style.backgroundImage = `url('${data.img}')`;
            imgDiv.style.opacity = '1';
        }, 200);

        document.querySelectorAll('.walk-btn').forEach(b => b.classList.remove('active'));
        document.querySelector(`.walk-btn[data-walk="${id}"]`).classList.add('active');
        
        document.getElementById('guideName').innerText = data.guide;
        document.getElementById('walkDate').innerText = data.date;
        document.getElementById('walkDist').innerText = data.distance;

        // Puntíky
        const diffContainer = document.getElementById('diffContainer');
        diffContainer.innerHTML = ''; 
        let activeClass = 'active-orange';
        if (data.difficulty <= 2) activeClass = 'active-green';
        if (data.difficulty >= 5) activeClass = 'active-red';

        for(let i = 1; i <= 5; i++) {
            let dot = document.createElement('div');
            dot.className = 'diff-dot';
            if (i <= data.difficulty) dot.classList.add(activeClass);
            diffContainer.appendChild(dot);
            setTimeout(() => { dot.classList.add('animate'); }, i * 50);
        }

        document.getElementById('inputWalkId').value = id;
        document.getElementById('inputWalkName').value = data.title;
        
        // Seznam
        let htmlContent = '<ul>';
        if(data.desc.length > 0) htmlContent += `<li>${data.desc[0]}</li>`;
        if(data.desc.length > 1) {
            htmlContent += '<div id="hiddenDetails" class="hidden-content-wrapper"><ul>';
            for(let i = 1; i < data.desc.length; i++) {
                htmlContent += `<li>${data.desc[i]}</li>`;
            }
            htmlContent += '</ul></div>';
        }
        htmlContent += '</ul>';
        if(data.desc.length > 1) {
            htmlContent += `<a class="toggle-details-btn" onclick="toggleDetails()" id="toggleBtn">Zobrazit podrobnosti o trase ▼</a>`;
        }
        document.getElementById('annotationContent').innerHTML = htmlContent;

        calculatePrice();
    }

    function toggleDetails() {
        const hiddenDiv = document.getElementById('hiddenDetails');
        const btn = document.getElementById('toggleBtn');
        if (hiddenDiv.classList.contains('is-open')) {
            hiddenDiv.classList.remove('is-open');
            btn.innerText = 'Zobrazit podrobnosti o trase ▼';
        } else {
            hiddenDiv.classList.add('is-open');
            btn.innerText = 'Skrýt podrobnosti ▲';
        }
    }

    function calculatePrice() {
        const id = document.getElementById('inputWalkId').value;
        const count = parseInt(document.getElementById('participantCount').value);
        const pricePerPerson = walks[id].pricePerPerson;
        let total = count * pricePerPerson;
        
        if (total === 0) {
            document.getElementById('finalPrice').innerText = "Zdarma";
            document.getElementById('submitBtn').innerText = "Rezervovat zdarma";
        } else {
            document.getElementById('finalPrice').innerText = total + " Kč";
            document.getElementById('submitBtn').innerText = "Zaplatit (" + total + " Kč)";
        }
    }

    // ODESLÁNÍ
    function submitForm(e) {
        e.preventDefault();
        const btn = document.getElementById('submitBtn');
        const originalText = btn.innerText;
        btn.disabled = true;
        btn.innerText = "Odesílám...";

        const formData = new FormData(document.getElementById('reservationForm'));

        fetch('rezervace.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                // Přepnutí pohledu
                document.getElementById('mainFormView').style.display = 'none';
                
                const successView = document.getElementById('successView');
                successView.style.display = 'flex';
                void successView.offsetWidth; // Force Reflow
                successView.classList.add('is-active');

            } else {
                alert("Chyba: " + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert("Chyba komunikace.");
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerText = originalText;
        });
    }

    // Otevření modalu – volitelně s předvybranou vycházkou (id z walks)
    function openModal(walkId) {
        const modal = document.getElementById('bookingModal');
        modal.style.display = 'flex';
        setTimeout(() => { modal.classList.add('is-visible'); }, 10);
        if (walkId && walks[walkId]) {
            changeWalk(walkId);
        } else {
            const currentId = document.getElementById('inputWalkId').value;
            const imgUrl = walks[currentId].img;
            const imgDiv = document.getElementById('modalImage');
            setTimeout(() => { imgDiv.style.backgroundImage = `url('${imgUrl}')`; }, 50);
        }
    }

    // Vygenerování karet vycházek na landing page (velké karty pod sebou, víc informací)
    function renderWalkCards() {
        const grid = document.getElementById('walksGrid');
        if (!grid) return;
        const ids = ['kras', 'svatojan', 'krivoklat', 'alkazar'];
        grid.innerHTML = ids.map(id => {
            const w = walks[id];
            const priceText = w.pricePerPerson === 0 ? 'Zdarma' : w.pricePerPerson + ' Kč / osoba';
            const firstLine = w.desc && w.desc[0] ? w.desc[0] : '';
            return `
                <div class="walk-card">
                    <div class="walk-card-image" style="background-image: url('${w.img}');"></div>
                    <div class="walk-card-body">
                        <div class="walk-card-title">${w.title}</div>
                        <div class="walk-card-meta">${w.date} · ${w.distance} · ${priceText}<br>Průvodce: ${w.guide}</div>
                        ${firstLine ? `<div class="walk-card-desc">${firstLine}</div>` : ''}
                        <button class="hero-btn" type="button" onclick="openModal('${id}')">Rezervovat místo</button>
                    </div>
                </div>
            `;
        }).join('');
    }

    // FAQ rozbalení / sbalení
    function toggleFaq(el) {
        const item = el.closest('.faq-item');
        item.classList.toggle('is-open');
    }

    // Mobilní menu
    function toggleNav() {
        const nav = document.getElementById('mainNav');
        if (!nav) return;
        nav.classList.toggle('is-open');
    }

    // Rotace hero pozadí (cover1–3)
    function startHeroSlideshow() {
        const container = document.getElementById('heroBg');
        if (!container) return;
        const slides = container.querySelectorAll('.hero-bg-slide');
        if (slides.length === 0) return;
        let idx = 0;
        setInterval(function() {
            slides[idx].classList.remove('active');
            idx = (idx + 1) % slides.length;
            slides[idx].classList.add('active');
        }, 5500);
    }

    // Odpočet do první vycházky
    function startCountdown() {
        const el = document.getElementById('heroContent');
        const targetStr = el && el.getAttribute('data-countdown');
        if (!targetStr) return;
        const target = new Date(targetStr.replace(' ', 'T'));
        const block = document.getElementById('heroCountdown');
        const done = document.getElementById('heroCountdownDone');
        const pad = (n) => String(Math.max(0, Math.floor(n))).padStart(2, '0');

        function tick() {
            const now = new Date();
            const diff = target - now;
            if (diff <= 0) {
                if (block) block.style.display = 'none';
                if (done) done.style.display = 'block';
                return;
            }
            const d = diff / (24*60*60*1000);
            const h = (d % 1) * 24;
            const m = (h % 1) * 60;
            const s = (m % 1) * 60;
            const daysEl = document.getElementById('cd-days');
            const hoursEl = document.getElementById('cd-hours');
            const minsEl = document.getElementById('cd-mins');
            const secsEl = document.getElementById('cd-secs');
            if (daysEl) daysEl.textContent = Math.floor(d);
            if (hoursEl) hoursEl.textContent = pad(h);
            if (minsEl) minsEl.textContent = pad(m);
            if (secsEl) secsEl.textContent = pad(s);
        }
        tick();
        setInterval(tick, 1000);
    }

    // Navigace: při scrollu přechod do pill tvaru
    function initHeaderScroll() {
        var header = document.querySelector('.site-header');
        if (!header) return;
        var threshold = 80;
        function update() {
            if (window.scrollY > threshold) header.classList.add('is-scrolled');
            else header.classList.remove('is-scrolled');
        }
        window.addEventListener('scroll', function() { requestAnimationFrame(update); }, { passive: true });
        update();
    }

    // Scroll reveal – sekce se jemně objeví při scrollu
    function initScrollReveal() {
        var sections = document.querySelectorAll('.section.reveal-ready');
        if (!sections.length) return;
        var io = new IntersectionObserver(function(entries) {
            entries.forEach(function(e) {
                if (e.isIntersecting) e.target.classList.add('is-visible');
            });
        }, { rootMargin: '-6% 0px -6% 0px', threshold: 0 });
        sections.forEach(function(s) { io.observe(s); });
    }

    // Init
    changeWalk('kras');
    renderWalkCards();
    startHeroSlideshow();
    startCountdown();
    initHeaderScroll();
    initScrollReveal();
    </script>
</body>
</html>