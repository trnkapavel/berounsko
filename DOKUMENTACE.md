# 📖 Technická Dokumentace - Berounsko.net

Podrobná dokumentace pro vývojáře a správce aplikace.

## 1. Architektura

### Frontend (index.php)

Jedná se o HTML stránku s vloženými CSS styly a JavaScript kódem. Nepoužívá se zde žádný build process.

**CSS Struktura:**
- CSS proměnné (--c-blue, --c-green, --c-orange, --c-red) pro konzistentní barvy
- CSS Grid pro responzivní layout
- Flexbox pro modální okno a fixed-footer
- CSS animace: `fadeIn`, `slideInUp`, `popIn` pro přechody a puntíky náročnosti
- CSS transitions pro modal overlay, okno, obrázek trasy a rozbalování detailů

**JavaScript:**
- Vanilla JS bez závislostí (jQuery není potřeba)
- Datová struktura `walks` obsahující všechny informace o trasách
- Fetch API pro komunikaci s backendem
- Funkce: `openModal()`, `closeAndReset()`, `changeWalk()`, `toggleDetails()`, `calculatePrice()`, `submitForm()`

**Objem kódu:**
- index.php: ~560 řádků (CSS + HTML + JS)

### Backend (rezervace.php)

PHP skript, který:
1. Přijímá POST data z formuláře
2. Zpracovává rezervaci
3. Generuje QR kód (SPAYD)
4. Sestaví `.ics` soubor pro kalendářní pozvánku
5. Odesílá multipart e-mail klientovi (HTML + .ics příloha)
6. Odesílá e-mail správci (HTML)
7. Synchronizuje s Google Sheets
8. Vrací JSON odpověď

**Tok zpracování:**
```
POST data
  ├─ Validace (email, počet osob)
  ├─ Načtení dat trasy z $walksData
  ├─ Výpočet ceny
  ├─ Generace QR kódu (SPAYD)
  ├─ Sestavení .ics souboru (iCal)
  ├─ cURL POST do Google Sheets
  ├─ Multipart mail pro klienta (HTML + .ics příloha)
  ├─ Mail pro admina (HTML)
  └─ JSON response
```

### Data Storage

**lokální:** `rezervace_data.json`
- Backup rezervací v JSON formátu
- Struktura:
```json
{
  "date": "2026-02-14 09:18:35",
  "walk": "Název vycházky",
  "email": "user@example.com",
  "count": 3,
  "total_price": 300
}
```

**Cloud:** Google Sheets
- Primární úložiště pro lepší dostupnost
- Realtime synchronizace

## 2. Detailní Popis Souborů

### index.php

#### CSS Sekce

**Barvové schéma:**
```css
--c-blue:    #30B0FF   /* Primární barva */
--c-green:   #80C024   /* Úspěch, lehkost */
--c-orange:  #FF9200   /* Varování, střední náročnost */
--c-red:     #FF4444   /* Těžkost */
--c-text:    #000000   /* Text */
```

**Modální okno (.modal-window):**
- Flexbox layout pro desktop (vlevo obrázek, vpravo formulář)
- Na mobilu se změní na column (stack)
- Animované otevření/zavření přes CSS třídy `is-visible`
- `backdrop-filter: blur(5px)` pro efekt rozmazaného pozadí

**Komponenty:**
- `.scroll-content` - Scrollovatelná oblast formuláře (flex: 1)
- `.fixed-footer` - Vždy viditelná patička s cenou a tlačítkem
- `.walk-btn` - Tlačítka pro výběr tras
- `.info-row` - Řádek s informacemi (průvodce, datum, délka, náročnost)
- `.difficulty-wrapper` - Indikátor náročnosti (puntíky s animací `popIn`)
- `.hidden-content-wrapper` - Rozbalitelný obsah (CSS max-height transition)
- `.form-group` - Skupiny formuláře

#### JavaScript Sekce

**Datová struktura `walks`:**
```javascript
{
  'kras': {
    title: string,           // Název pro zobrazení
    img: string,             // Cesta k obrázku (z img/ složky)
    guide: string,           // Jméno průvodce
    date: string,            // Datum vycházky
    distance: string,        // Délka trasy
    difficulty: number(1-5), // Náročnost
    pricePerPerson: number,  // Cena v Kč
    desc: string[]           // Pole opisů (první vždy viditelný, zbytek rozbalitelný)
  }
}
```

