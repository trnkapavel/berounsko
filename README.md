# 🥾 Berounsko.net - Rezervační Systém Vycházek

Interaktivní webová aplikace pro rezervaci komentovaných vycházek v oblasti Berounska. Uživatelé si mohou vybrat z nabízených tras, zadat počet účastníků a provést online rezervaci s automatickým odesláním potvrzovacího e-mailu.

## 🎯 Hlavní Funkce

- **Interaktivní výběr tras** - 4 různé vycházky s detailním popisem a animovaným rozbalováním
- **Rezervační formulář** - Jednoduchý formulář s výpočtem ceny v reálném čase
- **Online platba** - QR kód pro snadnou platbu převodem (SPAYD formát)
- **Automatické e-maily** - Potvrzovací zprávy pro uživatele i správce s `.ics` přílohou (kalendářní pozvánka)
- **Google Sheets integrace** - Ukládání rezervací do tabulky
- **SEO & Open Graph** - Meta tagy pro vyhledávače, Facebook, WhatsApp a Twitter
- **Responzivní design** - Funguje na mobilu, tabletu i desktopu
- **Animace** - Plynulé přechody modalu, puntíků náročnosti i obrázků tras

## 📷 Náhled Aplikace

![Berounsko.net Screenshot](screenshot/screenshot.png)

## 📋 Dostupné Vycházky

| Trasa | Délka | Cena | Náročnost |
|-------|-------|------|-----------|
| Okruh Srbsko, Chlum | 4 km | Zdarma | 🔴 Velmi těžká |
| Svatojanský okruh | 4 km | 100 Kč | 🔴 Těžká |
| Brdatka (Křivoklátsko) | 9,5 km | 100 Kč | 🟠 Střední |
| Alkazar | 4 km | 100 Kč | 🟢 Velmi lehká |

## 🛠️ Technologie

- **Frontend**: HTML5, CSS3, JavaScript (Vanilla)
- **Backend**: PHP 7.0+
- **Data**: JSON (rezervace_data.json)
- **Integrace**: Google Sheets API, SPAYD formát pro QR platby, iCal (.ics) pro kalendáře

## 📁 Struktura Projektu

```
berounsko/
├── index.php              # Hlavní stránka s modálním oknem
├── rezervace.php          # Backend pro zpracování rezervací
├── img/                   # Fotografie tras
│   ├── srbsko-chlum.jpg
│   ├── svatojansky-okruh.jpg
│   ├── brdatka.jpg
│   └── alkazar.jpg
├── screenshot/            # Screenshoty aplikace
│   └── screenshot.png     # Náhled aplikace
├── README.md              # Tato dokumentace
├── DOKUMENTACE.md         # Technická dokumentace
└── .gitignore             # Git ignore pravidla
```

## 🚀 Instalace a Spuštění

### Požadavky
- PHP 7.0 nebo vyšší
- Webový server (Apache, Nginx, MAMP, XAMPP)
- E-mail (pro odesílání potvrzení)

### Lokální spuštění

1. **Klonování repozitáře**
```bash
git clone https://github.com/username/berounsko.git
cd berounsko
```

2. **Spuštění na localhostu (PHP built-in server)**
```bash
php -S localhost:8000
```

3. **Otevření v prohlížeči**
```
http://localhost:8000
```

### Nasazení na hosting

1. Nahrajte soubory na web server (přes FTP/SFTP)
2. Upravte e-mail v `rezervace.php` (řádek 5)
3. Upravte Google Apps Script URL (řádek 9)
4. Otestujte formulář

## ⚙️ Konfigurace

### Hlavní nastavení (rezervace.php)

```php
// Řádek 5 - E-mail správce
$adminEmail = "tvuj@email.com";

// Řádek 7-8 - IBAN pro platby
$rawIban = "CZ15 3030 0000 0011 4692 8017";

// Řádek 11 - Google Sheets integrace
$googleScriptUrl = "https://script.google.com/macros/s/YOUR_SCRIPT_ID/exec";
```

