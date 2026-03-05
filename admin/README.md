# Administrace obsahu Berounsko

Všechny texty, vycházky, FAQ a kontakt se upravují v administraci a ukládají do `data/content.json`.

## Přihlášení

1. **První spuštění:** Pokud ještě nemáte soubor `admin/local.php`:
   - Zkopírujte `admin/local.php.example` jako `admin/local.php`.
   - Výchozí heslo je **admin** (nebo si vygenerujte vlastní hash, viz níže).

2. Otevřete v prohlížeči: **https://vase-domena.cz/admin/**

3. Po přihlášení uvidíte formuláře pro úpravu:
   - Hero (nadpis, podtitul)
   - Benefity (4 boxy)
   - Proč Berounsko, Kdo vás provede
   - Vycházky (4 trasy – název, obrázek, průvodce, datum, cena, popis, údaje pro e-mail/ICS)
   - FAQ (4 dvojice otázka/odpověď)
   - Kontakt, Patička
   - Nastavení (admin e-mail pro oznámení o rezervacích, IBAN)

4. **Změna hesla:** V `admin/local.php` nastavte vlastní hash:
   ```php
   <?php
   // Vygenerujte na serveru: php -r "echo password_hash('VaseHeslo', PASSWORD_DEFAULT);"
   define('ADMIN_PASSWORD_HASH', 'váš-vygenerovaný-hash');
   ```

## Oprávnění

- Složka `data/` musí být zapisovatelná webovým serverem (aby šlo ukládat `content.json`).
- Soubor `admin/local.php` nedávejte do Gitu (je v `.gitignore`).
