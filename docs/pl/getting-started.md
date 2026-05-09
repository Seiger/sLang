# Pierwsze kroki

## Wymagania

- Evolution CMS **3.3+**
- PHP **8.3+**
- Composer **2.2+**
- Jedna z baz danych: **MySQL 8.0+** / **MariaDB 10.5+** / **PostgreSQL 10+** / **SQLite 3.25+**

## Instalacja przez artisan package installer

Przejdź do folderu `core` swojej instalacji Evolution CMS:

```console
cd path/to/your/evolution/cms/core
```

Uruchom komendę artisan:

```console
php artisan package:installrequire seiger/slang "*"
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

![Multilingual tabs](https://github.com/Seiger/slang/releases/download/v1.0.0/sLang.png)

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

> **Deprecated:** scope `langAndTvs()` jest przestarzały od `1.0.8` i zostanie usunięty w `v1.2`. Używaj `lang()` i `withTVs()`.
