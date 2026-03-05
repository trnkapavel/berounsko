<?php
session_start();
require_once __DIR__ . '/config.php';

if (!admin_logged_in()) {
    http_response_code(403);
    exit('Přihlaste se.');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$data = load_content();

// Hero
if (!empty($_POST['hero'])) {
    $p = $_POST['hero'];
    $data['hero'] = [
        'title' => trim($p['title'] ?? ''),
        'subtitle' => trim($p['subtitle'] ?? ''),
    ];
}

// Benefits
if (!empty($_POST['benefits'])) {
    $p = $_POST['benefits'];
    $items = [];
    foreach ($p['items'] ?? [] as $i) {
        $items[] = ['title' => trim($i['title'] ?? ''), 'text' => trim($i['text'] ?? '')];
    }
    $data['benefits'] = [
        'sectionTitle' => trim($p['sectionTitle'] ?? ''),
        'intro' => trim($p['intro'] ?? ''),
        'items' => array_slice($items, 0, 4),
    ];
}

// Why Berounsko
if (!empty($_POST['whyBerounsko'])) {
    $p = $_POST['whyBerounsko'];
    $par = array_filter(array_map('trim', $p['paragraphs'] ?? []));
    $data['whyBerounsko'] = [
        'title' => trim($p['title'] ?? ''),
        'paragraphs' => array_values($par),
    ];
}

// Guides
if (!empty($_POST['guides'])) {
    $p = $_POST['guides'];
    $par = array_filter(array_map('trim', $p['paragraphs'] ?? []));
    $data['guides'] = [
        'title' => trim($p['title'] ?? ''),
        'paragraphs' => array_values($par),
    ];
}

// Walks
if (!empty($_POST['walks'])) {
    $p = $_POST['walks'];
    $data['walks'] = [
        'sectionTitle' => trim($p['sectionTitle'] ?? ''),
        'intro' => trim($p['intro'] ?? ''),
        'items' => [],
    ];
    foreach ($p['items'] ?? [] as $id => $w) {
        $descText = trim($w['desc_text'] ?? '');
        $desc = $descText === '' ? [] : array_values(array_filter(array_map('trim', explode("\n", $descText))));
        $data['walks']['items'][$id] = [
            'title' => trim($w['title'] ?? ''),
            'img' => trim($w['img'] ?? ''),
            'guide' => trim($w['guide'] ?? ''),
            'date' => trim($w['date'] ?? ''),
            'distance' => trim($w['distance'] ?? ''),
            'difficulty' => max(1, min(5, (int)($w['difficulty'] ?? 3))),
            'pricePerPerson' => max(0, (int)($w['pricePerPerson'] ?? 0)),
            'start' => trim($w['start'] ?? ''),
            'end' => trim($w['end'] ?? ''),
            'location' => trim($w['location'] ?? ''),
            'desc' => $desc,
        ];
    }
}

// FAQ
if (!empty($_POST['faq'])) {
    $p = $_POST['faq'];
    $items = [];
    foreach ($p['items'] ?? [] as $i) {
        $items[] = ['q' => trim($i['q'] ?? ''), 'a' => trim($i['a'] ?? '')];
    }
    $data['faq'] = [
        'title' => trim($p['title'] ?? ''),
        'items' => array_slice($items, 0, 4),
    ];
}

// Contact
if (!empty($_POST['contact'])) {
    $p = $_POST['contact'];
    $data['contact'] = [
        'title' => trim($p['title'] ?? ''),
        'intro' => trim($p['intro'] ?? ''),
        'email' => trim($p['email'] ?? ''),
    ];
}

// Footer
if (!empty($_POST['footer'])) {
    $data['footer'] = ['text' => trim($_POST['footer']['text'] ?? '')];
}

// Settings
if (!empty($_POST['settings'])) {
    $p = $_POST['settings'];
    $data['settings'] = [
        'adminEmail' => trim($p['adminEmail'] ?? ''),
        'iban' => trim($p['iban'] ?? ''),
    ];
}

if (save_content($data)) {
    header('Location: index.php?saved=1');
} else {
    http_response_code(500);
    echo 'Obsah se nepodařilo uložit. Zkontrolujte oprávnění složky data/.';
}
exit;
