# Довідник

Короткий lookup по API, класах і діях sLang.

## Facade

| Метод | Призначення |
| --- | --- |
| `sLang::langDefault()` | Типова locale. |
| `sLang::langConfig()` | Мови сайту. |
| `sLang::langFront()` | Мови фронтенду. |
| `sLang::langSwitcher()` | Дані перемикача мов. |
| `sLang::hreflang()` | Альтернативні мовні URL. |
| `sLang::siteContentFields()` | Поля ресурсу, які локалізує sLang. |

## Класи

| Клас | Призначення |
| --- | --- |
| `Seiger\sLang\Models\sLangTranslate` | Модель словника. |
| `Seiger\sLang\Models\sLangContent` | Locale-aware content model. |
| `Seiger\sLang\Support\LanguageBridge` | Міст мовних значень. |
| `Seiger\sLang\Livewire\ModulePanel` | Shell модуля. |
| `Seiger\sLang\Livewire\SettingsPanel` | Форма конфігурації. |
| `Seiger\sLang\Tables\TranslatesTableData` | Provider таблиці словника. |

## Дії словника

| Дія | Provider |
| --- | --- |
| Синхронізація | `synchronizeTranslations()` |
| Створення рядка | `createInlineRow()` |
| Inline save | `updateInlineField()` |
| Видалення | `deleteRow()` |
| Переклад одного поля | `autoTranslateInlineField()` |
| Переклад порожньої колонки | `autoTranslateEmptyColumn()` |

## Тестові Entrypoints

| Команда | Призначення |
| --- | --- |
| `php tests/run.php` | Статичні package contract checks. |
| `php docs/checks/docs-check.php` | Перевірка dDocs структури, лінків, headings і code fences. |
| `php tests/regression/slang-demo-regression.php demo/core` | DB-backed regression словника і налаштувань. |
| `php tests/demo-smoke.php demo/core --url=http://127.0.0.1:8788` | HTTP smoke модуля і resource edit tabs. |
