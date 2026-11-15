---
sidebar_position: 2
---

# Erste Schritte

## Anforderungen

- Evolution CMS **^3.5**. Die Mindestversion kann von der durch Composer gewählten EvoUI-Version abhängen.
- PHP **^8.4**
- Composer **2.2+**
- Eine der folgenden Datenbanken: **MySQL 8.0+** / **MariaDB 10.5+** / **PostgreSQL 10+** / **SQLite 3.25+**

## Installation über den artisan package installer

Wechseln Sie in den `core`-Ordner Ihrer Evolution CMS Installation:

```console
cd path/to/your/evolution/cms/core
```

Führen Sie den artisan-Befehl aus:

Sobald der stabile 2.x-Release auf Packagist verfügbar ist, installiert ihn der folgende Befehl.

```console
php artisan package:installrequire seiger/slang "^2.0"
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

![Ressourcen-Editor mit ausgewähltem ukrainischen Tab](../assets/images/slang-resource-editor.png)

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

> **Deprecated:** `langAndTvs()` bleibt für bestehenden Code verfügbar. Verwenden Sie für neue Abfragen `lang()` und `withTVs()`.
