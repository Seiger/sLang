# Reference

This page is a quick lookup for sLang APIs, classes, and manager surfaces.

## Facade And Helpers

| Surface | Purpose |
| --- | --- |
| `sLang::langDefault()` | Returns the configured default locale. |
| `sLang::langConfig()` | Returns all site languages configured for the manager. |
| `sLang::langFront()` | Returns frontend languages. |
| `sLang::langSwitcher()` | Builds language switcher data for templates. |
| `sLang::hreflang()` | Builds localized page alternates. |
| `sLang::siteContentFields()` | Returns resource fields handled by multilingual tabs. |

## Models And Support Classes

| Class | Purpose |
| --- | --- |
| `Seiger\sLang\Models\sLangTranslate` | Dictionary phrase model. |
| `Seiger\sLang\Models\sLangContent` | Locale-aware resource content model. |
| `Seiger\sLang\Models\sLangTmplvarContentvalue` | Locale-aware Template Variable value model. |
| `Seiger\sLang\Support\LanguageBridge` | Bridges manager/runtime language values. |
| `Seiger\sLang\Support\TreeCollection` | Tree collection helper for localized navigation. |

## Manager Classes

| Class | Purpose |
| --- | --- |
| `Seiger\sLang\Livewire\ModulePanel` | Top-level Livewire module shell. |
| `Seiger\sLang\Livewire\SettingsPanel` | Configuration form component. |
| `Seiger\sLang\Tables\TranslatesTableData` | Dictionary table provider. |
| `Seiger\sLang\Controllers\sLangController` | Legacy-compatible backend operations and parsing. |

The table preset is addressed as `slang.translates.table`; the source file is `config/translates/table.php`.

## Dictionary Actions

| Action | Provider |
| --- | --- |
| Synchronize keys | `TranslatesTableData::synchronizeTranslations()` |
| Add row | `TranslatesTableData::createInlineRow()` |
| Save inline field | `TranslatesTableData::updateInlineField()` |
| Delete row | `TranslatesTableData::deleteRow()` |
| Translate one value | `TranslatesTableData::autoTranslateInlineField()` |
| Translate empty column values | `TranslatesTableData::autoTranslateEmptyColumn()` |

## Resource Endpoints

`modules/sLangModule.php` keeps resource-only compatibility actions such as `translate-only` and dictionary synchronization. They are not new module-screen endpoints; they exist for Evolution resource edit forms and template key scanning.

## Test Entrypoints

| Command | Purpose |
| --- | --- |
| `php tests/run.php` | Static package contract checks. |
| `php docs/checks/docs-check.php` | dDocs structure, links, headings, and code fence checks. |
| `php tests/regression/slang-demo-regression.php demo/core` | Database-backed dictionary and settings regression. |
| `php tests/demo-smoke.php demo/core --url=http://127.0.0.1:8788` | HTTP smoke for module and resource edit tabs. |