**Klíčové funkce:**

| Funkce | Parametry | Popis |
|--------|-----------|-------|
| `openModal()` | - | Zobrazí modal s CSS animací |
| `closeAndReset()` | - | Zavře modal s animací a resetuje stav |
| `changeWalk(id)` | id: string | Aktualizuje UI, obrázek (fade), puntíky, popis |
| `toggleDetails()` | - | Rozbalí/sbalí skryté body popisu |
| `calculatePrice()` | - | Spočítá a zobrazí cenu v patičce |
| `submitForm(e)` | e: Event | Odešle POST na `rezervace.php`, zobrazí success view |

**Vykreslování puntíků náročnosti:**
```javascript
// Barvy podle stupně, puntíky se animují postupně (stagger 50ms)
difficulty 1-2: .active-green
difficulty 3-4: .active-orange
difficulty 5:   .active-red
```

#### HTML Sekce

**Struktura modálního okna:**
```
modal-overlay (backdrop, is-visible pro animaci)
  └─ modal-window
      ├─ modal-left (obrázek trasy, fade transition)
      └─ modal-right
          └─ mainFormView (view-wrapper)
              ├─ scroll-content
              │   ├─ walk-selector (tlačítka tras)
              │   ├─ annotation-box (popis + toggle)
              │   ├─ info-row (průvodce, datum, délka, náročnost)
              │   └─ reservationForm
              └─ fixed-footer (cena + submit tlačítko)
          └─ successView (potvrzení s animací)
```

**Formulář:**
- Hidden pole: `walk_id`, `walk_name`
- E-mail input (povinný)
- Select pro počet osob (1-20)
- Submit tlačítko mimo `<form>` s atributem `form="reservationForm"` (umožňuje umístění mimo form tag)

### rezervace.php

#### Konfigurační Sekce

```php
$adminEmail = "..."         // Kde dorazí notifikace
$rawIban = "..."            // IBAN pro QR platby
$googleScriptUrl = "..."    // URL skriptu pro Sheets
```

#### Data tras ($walksData)

Centrální pole s daty pro všechny vycházky. Slouží jako zdroj pravdy pro e-maily a kalendářní pozvánku:

```php
$walksData = [
    'kras' => [
        'name'     => 'Okruh Srbsko, Chlum',
        'date_txt' => '18. 4. 2026',         // Text do e-mailu
        'start'    => '20260418T100000',      // Začátek pro .ics (YYYYMMDDThhmmss)
        'end'      => '20260418T140000',      // Konec pro .ics
        'location' => 'Srbsko, Česká republika',
        'guide'    => 'Martin Majer, Jan Holeček',
        'img'      => 'https://...'           // Obrázek v HTML e-mailu
    ],
    // ... další trasy
]
```

#### Funkční Sekce

**1. Validace vstupů**
```php
if (!$email || !$count)
    return error response
```

**2. Generace QR kódu**
- Formát: SPAYD (SPD*1.0*)
- API: qrserver.com
- Vrací HTML img tag s QR kódem

**3. Sestavení .ics souboru (iCal)**
- Formát: RFC 5545 (VCALENDAR/VEVENT)
- Obsahuje: název, čas, lokaci, jméno průvodce
- Odeslán jako Base64 příloha e-mailu

**4. Odesílání do Google Sheets**
- cURL POST request
- Timeout: 5 sekund

**5. Multipart e-mail klientovi**
- `Content-Type: multipart/mixed`
- Část 1: HTML e-mail s obrázkem, detaily, QR kódem, jménem průvodce a datem
- Část 2: Base64 příloha `pozvanka.ics`

**6. E-mail správci**
- Jednoduché HTML s detaily rezervace
- `Reply-To` nastaven na e-mail klienta

**7. JSON Odpověď**
```php
{
  "success": bool,
  "message": "..."  // Error message if not success
}
```

## 3. Databáze a Datové Toky

### Přijaté Data

```
POST /rezervace.php
{
  "walk_id": "kras|svatojan|krivoklat|alkazar",
  "walk_name": "Název trasy",
  "email": "user@example.com",
  "count": 1-20
}
```

### Vypočítaná Data

```javascript
{
  "walk_id": string,
  "walk_name": string,
  "email": string,
  "count": number,
  "pricePerPerson": number,
  "totalPrice": number,
  "date_time": "Y-m-d H:i:s",
  "qrUrl": "https://qrserver.com/...",
  "currentImage": "https://images.unsplash.com/..."
}
```

