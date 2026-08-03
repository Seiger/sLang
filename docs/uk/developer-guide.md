# Гайд розробника

Цей гайд фіксує технічні межі sLang для розробників і агентів перед релізом.

## Архітектура

sLang має дві різні manager-поверхні:

- екрани модуля працюють через Livewire і evo-ui: `Seiger\sLang\Livewire\ModulePanel`, `Seiger\sLang\Livewire\SettingsPanel`, `Seiger\sLang\Tables\TranslatesTableData`;
- вкладки ресурсу вбудовані в нативну форму Evolution і описані в [Мості ресурсу](resource-bridge.md).

Runtime-інтеграція проходить через `Seiger\sLang\sLang`, facade alias `sLang`, `Seiger\sLang\Support\LanguageBridge` і locale-aware моделі, зокрема `Seiger\sLang\Models\sLangContent`.

## Дані

Міграції `CreateSLangTables` створюють таблиці словника і локалізованого контенту. Мовні колонки словника динамічні: `TranslatesTableData` читає `sLang::langConfig()` і будує колонку для кожної мови сайту.

## Межа evo-ui

Словник і Конфігурація мають використовувати evo-ui primitives для таблиць, форм, choices, кнопок, модалок, пагінації, row actions і dirty-state. Вони не підключають `assets/css/manager.css` і не повинні додавати локальні manager layout classes. Якщо потрібен reusable visual primitive, спершу додаємо його в evo-ui.

## Межа ресурсу

Resource tabs працюють у `form#mutate`, реєструються через `tpSettings.addTabPage` і мають підтримувати TinyMCE, CodeMirror та textarea fallback. Через це там дозволений тільки вузький scoped bridge.

## Карта Файлів

| Поверхня | Файли |
| --- | --- |
| Shell модуля | `views/index.blade.php`, `views/livewire/module-panel.blade.php`, `assets/js/manager.js` |
| Конфігурація | `src/Livewire/SettingsPanel.php`, `views/livewire/settings-panel.blade.php` |
| Таблиця словника | `config/translates/table.php`, `src/Tables/TranslatesTableData.php` |
| Resource bridge | `views/tabs.blade.php`, `views/resourceGeneralTab.blade.php`, `views/resourceTemplateVariablesTab.blade.php`, `views/resourceSettingsTab.blade.php` |
| Runtime API | `src/sLang.php`, `src/Support/LanguageBridge.php`, `src/Models/sLangContent.php` |

## Release Boundary

Legacy структура бази, parser services, translation provider calls і resource form integration залишаються сумісними. Переписана частина - manager UI модуля: Словник і Конфігурація мають залишатися на Livewire/evo-ui без скопійованого manager chrome, manager-local CSS і unscoped selectors.

## Перевірки

```bash
php docs/checks/docs-check.php
php tests/run.php
php tests/regression/slang-demo-regression.php demo/core
php tests/demo-smoke.php demo/core --url=http://127.0.0.1:8788
```
