---
sidebar_position: 2
---

# Pierwsze kroki

## Wymagania

- Evolution CMS **^3.5**. Minimalna wersja może zależeć od wersji EvoUI wybranej przez Composer.
- PHP **^8.4**
- Composer **2.2+**
- Jedna z baz danych: **MySQL 8.0+** / **MariaDB 10.5+** / **PostgreSQL 10+** / **SQLite 3.25+**

## Instalacja przez artisan package installer

Przejdź do folderu `core` swojej instalacji Evolution CMS:

```console
cd path/to/your/evolution/cms/core
```

Uruchom komendę artisan:

Po opublikowaniu stabilnego wydania 2.x w Packagist poniższe polecenie je zainstaluje.

```console
php artisan package:installrequire seiger/slang "^2.0"
```

Opublikuj pliki pakietu:

```console
php artisan vendor:publish --provider="Seiger\sLang\sLangServiceProvider"
```

Utwórz strukturę bazy danych:

```console
php artisan migrate
```

## Zarządzanie

Po instalacji moduł jest od razu gotowy do użycia. Ścieżka w panelu administracyjnym: **Admin Panel -> Modules -> Multilingual**.

Zasób zawiera osobne zakładki dla każdego języka.

![Edytor zasobu z otwartą zakładką ukraińską](../assets/images/slang-resource-editor.png)

[Zakładki zarządzania](management-tabs.md)

## Dodatkowo

Jeśli własny kod integruje się z sLang, sprawdzaj dostępność modułu przez zmienną konfiguracyjną.

```php
if (evo()->getConfig('check_sLang', false)) {
    // Your code
}
```

Jeśli plugin jest zainstalowany, `evo()->getConfig('check_sLang', false)` zawsze zwraca `true`. W innym przypadku zwraca `false`.

## Praca z lokalizowaną treścią

Użyj modelu `sLangContent`, aby pobierać przetłumaczone zasoby:

```php
use Seiger\sLang\Models\sLangContent;

$items = sLangContent::active()->get(); // locale resolved automatically
$itemsEn = sLangContent::lang('en')->withTVs(['preview'])->get();
```

> **Deprecated:** `langAndTvs()` działa w istniejącym kodzie. W nowych zapytaniach używaj `lang()` i `withTVs()`.
