# Developer Guide

This guide explains the sLang implementation boundaries for maintainers and release agents.

## Architecture

sLang has two manager surfaces with different contracts:

- Module screens use Livewire and evo-ui through `Seiger\sLang\Livewire\ModulePanel`, `Seiger\sLang\Livewire\SettingsPanel`, and `Seiger\sLang\Tables\TranslatesTableData`.
- Resource edit tabs are embedded into the native Evolution resource form and are documented in [Resource Bridge](resource-bridge.md).

Runtime integration is exposed through `Seiger\sLang\sLang`, the `sLang` facade alias, `Seiger\sLang\Support\LanguageBridge`, and locale-aware models such as `Seiger\sLang\Models\sLangContent`.

## Data Lifecycle

The install/update path uses `CreateSLangTables` to create dictionary and localized content tables. Language columns are dynamic: `TranslatesTableData` reads `sLang::langConfig()` and exposes one editable column per configured site language.

The dictionary synchronize action calls the legacy parser through `modules/sLangModule.php` and `sLangController::parseBlade`. That backend is intentionally kept because it scans existing Evolution templates and Blade files.

The dictionary evo-ui preset is registered as `slang.translates.table` and loaded from `config/translates/table.php`. Evolution manager settings remain compatible with the legacy `cms.settings` storage layer.

## Module Screen Boundary

The Dictionary and Configuration screens must use evo-ui components for tabs, forms, choices, tables, buttons, dirty-state handling, pagination, row actions, and modals. They do not load `assets/css/manager.css` and must not add manager-local layout classes. Styling global `.evo-ui-*` selectors in sLang is release drift; add missing reusable primitives to evo-ui instead.

## Resource Tab Boundary

Resource tabs run inside `form#mutate`, depend on `tpSettings.addTabPage`, and must interoperate with TinyMCE, CodeMirror, and native textarea editors. They may keep scoped compatibility JavaScript and CSS because the Evolution resource editor is not a Livewire module screen.

## Package File Map

| Surface | Files |
| --- | --- |
| Module shell | `views/index.blade.php`, `views/livewire/module-panel.blade.php`, `assets/js/manager.js` |
| Configuration | `src/Livewire/SettingsPanel.php`, `views/livewire/settings-panel.blade.php` |
| Dictionary table | `config/translates/table.php`, `src/Tables/TranslatesTableData.php` |
| Resource bridge | `views/tabs.blade.php`, `views/resourceGeneralTab.blade.php`, `views/resourceTemplateVariablesTab.blade.php`, `views/resourceSettingsTab.blade.php` |
| Runtime API | `src/sLang.php`, `src/Support/LanguageBridge.php`, `src/Models/sLangContent.php` |

## Release Boundary

Legacy database structure, parser services, translation provider calls, and resource form integration stay available. The migrated part is the manager-facing module UI: Dictionary and Configuration should stay Livewire/evo-ui based, with no copied manager chrome, no manager-local CSS, and no unscoped selectors.

## Tests

Run the package checks before release:

```bash
php docs/checks/docs-check.php
php tests/run.php
php tests/regression/slang-demo-regression.php demo/core
php tests/demo-smoke.php demo/core --url=http://127.0.0.1:8788
```

Use MiddleDuck coverage helpers when the task touches docs or evo-ui boundaries:

```bash
php DuckBook/scripts/extras-doc-coverage.php --extras=/path/to/Extras --modules=sLang
php skills/evo-ui-consumer-conformance/scripts/evo-ui-consumer-conformance.php --extras=/path/to/Extras --modules=sLang
```

The demo regression runner contains fixture classes `SlangRegressionBulkTableData`, `SlangRegressionCleanupController`, and `SlangRegressionFakeController` so dictionary bulk translation and cleanup behavior can be tested without a full browser session.
