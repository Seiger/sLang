# Guide développeur

Ce guide fixe les limites techniques de sLang pour la maintenance et les releases.

## Architecture

sLang expose deux surfaces manager:

- les écrans du module utilisent Livewire et evo-ui avec `Seiger\sLang\Livewire\ModulePanel`, `Seiger\sLang\Livewire\SettingsPanel` et `Seiger\sLang\Tables\TranslatesTableData`;
- les onglets de ressource sont intégrés au formulaire natif Evolution et décrits dans le [pont des ressources](resource-bridge.md).

L'intégration runtime passe par `Seiger\sLang\sLang`, l'alias facade `sLang`, `Seiger\sLang\Support\LanguageBridge` et `Seiger\sLang\Models\sLangContent`.

## Données

`CreateSLangTables` crée les tables du dictionnaire et du contenu localisé. `TranslatesTableData` lit `sLang::langConfig()` pour construire les colonnes de langue.

## Limite EvoUI

Dictionary et Configuration doivent utiliser les primitives evo-ui pour tables, formulaires, choices, boutons, modales, pagination, actions de ligne et dirty state. Ils ne chargent pas `assets/css/manager.css` et ne doivent pas ajouter de classes layout manager locales. Les primitives réutilisables manquantes doivent être ajoutées dans evo-ui d'abord.

## Carte Des Fichiers

| Surface | Fichiers |
| --- | --- |
| Shell du module | `views/index.blade.php`, `views/livewire/module-panel.blade.php`, `assets/js/manager.js` |
| Configuration | `src/Livewire/SettingsPanel.php`, `views/livewire/settings-panel.blade.php` |
| Table Dictionary | `config/translates/table.php`, `src/Tables/TranslatesTableData.php` |
| Resource bridge | `views/tabs.blade.php`, `views/resourceGeneralTab.blade.php`, `views/resourceTemplateVariablesTab.blade.php`, `views/resourceSettingsTab.blade.php` |
| Runtime API | `src/sLang.php`, `src/Support/LanguageBridge.php`, `src/Models/sLangContent.php` |

## Limite De Release

La structure base de données legacy, les parsers, les appels au provider de traduction et l'intégration du formulaire de ressource restent compatibles. La partie migrée est l'UI manager du module: Dictionary et Configuration restent Livewire/evo-ui.

## Vérifications

```bash
php docs/checks/docs-check.php
php tests/run.php
```
