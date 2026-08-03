# Referencja

Szybki lookup API i klas sLang.

## Helpers

| API | Cel |
| --- | --- |
| `sLang::langDefault()` | Język domyślny. |
| `sLang::langConfig()` | Języki strony. |
| `sLang::langFront()` | Języki frontendu. |
| `sLang::langSwitcher()` | Dane przełącznika języka. |
| `sLang::hreflang()` | Lokalne alternatywy URL. |

## Klasy

| Klasa | Cel |
| --- | --- |
| `Seiger\sLang\Models\sLangTranslate` | Model słownika. |
| `Seiger\sLang\Models\sLangContent` | Locale-aware content. |
| `Seiger\sLang\Support\LanguageBridge` | Most językowy. |
| `Seiger\sLang\Livewire\SettingsPanel` | Formularz konfiguracji. |
| `Seiger\sLang\Tables\TranslatesTableData` | Provider słownika. |

## Entrypoints Testów

| Komenda | Cel |
| --- | --- |
| `php tests/run.php` | Statyczne package contract checks. |
| `php docs/checks/docs-check.php` | Struktura dDocs, linki, headings i code fences. |
| `php tests/regression/slang-demo-regression.php demo/core` | Regresja DB dla Dictionary i Settings. |
| `php tests/demo-smoke.php demo/core --url=http://127.0.0.1:8788` | HTTP smoke modułu i resource tabs. |
