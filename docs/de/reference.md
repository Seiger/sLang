# Referenz

Schneller Überblick über APIs und Klassen.

## Helpers

| API | Zweck |
| --- | --- |
| `sLang::langDefault()` | Standardsprache. |
| `sLang::langConfig()` | Website-Sprachen. |
| `sLang::langFront()` | Frontend-Sprachen. |
| `sLang::langSwitcher()` | Sprachumschalter-Daten. |
| `sLang::hreflang()` | Lokalisierte Alternates. |

## Klassen

| Klasse | Zweck |
| --- | --- |
| `Seiger\sLang\Models\sLangTranslate` | Wörterbuchmodell. |
| `Seiger\sLang\Models\sLangContent` | Locale-aware Content. |
| `Seiger\sLang\Support\LanguageBridge` | Sprachbrücke. |
| `Seiger\sLang\Livewire\SettingsPanel` | Konfigurationsformular. |
| `Seiger\sLang\Tables\TranslatesTableData` | Wörterbuch-Provider. |

## Test Entrypoints

| Befehl | Zweck |
| --- | --- |
| `php tests/run.php` | Statische Package-Contract-Checks. |
| `php docs/checks/docs-check.php` | dDocs Struktur, Links, Headings und Code-Fences. |
| `php tests/regression/slang-demo-regression.php demo/core` | Datenbank-Regression für Dictionary und Settings. |
| `php tests/demo-smoke.php demo/core --url=http://127.0.0.1:8788` | HTTP Smoke für Modul und Resource-Tabs. |
