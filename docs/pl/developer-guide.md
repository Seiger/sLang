# Przewodnik dewelopera

Ten dokument opisuje granice techniczne sLang dla utrzymania i release.

## Architektura

sLang ma dwie powierzchnie managera:

- ekrany modułu używają Livewire i evo-ui przez `Seiger\sLang\Livewire\ModulePanel`, `Seiger\sLang\Livewire\SettingsPanel` oraz `Seiger\sLang\Tables\TranslatesTableData`;
- zakładki zasobu są osadzone w natywnym formularzu Evolution i opisane w [moście zasobu](resource-bridge.md).

Integracja runtime używa `Seiger\sLang\sLang`, aliasu facade `sLang`, `Seiger\sLang\Support\LanguageBridge` i `Seiger\sLang\Models\sLangContent`.

## Dane

`CreateSLangTables` tworzy tabele słownika i lokalizowanej treści. `TranslatesTableData` czyta `sLang::langConfig()` i buduje kolumny językowe.

## Granica EvoUI

Dictionary i Configuration muszą używać primitives evo-ui dla tabel, formularzy, choices, przycisków, modali, paginacji, akcji wiersza i dirty state. Nie ładują `assets/css/manager.css` i nie mogą dodawać lokalnych klas layoutu managera. Brakujące reusable primitives dodaj najpierw w evo-ui.

## Mapa Plików

| Powierzchnia | Pliki |
| --- | --- |
| Shell modułu | `views/index.blade.php`, `views/livewire/module-panel.blade.php`, `assets/js/manager.js` |
| Configuration | `src/Livewire/SettingsPanel.php`, `views/livewire/settings-panel.blade.php` |
| Tabela Dictionary | `config/translates/table.php`, `src/Tables/TranslatesTableData.php` |
| Resource bridge | `views/tabs.blade.php`, `views/resourceGeneralTab.blade.php`, `views/resourceTemplateVariablesTab.blade.php`, `views/resourceSettingsTab.blade.php` |
| Runtime API | `src/sLang.php`, `src/Support/LanguageBridge.php`, `src/Models/sLangContent.php` |

## Granica Release

Legacy struktura bazy, parsery, wywołania providerów tłumaczeń i integracja formularza zasobu zostają kompatybilne. Zmigrowana część to UI managera modułu: Dictionary i Configuration pozostają ekranami Livewire/evo-ui.

## Kontrole

```bash
php docs/checks/docs-check.php
php tests/run.php
```