### Odeslaná Data (Google Sheets)

```json
{
  "date": "2026-02-14 09:18:35",
  "walk": "Svatojanský okruh",
  "email": "user@example.com",
  "count": 3,
  "price": 300,
  "qr_link": "https://api.qrserver.com/..."
}
```

## 4. Bezpečnost

### Potřebná Opatření

**K-Výštění:**
```php
// Validace e-mailu
filter_var($email, FILTER_VALIDATE_EMAIL)

// Sanitizace vstupů
htmlspecialchars()
strip_tags()

// Limitování počtu osob
$count = min($count, 100);
```

**CSRF ochrana:**
- Aktuálně není implementována
- Doporučuje se přidat token do formuláře

**Rate limiting:**
- Není implementován
- Na produkci by bylo vhodné přidat

**HTTPS:**
- Povinné pro produkci
- Zajistit redirect http → https

### Citlivé Údaje

- Admin e-mail - v `$adminEmail`
- IBAN - v `$rawIban`
- Google API klíč - v `$googleScriptUrl`

**Doporučení:**
- Nepřidávat do Gitu (použít `.env`)
- Chránit webhosty přístupová práva
- Rotovat klíče pravidelně

## 5. Integrace

### Google Sheets API

1. Vytvoř Google Apps Script project
2. Vytvoř funkcí, která přijímá POST data:

```javascript
function doPost(e) {
  var data = JSON.parse(e.postData.contents);
  // Zpracuj data a ulož do Sheets
  return ContentService.createTextOutput("OK");
}
```

3. Publikuj jako Web App (Execute as Me)
4. Kopíruj URL do `$googleScriptUrl`

### QR Platby (SPAYD)

Format: `SPD*1.0*ACC:{IBAN}*AM:{amount}.00*CC:{currency}*MSG:{message}`

Příklad:
```
SPD*1.0*ACC:CZ15303000000011469280017*AM:300.00*CC:CZK*MSG:Vychazka Svatojan
```

API: https://api.qrserver.com/v1/create-qr-code

## 6. Běžné Chyby a Řešení

### E-maily se neposílají

**Příčiny:**
1. PHP mail() není konfigurován
2. Sendmail není nainstalován
3. Brána блокuje e-maily

**Řešení:**
```php
// Kontrola
if(mail($to, $subject, $message)) {
    echo "Email sent";
} else {
    echo "Email failed";
    // Zkontroluj error_log
}
```

### Google Sheets se nenaplňuje

**Příčiny:**
1. URL skriptu je nesprávná
2. Google Apps Script vrací chybu
3. cURL není nainstalován

**Řešení:**
```php
// Debug cURL
curl_setopt($ch, CURLOPT_VERBOSE, true);
// Zobraz odpověď
$response = curl_exec($ch);
echo curl_error($ch);
```

### QR kód se nezobrazuje

**Příčiny:**
1. qrserver.com API je nedostupný
2. Proxy/firewall blokuje
3. IBAN je nevalidní

**Řešení:**
- Zkontroluj IBAN formát
- Zkontroluj internet konekcí
- Použij vlastní QR generator

## 7. Nasazení

### Vývojové Prostředí

```bash
# PHP built-in server
php -S localhost:8000

# Nebo Apache/Nginx lokálně
```

### Produkční Nasazení

1. **Web Hosting:**
   - PHP 7.0+
   - E-mail (Sendmail/Postfix)
   - HTTPS

2. **Deployment:**
```bash
git clone ...
cd berounsko
# Konfiguruj údaje v rezervace.php
# Nastav oprávnění na soubory
chmod 644 index.php rezervace.php
```

3. **Monitoring:**
   - Sleduj e-maily (dorazí všechny?)
   - Zkontroluj Google Sheets (nabírají se data?)
   - Otestuj formulář (jde zarezervovat?)

## 8. Budoucí Vylepšení

- [ ] Přepis na PHP framework (Laravel/Symfony)
- [ ] Databáze MySQL
- [ ] Admin panel pro správu tras
- [ ] Platební brána (Stripe/Square)
- [ ] Uživatelské konta
- [ ] Více jazyků (en, de)
- [ ] SMS notifikace
- [x] Integrace s kalendářem (.ics příloha v e-mailu)

---

**Verze:** 1.1
**Poslední úprava:** 23. února 2026
