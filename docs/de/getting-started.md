# Erste Schritte

## Anforderungen

- Evolution CMS **3.3+**
- PHP **8.3+**
- Composer **2.2+**
- Eine der folgenden Datenbanken: **MySQL 8.0+** / **MariaDB 10.5+** / **PostgreSQL 10+** / **SQLite 3.25+**

## Installation über den artisan package installer

Wechseln Sie in den `core`-Ordner Ihrer Evolution CMS Installation:

```console
cd path/to/your/evolution/cms/core
```

Führen Sie den artisan-Befehl aus:

```console
php artisan package:installrequire seiger/slang "*"
```

Veröffentlichen Sie die Paketdateien:

```console
php artisan vendor:publish --provider="Seiger\sLang\sLangServiceProvider"
```

Erstellen Sie die Datenbankstruktur:

```console
php artisan migrate
```

## Verwaltung

Nach der Installation kann das Modul sofort verwendet werden. Pfad im Admin-Panel: **Admin Panel -> Modules -> Multilingual**.

Die Ressource enthält separate Tabs für jede Sprache.

![Multilingual tabs](https://github.com/Seiger/slang/releases/download/v1.0.0/sLang.png)

[Verwaltungs-Tabs](management-tabs.md)

## Zusätzlich

Wenn eigener Code mit sLang integriert wird, kann die Verfügbarkeit des Moduls über eine Konfigurationsvariable geprüft werden.

```php
if (evo()->getConfig('check_sLang', false)) {
    // Your code
}
```

Wenn das Plugin installiert ist, gibt `evo()->getConfig('check_sLang', false)` immer `true` zurück. Andernfalls wird `false` zurückgegeben.

## Arbeiten mit lokalisierten Inhalten

Verwenden Sie das Modell `sLangContent`, um übersetzte Ressourcen zu laden:

```php
use Seiger\sLang\Models\sLangContent;

$items = sLangContent::active()->get(); // locale resolved automatically
$itemsEn = sLangContent::lang('en')->withTVs(['preview'])->get();
```

> **Deprecated:** Der Scope `langAndTvs()` ist seit `1.0.8` veraltet und wird in `v1.2` entfernt. Verwenden Sie `lang()` und `withTVs()`.