### Ceny tras

V souboru `index.php` (ve struktuře `walks`) lze nastavit cenu pro každou trasu:

```javascript
'kras': {
    pricePerPerson: 0,      // Zdarma
    // ...
},
'svatojan': {
    pricePerPerson: 100,    // 100 Kč na osobu
    // ...
}
```

### Data tras (rezervace.php)

Data, průvodci, lokace a časy pro kalendářní pozvánku se nastavují v poli `$walksData`:

```php
'kras' => [
    'name'     => 'Okruh Srbsko, Chlum',
    'date_txt' => '18. 4. 2026',
    'start'    => '20260418T100000',   // Pro .ics soubor
    'end'      => '20260418T140000',
    'location' => 'Srbsko, Česká republika',
    'guide'    => 'Martin Majer, Jan Holeček',
    'img'      => 'https://...'        // Obrázek pro e-mail
]
```

## 💳 Platební Systém

Aplikace generuje QR kódy ve formátu **SPAYD** (iniciativa ČNB pro standardizované platby).

- Uživatelé si nasnímají QR kód telefonem
- Bankovní aplikace se otevře s vyplněnou částkou
- Po potvrzení je rezervace platná

## 📧 E-maily

### Pro uživatele
- Potvrzení rezervace
- Platební údaje (QR kód + IBAN)
- Datum, čas a jméno průvodce
- **Příloha `.ics`** – kalendářní pozvánka pro Google Calendar, Apple Calendar, Outlook

### Pro správce
- Nová objednávka
- Kontakt na uživatele
- Informace o trase a platbě

## 🔄 Integrace s Google Sheets

Všechny rezervace se automaticky přidávají do Google Sheets tabulky:

1. Vytvoř Google Apps Script
2. Nastav jej na příjímání POST dat
3. Vlož URL do `$googleScriptUrl` v `rezervace.php`

Struktura dat:
```json
{
  "date": "2026-04-18 10:00:00",
  "walk": "Okruh Srbsko, Chlum",
  "email": "user@example.com",
  "count": 3,
  "price": 0,
  "qr_link": ""
}
```

## 🐛 Řešení Problémů

### E-maily se neposílají
- Zkontroluj e-mail správce v `rezervace.php`
- Zkontroluj, že server má povoleno odesílání e-mailů (PHP mail())

### QR kód se nezobrazuje
- QR kódy se generují přes `qrserver.com` API
- Potřebuje internetové připojení
- Zkontroluj IBAN na správný formát

### Google Sheets se nenaplňuje
- Zkontroluj URL v `$googleScriptUrl`
- Google Apps Script musí být publikován jako web app

## 📱 Responzivita

Aplikace je optimalizována pro:
- 📱 Mobilní zařízení (320px+)
- 📊 Tablety (768px+)
- 🖥️ Desktopy (1024px+)

## 👨‍💻 Vývoj

Projekt je napsán bez velkých frameworků pro snadnou údržbu a minimální závislosti.

### Schéma toku dat

```
Uživatel vyplní formulář
      ↓
JavaScript ověří a odešle
      ↓
POST request na rezervace.php
      ↓
PHP zpracuje data
      ├→ Generuje QR kód (SPAYD)
      ├→ Sestaví .ics soubor (iCal)
      ├→ Posílá do Google Sheets
      ├→ Odesílá e-mail uživateli (HTML + .ics příloha)
      ├→ Odesílá e-mail správci (HTML)
      └→ Vrací JSON odpověď
      ↓
JavaScript zobrazí potvrzení s animací
```

## 📝 Licence

MIT License - viz LICENSE soubor

## 👋 Kontakt a Podpora

- **Web**: https://www.berounsko.net
- **E-mail**: info@berounsko.net

---

**Poslední aktualizace**: 23. února 2026
