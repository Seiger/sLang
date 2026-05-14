# Entwicklerhandbuch

Dieses Dokument beschreibt die technischen Grenzen von sLang für Wartung und Release.

## Architektur

sLang besitzt zwei Manager-Oberflächen:

- Modulbildschirme nutzen Livewire und evo-ui mit `Seiger\sLang\Livewire\ModulePanel`, `Seiger\sLang\Livewire\SettingsPanel` und `Seiger\sLang\Tables\TranslatesTableData`.
- Resource-Tabs sind in das native Evolution-Formular eingebettet und im [Resource Bridge](resource-bridge.md) beschrieben.

Runtime-Integration läuft über `Seiger\sLang\sLang`, den facade alias `sLang`, `Seiger\sLang\Support\LanguageBridge` und Modelle wie `Seiger\sLang\Models\sLangContent`.

## Datenfluss

`CreateSLangTables` erstellt Wörterbuch- und Content-Tabellen. `TranslatesTableData` liest `sLang::langConfig()` und erstellt eine editierbare Spalte pro Website-Sprache.

## EvoUI Grenze

Dictionary und Configuration müssen evo-ui Primitives für Tabellen, Formulare, Choices, Buttons, Modals, Pagination, Row Actions und Dirty-State verwenden. Sie laden kein `assets/css/manager.css` und dürfen keine lokalen Manager-Layout-Klassen hinzufügen. Fehlende wiederverwendbare Primitives gehören zuerst nach evo-ui.

## Dateikarte

| Oberfläche | Dateien |
| --- | --- |
| Modul-Shell | `views/index.blade.php`, `views/livewire/module-panel.blade.php`, `assets/js/manager.js` |
| Configuration | `src/Livewire/SettingsPanel.php`, `views/livewire/settings-panel.blade.php` |
| Dictionary table | `config/translates/table.php`, `src/Tables/TranslatesTableData.php` |
| Resource bridge | `views/tabs.blade.php`, `views/resourceGeneralTab.blade.php`, `views/resourceTemplateVariablesTab.blade.php`, `views/resourceSettingsTab.blade.php` |
| Runtime API | `src/sLang.php`, `src/Support/LanguageBridge.php`, `src/Models/sLangContent.php` |

## Release Boundary

Legacy-Datenbankstruktur, Parser, Translation Provider und Resource-Form-Integration bleiben kompatibel. Migriert ist die Manager-Moduloberfläche: Dictionary und Configuration bleiben Livewire/evo-ui Screens ohne kopierte Manager-Chrome-Styles.

## Prüfungen

```bash
php docs/checks/docs-check.php
php tests/run.php
```
